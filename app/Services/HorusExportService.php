<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HorusExportService
{
    /**
     * Exportação simplificada para integração manual com Hórus/BNAFAR (layout CSV).
     */
    public static function exportarMovimentos(?int $clinicaId, string $de, string $ate): StreamedResponse
    {
        Fase4SchemaService::garantir();

        $clinica = $clinicaId > 0
            ? DB::table('clinicas')->where('id', $clinicaId)->first()
            : DB::table('clinicas')->where('eh_central', 1)->first();

        $cnes = $clinica->cnes ?? '';

        $saidas = DB::table('saidas_medicamentos as s')
            ->join('medicamentos as m', 'm.id', '=', 's.medicamento_id')
            ->whereDate('s.data_saida', '>=', $de)
            ->whereDate('s.data_saida', '<=', $ate)
            ->when($clinicaId > 0, fn ($q) => $q->where('s.clinica_id', $clinicaId))
            ->select('s.data_saida', 'm.codigo', 's.quantidade')
            ->orderBy('s.data_saida')
            ->get();

        $entradas = DB::table('entradas_medicamentos as e')
            ->join('medicamentos as m', 'm.id', '=', 'e.medicamento_id')
            ->whereDate('e.data_entrada', '>=', $de)
            ->whereDate('e.data_entrada', '<=', $ate)
            ->when($clinicaId > 0, fn ($q) => $q->where('e.clinica_id', $clinicaId))
            ->select('e.data_entrada', 'm.codigo', 'e.quantidade', 'e.tipo_entrada')
            ->orderBy('e.data_entrada')
            ->get();

        return RelatorioService::exportarCsv(
            'horus_export_' . date('Y-m-d') . '.csv',
            ['CNES', 'Data', 'Codigo_CATMAT', 'Quantidade', 'Tipo_movimento', 'Sistema_origem'],
            array_merge(
                $entradas->map(fn ($r) => [
                    $cnes,
                    $r->data_entrada,
                    $r->codigo,
                    $r->quantidade,
                    $r->tipo_entrada ?: 'entrada',
                    'FARPOP',
                ])->all(),
                $saidas->map(fn ($r) => [
                    $cnes,
                    $r->data_saida,
                    $r->codigo,
                    $r->quantidade,
                    'dispensa_paciente',
                    'FARPOP',
                ])->all()
            )
        );
    }
}

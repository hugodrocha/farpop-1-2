<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RelatorioService
{
    /**
     * @param list<array<string, mixed>> $rows
     * @param list<string> $headers
     */
    public static function exportarCsv(string $filename, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public static function estoqueGeral(?int $clinicaId = null): Collection
    {
        $q = DB::table('estoque as e')
            ->join('clinicas as c', 'c.id', '=', 'e.clinica_id')
            ->join('medicamentos as m', 'm.id', '=', 'e.medicamento_id')
            ->select(
                'c.nome as unidade',
                'm.codigo',
                'm.nome as medicamento',
                'm.dosagem',
                'm.unidade as un_medida',
                'e.quantidade',
                'm.estoque_minimo'
            )
            ->orderBy('c.nome')
            ->orderBy('m.nome');

        if ($clinicaId > 0) {
            $q->where('e.clinica_id', $clinicaId);
        }

        return $q->get();
    }

    public static function dispensasPorPaciente(?int $clinicaId = null, ?string $de = null, ?string $ate = null): Collection
    {
        $q = DB::table('saidas_medicamentos as s')
            ->join('pacientes as p', 'p.id', '=', 's.paciente_id')
            ->join('medicamentos as m', 'm.id', '=', 's.medicamento_id')
            ->join('clinicas as c', 'c.id', '=', 's.clinica_id')
            ->select(
                'p.nome as paciente',
                'p.cpf',
                'p.cartao_sus as cns',
                'c.nome as unidade',
                'm.nome as medicamento',
                's.quantidade',
                's.data_saida',
                's.prescritor',
                's.numero_solicitacao'
            )
            ->orderByDesc('s.data_saida');

        if ($clinicaId > 0) {
            $q->where('s.clinica_id', $clinicaId);
        }
        if ($de) {
            $q->whereDate('s.data_saida', '>=', $de);
        }
        if ($ate) {
            $q->whereDate('s.data_saida', '<=', $ate);
        }

        return $q->limit(5000)->get();
    }

    public static function dispensasPorMedicamento(?int $clinicaId = null, ?string $de = null, ?string $ate = null): Collection
    {
        $q = DB::table('saidas_medicamentos as s')
            ->join('medicamentos as m', 'm.id', '=', 's.medicamento_id')
            ->join('clinicas as c', 'c.id', '=', 's.clinica_id')
            ->select(
                'm.codigo',
                'm.nome as medicamento',
                'c.nome as unidade',
                DB::raw('SUM(s.quantidade) as total'),
                DB::raw('COUNT(*) as qtd_registros')
            )
            ->groupBy('m.codigo', 'm.nome', 'c.nome')
            ->orderByDesc('total');

        if ($clinicaId > 0) {
            $q->where('s.clinica_id', $clinicaId);
        }
        if ($de) {
            $q->whereDate('s.data_saida', '>=', $de);
        }
        if ($ate) {
            $q->whereDate('s.data_saida', '<=', $ate);
        }

        return $q->get();
    }

    public static function transferencias(?int $clinicaId = null, ?string $de = null, ?string $ate = null): Collection
    {
        $q = DB::table('transferencias as t')
            ->join('clinicas as co', 'co.id', '=', 't.clinica_origem_id')
            ->join('clinicas as cd', 'cd.id', '=', 't.clinica_destino_id')
            ->select(
                't.codigo_nota',
                't.status',
                'co.nome as origem',
                'cd.nome as destino',
                't.enviado_em',
                't.recebido_em'
            )
            ->orderByDesc('t.enviado_em');

        if ($clinicaId > 0) {
            $q->where(function ($sub) use ($clinicaId) {
                $sub->where('t.clinica_origem_id', $clinicaId)
                    ->orWhere('t.clinica_destino_id', $clinicaId);
            });
        }
        if ($de) {
            $q->whereDate('t.enviado_em', '>=', $de);
        }
        if ($ate) {
            $q->whereDate('t.enviado_em', '<=', $ate);
        }

        return $q->limit(3000)->get();
    }

    public static function consumoMensal(?int $clinicaId = null, int $meses = 12): Collection
    {
        $inicio = today()->subMonths($meses - 1)->startOfMonth();

        $q = DB::table('saidas_medicamentos as s')
            ->join('clinicas as c', 'c.id', '=', 's.clinica_id')
            ->where('s.data_saida', '>=', $inicio)
            ->select(
                'c.nome as unidade',
                DB::raw("DATE_FORMAT(s.data_saida, '%Y-%m') as mes"),
                DB::raw('SUM(s.quantidade) as total')
            )
            ->groupBy('c.nome', DB::raw("DATE_FORMAT(s.data_saida, '%Y-%m')"))
            ->orderBy('mes')
            ->orderBy('c.nome');

        if ($clinicaId > 0) {
            $q->where('s.clinica_id', $clinicaId);
        }

        return $q->get();
    }

    public static function escopoClinica(Request $request): int
    {
        $perfil = (string) $request->session()->get('perfil', '');
        $clinicaUsuario = (int) $request->session()->get('clinica_id', 0);
        $filtro = (int) $request->query('clinica_id', 0);

        if (in_array($perfil, ['superuser', 'admin', 'gestor'], true)) {
            return $filtro;
        }

        return $clinicaUsuario;
    }
}

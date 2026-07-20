<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AlertaVencimentoService
{
    public static function diasAlerta(): int
    {
        return max(1, (int) ConfigService::get('alerta_vencimento_dias', '90'));
    }

    /**
     * @return Collection<int, object>
     */
    public static function listar(?int $clinicaId = null, ?string $tipo = null): Collection
    {
        LoteService::inicializar();

        $dias = self::diasAlerta();
        $hoje = today()->toDateString();
        $limite = today()->addDays($dias)->toDateString();

        if (DB::table('estoque_lotes')->exists()) {
            return self::listarDeLotes($clinicaId, $tipo, $hoje, $limite);
        }

        return self::listarDeEntradasLegado($clinicaId, $tipo, $hoje, $limite);
    }

    public static function contar(?int $clinicaId = null): array
    {
        return [
            'vencidos' => self::listar($clinicaId, 'vencido')->count(),
            'vencendo' => self::listar($clinicaId, 'vencendo')->count(),
            'dias' => self::diasAlerta(),
        ];
    }

    private static function listarDeLotes(?int $clinicaId, ?string $tipo, string $hoje, string $limite): Collection
    {
        $q = DB::table('estoque_lotes as l')
            ->join('medicamentos as m', 'm.id', '=', 'l.medicamento_id')
            ->join('clinicas as c', 'c.id', '=', 'l.clinica_id')
            ->whereNotNull('l.validade')
            ->where('l.quantidade', '>', 0)
            ->select(
                'l.clinica_id',
                'c.nome as clinica',
                'l.medicamento_id',
                'm.nome as medicamento',
                'm.codigo',
                'l.lote',
                'l.fabricante',
                'l.validade',
                'l.quantidade as quantidade_entrada',
                'l.id as lote_id'
            );

        if ($clinicaId > 0) {
            $q->where('l.clinica_id', $clinicaId);
        }

        if ($tipo === 'vencido') {
            $q->where('l.validade', '<', $hoje);
        } elseif ($tipo === 'vencendo') {
            $q->whereBetween('l.validade', [$hoje, $limite]);
        } else {
            $q->where('l.validade', '<=', $limite);
        }

        return $q->orderBy('l.validade')->orderBy('c.nome')->orderBy('m.nome')->get()
            ->map(fn ($row) => self::enriquecerLinha($row, $hoje));
    }

    private static function listarDeEntradasLegado(?int $clinicaId, ?string $tipo, string $hoje, string $limite): Collection
    {
        $q = DB::table('entradas_medicamentos as em')
            ->join('medicamentos as m', 'm.id', '=', 'em.medicamento_id')
            ->join('clinicas as c', 'c.id', '=', 'em.clinica_id')
            ->join('estoque as e', function ($join) {
                $join->on('e.clinica_id', '=', 'em.clinica_id')
                    ->on('e.medicamento_id', '=', 'em.medicamento_id');
            })
            ->whereNotNull('em.validade')
            ->where('e.quantidade', '>', 0)
            ->select(
                'em.clinica_id',
                'c.nome as clinica',
                'em.medicamento_id',
                'm.nome as medicamento',
                'm.codigo',
                'em.lote',
                'em.validade',
                DB::raw('SUM(em.quantidade) as quantidade_entrada')
            )
            ->groupBy('em.clinica_id', 'c.nome', 'em.medicamento_id', 'm.nome', 'm.codigo', 'em.lote', 'em.validade');

        if ($clinicaId > 0) {
            $q->where('em.clinica_id', $clinicaId);
        }
        if ($tipo === 'vencido') {
            $q->where('em.validade', '<', $hoje);
        } elseif ($tipo === 'vencendo') {
            $q->whereBetween('em.validade', [$hoje, $limite]);
        } else {
            $q->where('em.validade', '<=', $limite);
        }

        return $q->orderBy('em.validade')->get()->map(fn ($row) => self::enriquecerLinha($row, $hoje));
    }

    private static function enriquecerLinha(object $row, string $hoje): object
    {
        $validade = (string) $row->validade;
        $row->validade_fmt = ValidadeService::formatar($validade) ?: $validade;
        $row->status = $validade < $hoje ? 'vencido' : 'vencendo';
        $row->dias_restantes = (int) today()->diffInDays($validade, false);

        return $row;
    }
}

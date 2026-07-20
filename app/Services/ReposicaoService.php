<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReposicaoService
{
    public static function diasHistorico(): int
    {
        return max(30, (int) ConfigService::get('reposicao_dias_historico', '90'));
    }

    public static function diasCobertura(): int
    {
        return max(7, (int) ConfigService::get('reposicao_dias_cobertura', '30'));
    }

    public static function diasCritico(): int
    {
        return max(1, (int) ConfigService::get('reposicao_dias_critico', '7'));
    }

    /**
     * Lista de compras/reposição — itens com quantidade sugerida > 0.
     */
    public static function pedidoReposicao(?int $clinicaId = null, bool $agruparMedicamento = false): Collection
    {
        $itens = self::analisar($clinicaId, 'reposicao')
            ->filter(fn ($row) => (int) $row->qtd_sugerida > 0)
            ->values();

        if (!$agruparMedicamento || $clinicaId > 0) {
            return $itens;
        }

        return $itens
            ->groupBy('medicamento_id')
            ->map(function (Collection $grupo) {
                $first = $grupo->first();
                $unidades = $grupo->pluck('unidade')->unique()->implode(', ');

                return (object) [
                    'unidade' => $unidades,
                    'medicamento_id' => $first->medicamento_id,
                    'codigo' => $first->codigo,
                    'medicamento' => $first->medicamento,
                    'dosagem' => $first->dosagem,
                    'quantidade' => $grupo->sum('quantidade'),
                    'estoque_minimo' => $first->estoque_minimo,
                    'consumo_medio_dia' => round($grupo->avg('consumo_medio_dia'), 2),
                    'dias_restantes' => $grupo->pluck('dias_restantes')->filter(fn ($d) => $d !== null)->min(),
                    'qtd_sugerida' => $grupo->sum('qtd_sugerida'),
                    'situacao' => $grupo->contains(fn ($r) => $r->situacao === 'ruptura') ? 'ruptura' : ($grupo->contains(fn ($r) => $r->situacao === 'critico') ? 'critico' : 'atencao'),
                    'situacao_label' => $grupo->contains(fn ($r) => $r->situacao === 'ruptura') ? 'Ruptura (zerado)' : ($grupo->contains(fn ($r) => $r->situacao === 'critico') ? 'Crítico' : 'Atenção'),
                    'badge_class' => $grupo->contains(fn ($r) => $r->situacao === 'ruptura') ? 'dark' : ($grupo->contains(fn ($r) => $r->situacao === 'critico') ? 'danger' : 'warning'),
                ];
            })
            ->sortBy('medicamento')
            ->values();
    }

    /**
     * @param 'todos'|'ruptura'|'critico'|'atencao'|null $filtro
     */
    public static function analisar(?int $clinicaId = null, ?string $filtro = 'todos'): Collection
    {
        $diasHist = self::diasHistorico();
        $diasCob = self::diasCobertura();
        $diasCrit = self::diasCritico();
        $inicio = today()->subDays($diasHist);

        $consumoSub = DB::table('saidas_medicamentos')
            ->select('clinica_id', 'medicamento_id', DB::raw('SUM(quantidade) as consumo_periodo'))
            ->where('data_saida', '>=', $inicio)
            ->groupBy('clinica_id', 'medicamento_id');

        $q = DB::table('estoque as e')
            ->join('medicamentos as m', 'm.id', '=', 'e.medicamento_id')
            ->join('clinicas as c', 'c.id', '=', 'e.clinica_id')
            ->leftJoinSub($consumoSub, 'cons', function ($join) {
                $join->on('cons.clinica_id', '=', 'e.clinica_id')
                    ->on('cons.medicamento_id', '=', 'e.medicamento_id');
            })
            ->where('m.ativo', 1)
            ->select(
                'c.nome as unidade',
                'e.clinica_id',
                'e.medicamento_id',
                'm.codigo',
                'm.nome as medicamento',
                'm.dosagem',
                'e.quantidade',
                'm.estoque_minimo',
                DB::raw('COALESCE(cons.consumo_periodo, 0) as consumo_periodo')
            )
            ->orderBy('c.nome')
            ->orderBy('m.nome');

        if ($clinicaId > 0) {
            $q->where('e.clinica_id', $clinicaId);
        }

        return $q->get()
            ->map(function ($row) use ($diasHist, $diasCob, $diasCrit) {
                $qtd = (int) $row->quantidade;
                $minimo = (int) $row->estoque_minimo;
                $consumo = (float) $row->consumo_periodo;
                $mediaDia = $consumo > 0 ? $consumo / $diasHist : 0.0;

                $diasRestantes = null;
                if ($mediaDia > 0) {
                    $diasRestantes = $qtd > 0 ? (int) floor($qtd / $mediaDia) : 0;
                }

                $meta = $minimo > 0
                    ? max($minimo, (int) ceil($mediaDia * $diasCob))
                    : (int) ceil($mediaDia * $diasCob);

                $sugerida = max(0, $meta - $qtd);

                $clsEstoque = EstoqueService::classificar($qtd, $minimo);
                $situacao = self::classificarSituacao($qtd, $diasRestantes, $diasCrit, $clsEstoque, $consumo);

                $row->consumo_medio_dia = round($mediaDia, 2);
                $row->dias_restantes = $diasRestantes;
                $row->qtd_sugerida = $sugerida;
                $row->situacao = $situacao;
                $row->situacao_label = self::labelSituacao($situacao);
                $row->badge_class = self::badgeSituacao($situacao);

                return $row;
            })
            ->filter(function ($row) use ($filtro) {
                if ($filtro === 'ruptura') {
                    return $row->situacao === 'ruptura';
                }
                if ($filtro === 'critico') {
                    return in_array($row->situacao, ['ruptura', 'critico'], true);
                }
                if ($filtro === 'atencao') {
                    return $row->situacao === 'atencao';
                }
                if ($filtro === 'reposicao') {
                    return $row->qtd_sugerida > 0
                        || in_array($row->situacao, ['ruptura', 'critico', 'atencao'], true);
                }

                return in_array($row->situacao, ['ruptura', 'critico', 'atencao'], true);
            })
            ->values();
    }

    /**
     * @return array{ruptura: int, critico: int, atencao: int}
     */
    public static function contar(?int $clinicaId = null): array
    {
        $itens = self::analisar($clinicaId, 'todos');

        return [
            'ruptura' => $itens->where('situacao', 'ruptura')->count(),
            'critico' => $itens->where('situacao', 'critico')->count(),
            'atencao' => $itens->where('situacao', 'atencao')->count(),
        ];
    }

    private static function classificarSituacao(
        int $qtd,
        ?int $diasRestantes,
        int $diasCrit,
        string $clsEstoque,
        float $consumo
    ): string {
        if ($qtd <= 0) {
            return 'ruptura';
        }

        if ($diasRestantes !== null) {
            if ($diasRestantes <= $diasCrit) {
                return 'critico';
            }
            if ($diasRestantes <= 30) {
                return 'atencao';
            }
        }

        if ($clsEstoque === 'vermelho') {
            return 'critico';
        }
        if ($clsEstoque === 'amarelo') {
            return 'atencao';
        }

        return $consumo > 0 ? 'ok' : 'sem_consumo';
    }

    private static function labelSituacao(string $situacao): string
    {
        return match ($situacao) {
            'ruptura' => 'Ruptura (zerado)',
            'critico' => 'Crítico',
            'atencao' => 'Atenção',
            'sem_consumo' => 'Sem consumo recente',
            default => 'OK',
        };
    }

    private static function badgeSituacao(string $situacao): string
    {
        return match ($situacao) {
            'ruptura' => 'dark',
            'critico' => 'danger',
            'atencao' => 'warning',
            'sem_consumo' => 'secondary',
            default => 'success',
        };
    }
}

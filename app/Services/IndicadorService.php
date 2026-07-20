<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IndicadorService
{
    /**
     * KPIs operacionais de um mês.
     *
     * @return array<string, mixed>
     */
    public static function mensal(?int $clinicaId = null, ?int $mes = null, ?int $ano = null): array
    {
        $mes = $mes ?? (int) today()->month;
        $ano = $ano ?? (int) today()->year;
        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));

        $entradas = self::queryEntradas($clinicaId, $inicio, $fim);
        $saidas = self::querySaidas($clinicaId, $inicio, $fim);
        $transferencias = self::queryTransferencias($clinicaId, $inicio, $fim);
        $solicitacoes = self::querySolicitacoes($clinicaId, $inicio, $fim);

        $estoqueQ = DB::table('estoque as e');
        if ($clinicaId > 0) {
            $estoqueQ->where('e.clinica_id', $clinicaId);
        }
        $estoqueAtual = (int) $estoqueQ->sum('quantidade');

        $alertas = AlertaOperacionalService::resumo($clinicaId);
        $reposicao = ReposicaoService::contar($clinicaId);

        return [
            'mes' => $mes,
            'ano' => $ano,
            'periodo_label' => self::labelMes($mes, $ano),
            'entradas_qtd' => (int) ($entradas->registros ?? 0),
            'entradas_unidades' => (int) ($entradas->total ?? 0),
            'saidas_qtd' => (int) ($saidas->registros ?? 0),
            'saidas_unidades' => (int) ($saidas->total ?? 0),
            'pacientes_atendidos' => (int) ($saidas->pacientes ?? 0),
            'transferencias_enviadas' => (int) ($transferencias->enviadas ?? 0),
            'transferencias_recebidas' => (int) ($transferencias->recebidas ?? 0),
            'solicitacoes_criadas' => (int) ($solicitacoes->criadas ?? 0),
            'solicitacoes_aprovadas' => (int) ($solicitacoes->aprovadas ?? 0),
            'estoque_atual' => $estoqueAtual,
            'alertas_criticos' => (int) ($alertas['estoque_critico'] ?? 0),
            'alertas_vencidos' => (int) ($alertas['vencidos'] ?? 0),
            'ruptura' => (int) ($reposicao['ruptura'] ?? 0),
        ];
    }

    /**
     * Últimos N meses para comparativo.
     */
    public static function historico(?int $clinicaId = null, int $meses = 6): Collection
    {
        $out = collect();
        $ref = today()->startOfMonth();

        for ($i = $meses - 1; $i >= 0; $i--) {
            $dt = $ref->copy()->subMonths($i);
            $out->push((object) self::mensal($clinicaId, (int) $dt->month, (int) $dt->year));
        }

        return $out;
    }

    public static function labelMes(int $mes, int $ano): string
    {
        $nomes = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

        return ($nomes[$mes] ?? (string) $mes) . '/' . $ano;
    }

    private static function queryEntradas(?int $clinicaId, string $inicio, string $fim): object
    {
        $q = DB::table('entradas_medicamentos')
            ->whereDate('data_entrada', '>=', $inicio)
            ->whereDate('data_entrada', '<=', $fim);
        if ($clinicaId > 0) {
            $q->where('clinica_id', $clinicaId);
        }

        return $q->selectRaw('COUNT(*) as registros, COALESCE(SUM(quantidade),0) as total')->first();
    }

    private static function querySaidas(?int $clinicaId, string $inicio, string $fim): object
    {
        $q = DB::table('saidas_medicamentos')
            ->whereDate('data_saida', '>=', $inicio)
            ->whereDate('data_saida', '<=', $fim);
        if ($clinicaId > 0) {
            $q->where('clinica_id', $clinicaId);
        }

        return $q->selectRaw('COUNT(*) as registros, COALESCE(SUM(quantidade),0) as total, COUNT(DISTINCT paciente_id) as pacientes')->first();
    }

    private static function queryTransferencias(?int $clinicaId, string $inicio, string $fim): object
    {
        $apply = function ($q) use ($clinicaId, $inicio, $fim) {
            $q->whereDate('enviado_em', '>=', $inicio)
                ->whereDate('enviado_em', '<=', $fim);
            if ($clinicaId > 0) {
                $q->where(function ($sub) use ($clinicaId) {
                    $sub->where('clinica_origem_id', $clinicaId)
                        ->orWhere('clinica_destino_id', $clinicaId);
                });
            }
        };

        $enviadasQ = DB::table('transferencias');
        $apply($enviadasQ);

        $recebidasQ = DB::table('transferencias')->whereIn('status', ['recebida', 'recebida_parcial']);
        $apply($recebidasQ);

        return (object) [
            'enviadas' => (int) $enviadasQ->count(),
            'recebidas' => (int) $recebidasQ->count(),
        ];
    }

    private static function querySolicitacoes(?int $clinicaId, string $inicio, string $fim): object
    {
        if (!Schema::hasTable('transferencia_solicitacoes')) {
            return (object) ['criadas' => 0, 'aprovadas' => 0];
        }

        $apply = function ($q) use ($clinicaId, $inicio, $fim) {
            $q->whereDate('criado_em', '>=', $inicio)
                ->whereDate('criado_em', '<=', $fim);
            if ($clinicaId > 0) {
                $q->where('clinica_solicitante_id', $clinicaId);
            }
        };

        $criadasQ = DB::table('transferencia_solicitacoes');
        $apply($criadasQ);

        $aprovadasQ = DB::table('transferencia_solicitacoes')
            ->whereIn('status', ['aprovada', 'em_separacao', 'enviada', 'concluida']);
        $apply($aprovadasQ);

        return (object) [
            'criadas' => (int) $criadasQ->count(),
            'aprovadas' => (int) $aprovadasQ->count(),
        ];
    }
}

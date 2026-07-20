<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlertaOperacionalService
{
    public static function resumo(?int $clinicaId = null): array
    {
        $venc = AlertaVencimentoService::contar($clinicaId);
        $estoque = self::contarEstoque($clinicaId);

        $transfQ = DB::table('transferencias')->where('status', 'pendente');
        $solQ = null;

        if ($clinicaId > 0) {
            $transfQ->where('clinica_destino_id', $clinicaId);
        }

        $solicitacoesPendentes = 0;
        if (Schema::hasTable('transferencia_solicitacoes')) {
            $solQ = DB::table('transferencia_solicitacoes')->whereIn('status', ['solicitada', 'aprovada', 'em_separacao']);
            if ($clinicaId > 0) {
                $solQ->where(function ($sub) use ($clinicaId) {
                    $sub->where('clinica_solicitante_id', $clinicaId)
                        ->orWhere('clinica_origem_id', $clinicaId);
                });
            }
            $solicitacoesPendentes = (int) $solQ->count();
        }

        $reservasQ = DB::table('reservas_medicamentos')->where('status', 'ativa');
        if ($clinicaId > 0 && Schema::hasTable('reservas_medicamentos')) {
            $reservasQ->where('clinica_id', $clinicaId);
        }

        return [
            'estoque_critico' => $estoque['critico'],
            'estoque_atencao' => $estoque['atencao'],
            'vencidos' => $venc['vencidos'],
            'vencendo' => $venc['vencendo'],
            'dias_vencimento' => $venc['dias'],
            'transf_pendentes' => (int) $transfQ->count(),
            'solicitacoes_pendentes' => $solicitacoesPendentes,
            'reservas_ativas' => Schema::hasTable('reservas_medicamentos') ? (int) $reservasQ->count() : 0,
        ];
    }

    public static function contarEstoque(?int $clinicaId = null): array
    {
        $critico = 0;
        $atencao = 0;

        foreach (self::baseEstoque($clinicaId) as $row) {
            $cls = EstoqueService::classificar((int) $row->quantidade, (int) $row->estoque_minimo);
            if ($cls === 'vermelho') {
                $critico++;
            } elseif ($cls === 'amarelo') {
                $atencao++;
            }
        }

        return ['critico' => $critico, 'atencao' => $atencao];
    }

    /**
     * @param 'critico'|'atencao'|'todos'|null $nivel
     */
    public static function listarEstoque(?int $clinicaId = null, ?string $nivel = 'todos'): Collection
    {
        return self::baseEstoque($clinicaId)
            ->filter(function ($row) use ($nivel) {
                $cls = EstoqueService::classificar((int) $row->quantidade, (int) $row->estoque_minimo);
                if ($nivel === 'critico') {
                    return $cls === 'vermelho';
                }
                if ($nivel === 'atencao') {
                    return $cls === 'amarelo';
                }

                return in_array($cls, ['vermelho', 'amarelo'], true);
            })
            ->map(function ($row) {
                $cls = EstoqueService::classificar((int) $row->quantidade, (int) $row->estoque_minimo);
                [$badge, $label] = EstoqueService::badge($cls);
                $row->classificacao = $cls;
                $row->badge_class = $badge;
                $row->badge_label = $label;
                $row->percentual_minimo = (int) $row->estoque_minimo > 0
                    ? (int) round((int) $row->quantidade / (int) $row->estoque_minimo * 100)
                    : null;

                return $row;
            })
            ->values();
    }

    private static function baseEstoque(?int $clinicaId): Collection
    {
        $q = DB::table('estoque as e')
            ->join('clinicas as c', 'c.id', '=', 'e.clinica_id')
            ->join('medicamentos as m', 'm.id', '=', 'e.medicamento_id')
            ->where('m.estoque_minimo', '>', 0)
            ->select(
                'e.clinica_id',
                'c.nome as unidade',
                'm.codigo',
                'm.nome as medicamento',
                'm.dosagem',
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

    public static function listarPendencias(?int $clinicaId = null): Collection
    {
        $itens = collect();

        $transf = DB::table('transferencias as t')
            ->join('clinicas as co', 'co.id', '=', 't.clinica_origem_id')
            ->join('clinicas as cd', 'cd.id', '=', 't.clinica_destino_id')
            ->where('t.status', 'pendente')
            ->when($clinicaId > 0, fn ($q) => $q->where('t.clinica_destino_id', $clinicaId))
            ->orderByDesc('t.enviado_em')
            ->limit(50)
            ->get(['t.id', 't.codigo_nota', 'co.nome as origem', 'cd.nome as destino', 't.enviado_em']);

        foreach ($transf as $t) {
            $itens->push((object) [
                'tipo' => 'transferencia',
                'titulo' => 'Transferência aguardando recebimento',
                'detalhe' => $t->origem . ' → ' . $t->destino,
                'codigo' => $t->codigo_nota,
                'data' => $t->enviado_em,
                'url' => route('transferencias.receber'),
            ]);
        }

        if (Schema::hasTable('transferencia_solicitacoes')) {
            $sols = DB::table('transferencia_solicitacoes as s')
                ->join('clinicas as cs', 'cs.id', '=', 's.clinica_solicitante_id')
                ->whereIn('s.status', ['solicitada', 'aprovada', 'em_separacao'])
                ->when($clinicaId > 0, function ($q) use ($clinicaId) {
                    $q->where(function ($sub) use ($clinicaId) {
                        $sub->where('s.clinica_solicitante_id', $clinicaId)
                            ->orWhere('s.clinica_origem_id', $clinicaId);
                    });
                })
                ->orderByDesc('s.criado_em')
                ->limit(50)
                ->get(['s.id', 's.codigo', 's.status', 'cs.nome as solicitante', 's.criado_em']);

            foreach ($sols as $s) {
                $itens->push((object) [
                    'tipo' => 'solicitacao',
                    'titulo' => 'Solicitação ' . str_replace('_', ' ', $s->status),
                    'detalhe' => $s->solicitante,
                    'codigo' => $s->codigo,
                    'data' => $s->criado_em,
                    'url' => route('solicitacoes.show', $s->id),
                ]);
            }
        }

        return $itens->sortByDesc('data')->values();
    }
}

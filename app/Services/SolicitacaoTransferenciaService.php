<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SolicitacaoTransferenciaService
{
    public static function inicializar(): void
    {
        Fase4SchemaService::garantir();
    }

    public static function gerarCodigo(): string
    {
        return 'SOL' . now()->format('ymd') . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param list<array{medicamento_id: int, quantidade: int, observacao?: string}> $itens
     */
    public static function criar(int $clinicaSolicitanteId, int $usuarioId, ?string $observacao, array $itens): int
    {
        self::inicializar();

        $centralId = EstoqueService::getCentralId();
        if (!$centralId) {
            throw new RuntimeException('Unidade central não configurada.');
        }
        if ($clinicaSolicitanteId === $centralId) {
            throw new RuntimeException('A central não precisa solicitar transferência para si mesma.');
        }
        if (empty($itens)) {
            throw new RuntimeException('Informe ao menos um medicamento.');
        }

        return DB::transaction(function () use ($clinicaSolicitanteId, $centralId, $usuarioId, $observacao, $itens) {
            $id = DB::table('transferencia_solicitacoes')->insertGetId([
                'codigo' => self::gerarCodigo(),
                'clinica_solicitante_id' => $clinicaSolicitanteId,
                'clinica_origem_id' => $centralId,
                'status' => 'solicitada',
                'observacao' => $observacao,
                'usuario_solicitante_id' => $usuarioId,
                'criado_em' => now(),
                'atualizado_em' => now(),
            ]);

            foreach ($itens as $it) {
                $medId = (int) ($it['medicamento_id'] ?? 0);
                $qtd = (int) ($it['quantidade'] ?? 0);
                if ($medId <= 0 || $qtd <= 0) {
                    continue;
                }
                DB::table('transferencia_solicitacao_itens')->insert([
                    'solicitacao_id' => $id,
                    'medicamento_id' => $medId,
                    'quantidade_solicitada' => $qtd,
                    'observacao' => $it['observacao'] ?? null,
                ]);
            }

            AuditoriaService::registrar('SOLICITACAO_TRANSFERENCIA', 'SOL#' . $id, 'Solicitação criada pela unidade #' . $clinicaSolicitanteId);

            return (int) $id;
        });
    }

    public static function aprovar(int $solicitacaoId, int $usuarioId, array $quantidadesAprovadas = []): void
    {
        self::inicializar();

        DB::transaction(function () use ($solicitacaoId, $usuarioId, $quantidadesAprovadas) {
            $sol = DB::table('transferencia_solicitacoes')->where('id', $solicitacaoId)->lockForUpdate()->first();
            if (!$sol || $sol->status !== 'solicitada') {
                throw new RuntimeException('Solicitação indisponível para aprovação.');
            }

            $itens = DB::table('transferencia_solicitacao_itens')->where('solicitacao_id', $solicitacaoId)->get();
            foreach ($itens as $it) {
                $aprovada = (int) ($quantidadesAprovadas[$it->id] ?? $it->quantidade_solicitada);
                $aprovada = max(0, min($aprovada, (int) $it->quantidade_solicitada));
                DB::table('transferencia_solicitacao_itens')->where('id', $it->id)->update([
                    'quantidade_aprovada' => $aprovada,
                ]);
            }

            DB::table('transferencia_solicitacoes')->where('id', $solicitacaoId)->update([
                'status' => 'aprovada',
                'usuario_aprovacao_id' => $usuarioId,
                'aprovado_em' => now(),
                'atualizado_em' => now(),
            ]);

            AuditoriaService::registrar('SOLICITACAO_APROVADA', $sol->codigo, 'Aprovada por usuário #' . $usuarioId);
        });
    }

    public static function recusar(int $solicitacaoId, int $usuarioId, string $motivo): void
    {
        self::inicializar();

        $sol = DB::table('transferencia_solicitacoes')->where('id', $solicitacaoId)->first();
        if (!$sol || $sol->status !== 'solicitada') {
            throw new RuntimeException('Solicitação indisponível.');
        }

        DB::table('transferencia_solicitacoes')->where('id', $solicitacaoId)->update([
            'status' => 'recusada',
            'motivo_recusa' => $motivo,
            'usuario_aprovacao_id' => $usuarioId,
            'atualizado_em' => now(),
        ]);

        AuditoriaService::registrar('SOLICITACAO_RECUSADA', $sol->codigo, $motivo);
    }

    public static function marcarSeparacao(int $solicitacaoId, int $usuarioId): void
    {
        self::inicializar();

        $sol = DB::table('transferencia_solicitacoes')->where('id', $solicitacaoId)->first();
        if (!$sol || $sol->status !== 'aprovada') {
            throw new RuntimeException('Somente solicitações aprovadas podem ir para separação.');
        }

        DB::table('transferencia_solicitacoes')->where('id', $solicitacaoId)->update([
            'status' => 'em_separacao',
            'separado_em' => now(),
            'atualizado_em' => now(),
        ]);

        AuditoriaService::registrar('SOLICITACAO_SEPARACAO', $sol->codigo, 'Separação iniciada');
    }

    public static function enviar(int $solicitacaoId, int $usuarioId): int
    {
        self::inicializar();

        return DB::transaction(function () use ($solicitacaoId, $usuarioId) {
            $sol = DB::table('transferencia_solicitacoes')->where('id', $solicitacaoId)->lockForUpdate()->first();
            if (!$sol || !in_array($sol->status, ['aprovada', 'em_separacao'], true)) {
                throw new RuntimeException('Solicitação não está pronta para envio.');
            }

            $centralId = (int) $sol->clinica_origem_id;
            $destinoId = (int) $sol->clinica_solicitante_id;

            $itens = DB::table('transferencia_solicitacao_itens')
                ->where('solicitacao_id', $solicitacaoId)
                ->get();

            $payload = [];
            foreach ($itens as $it) {
                $qtd = (int) ($it->quantidade_aprovada ?? $it->quantidade_solicitada);
                if ($qtd <= 0) {
                    continue;
                }
                $payload[] = [
                    'medicamento_id' => (int) $it->medicamento_id,
                    'quantidade' => $qtd,
                    'lote' => null,
                    'validade' => null,
                ];
            }

            if (empty($payload)) {
                throw new RuntimeException('Nenhum item aprovado para envio.');
            }

            $transferenciaId = TransferenciaService::finalizarLote(
                $centralId,
                $destinoId,
                $usuarioId,
                'Solicitação ' . $sol->codigo . ($sol->observacao ? ' — ' . $sol->observacao : ''),
                $payload
            );

            DB::table('transferencia_solicitacoes')->where('id', $solicitacaoId)->update([
                'status' => 'enviada',
                'transferencia_id' => $transferenciaId,
                'enviado_em' => now(),
                'atualizado_em' => now(),
            ]);

            AuditoriaService::registrar('SOLICITACAO_ENVIADA', $sol->codigo, 'Transferência #' . $transferenciaId);

            return $transferenciaId;
        });
    }

    public static function contarPendentes(?int $clinicaId = null): int
    {
        self::inicializar();

        $q = DB::table('transferencia_solicitacoes')->whereIn('status', ['solicitada', 'aprovada', 'em_separacao']);
        if ($clinicaId > 0) {
            $q->where(function ($sub) use ($clinicaId) {
                $sub->where('clinica_solicitante_id', $clinicaId)
                    ->orWhere('clinica_origem_id', $clinicaId);
            });
        }

        return (int) $q->count();
    }

    public static function listar(?int $clinicaId = null, ?string $status = null): Collection
    {
        self::inicializar();

        $q = DB::table('transferencia_solicitacoes as s')
            ->join('clinicas as cs', 'cs.id', '=', 's.clinica_solicitante_id')
            ->join('clinicas as co', 'co.id', '=', 's.clinica_origem_id')
            ->leftJoin('usuarios as u', 'u.id', '=', 's.usuario_solicitante_id')
            ->select('s.*', 'cs.nome as solicitante', 'co.nome as origem', 'u.nome_completo as usuario_nome')
            ->orderByDesc('s.criado_em')
            ->limit(200);

        if ($clinicaId > 0) {
            $q->where(function ($sub) use ($clinicaId) {
                $sub->where('s.clinica_solicitante_id', $clinicaId)
                    ->orWhere('s.clinica_origem_id', $clinicaId);
            });
        }
        if ($status) {
            $q->where('s.status', $status);
        }

        return $q->get();
    }

    public static function itens(int $solicitacaoId): Collection
    {
        return DB::table('transferencia_solicitacao_itens as i')
            ->join('medicamentos as m', 'm.id', '=', 'i.medicamento_id')
            ->where('i.solicitacao_id', $solicitacaoId)
            ->select('i.*', 'm.nome as medicamento', 'm.codigo', 'm.dosagem')
            ->get();
    }
}

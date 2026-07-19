<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class TransferenciaService
{
    public static function gerarCodigoNota(int $origemId, int $destinoId): string
    {
        $ts = now()->format('YmdHis');
        $o = str_pad((string) $origemId, 4, '0', STR_PAD_LEFT);
        $d = str_pad((string) $destinoId, 4, '0', STR_PAD_LEFT);
        $r = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $codigo = substr($ts . $o . $d . $r, 0, 26);

        return str_pad($codigo, 26, '0', STR_PAD_LEFT);
    }

    public static function codigoUnico(int $origemId, int $destinoId): string
    {
        for ($i = 0; $i < 5; $i++) {
            $codigo = self::gerarCodigoNota($origemId, $destinoId);
            if (!DB::table('transferencias')->where('codigo_nota', $codigo)->exists()) {
                return $codigo;
            }
        }

        throw new RuntimeException('Não foi possível gerar código único para a nota.');
    }

    public static function finalizarLote(int $centralId, int $destinoId, int $usuarioId, ?string $observacao, array $itens): int
    {
        if ($destinoId <= 0 || $destinoId === $centralId) {
            throw new RuntimeException('Selecione uma clínica de destino válida (não pode ser a Central).');
        }
        if (empty($itens)) {
            throw new RuntimeException('O lote deve ter pelo menos 1 medicamento.');
        }

        $itensValidos = [];
        foreach ($itens as $i => $it) {
            $medId = (int) ($it['medicamento_id'] ?? 0);
            $qtd = (int) ($it['quantidade'] ?? 0);
            if ($medId <= 0 || $qtd <= 0) {
                throw new RuntimeException('Item #' . ($i + 1) . ' inválido.');
            }
            $est = EstoqueService::getAtual($centralId, $medId);
            if ($est < $qtd) {
                $nome = DB::table('medicamentos')->where('id', $medId)->value('nome') ?: "med#{$medId}";
                throw new RuntimeException("Estoque insuficiente na Central para '{$nome}'. Disponível: {$est}, solicitado: {$qtd}.");
            }
            $itensValidos[] = [
                'medicamento_id' => $medId,
                'quantidade' => $qtd,
                'lote' => ($it['lote'] ?? '') !== '' ? $it['lote'] : null,
                'validade' => ValidadeService::normalizar($it['validade'] ?? null),
            ];
        }

        return DB::transaction(function () use ($centralId, $destinoId, $usuarioId, $observacao, $itensValidos) {
            $codigoNota = self::codigoUnico($centralId, $destinoId);

            $transferenciaId = DB::table('transferencias')->insertGetId([
                'codigo_nota' => $codigoNota,
                'clinica_origem_id' => $centralId,
                'clinica_destino_id' => $destinoId,
                'medicamento_id' => null,
                'quantidade' => null,
                'observacao' => $observacao,
                'usuario_envio_id' => $usuarioId,
                'status' => 'pendente',
                'enviado_em' => now(),
            ]);

            foreach ($itensValidos as $it) {
                DB::table('transferencia_itens')->insert([
                    'transferencia_id' => $transferenciaId,
                    'medicamento_id' => $it['medicamento_id'],
                    'quantidade_enviada' => $it['quantidade'],
                    'quantidade_recebida' => 0,
                    'lote' => $it['lote'],
                    'validade' => $it['validade'],
                ]);

                if (!EstoqueService::subtrair($centralId, $it['medicamento_id'], $it['quantidade'])) {
                    throw new RuntimeException('Falha ao debitar estoque da Central.');
                }
            }

            AuditoriaService::registrar(
                'TRANSFERENCIA_ENVIADA',
                'nota ' . $codigoNota,
                count($itensValidos) . ' item(ns) enviado(s) para clínica #' . $destinoId
            );

            return $transferenciaId;
        });
    }

    public static function confirmarRecebimento(int $transferenciaId, int $usuarioId, int $clinicaUsuarioId, bool $ehAdmin, array $recebidas, ?string $obsRec): void
    {
        DB::transaction(function () use ($transferenciaId, $usuarioId, $clinicaUsuarioId, $ehAdmin, $recebidas, $obsRec) {
            $t = DB::table('transferencias')->where('id', $transferenciaId)->where('status', 'pendente')->first();
            if (!$t) {
                throw new RuntimeException('Nota não está mais pendente.');
            }

            $podeReceber = $ehAdmin || ((session('perfil') === 'farmaceutico') && $clinicaUsuarioId === (int) $t->clinica_destino_id);
            if (!$podeReceber) {
                throw new RuntimeException('Sem permissão para receber esta nota.');
            }

            $destinoId = (int) $t->clinica_destino_id;
            $itens = DB::table('transferencia_itens')->where('transferencia_id', $transferenciaId)->get()->keyBy('id');

            foreach ($recebidas as $itemId => $qtdRec) {
                $qtdRec = (int) $qtdRec;
                if ($qtdRec <= 0 || !isset($itens[$itemId])) {
                    continue;
                }
                $it = $itens[$itemId];
                if ($qtdRec > (int) $it->quantidade_enviada) {
                    $qtdRec = (int) $it->quantidade_enviada;
                }

                DB::table('transferencia_itens')->where('id', $itemId)->update([
                    'quantidade_recebida' => $qtdRec,
                    'recebido_em' => now(),
                ]);

                EstoqueService::somarOuCriar($destinoId, (int) $it->medicamento_id, $qtdRec);

                $entradaId = DB::table('entradas_medicamentos')->insertGetId([
                    'clinica_id' => $destinoId,
                    'medicamento_id' => $it->medicamento_id,
                    'quantidade' => $qtdRec,
                    'lote' => $it->lote,
                    'validade' => $it->validade,
                    'origem' => 'Transferência nota ' . ($t->codigo_nota ?? "#{$transferenciaId}"),
                    'observacao' => 'Recebido via transferência',
                    'usuario_id' => $usuarioId,
                    'data_entrada' => now(),
                ]);
            }

            DB::table('transferencias')->where('id', $transferenciaId)->update([
                'status' => 'recebida',
                'usuario_recebimento_id' => $usuarioId,
                'recebido_em' => now(),
                'observacao_recebimento' => $obsRec,
            ]);

            AuditoriaService::registrar(
                'TRANSFERENCIA_RECEBIDA',
                'nota ' . ($t->codigo_nota ?? "#{$transferenciaId}"),
                'Recebimento confirmado na clínica #' . $destinoId
            );
        });
    }

    public static function cancelar(int $transferenciaId, int $usuarioId, string $motivo, string $perfil, int $clinicaUsuarioId): void
    {
        $centralId = EstoqueService::getCentralId();
        if (!PerfilService::podeCancelarTransferencia($perfil, $clinicaUsuarioId, $centralId)) {
            throw new RuntimeException('Sem permissão para cancelar transferências.');
        }

        DB::transaction(function () use ($transferenciaId, $usuarioId, $motivo, $centralId) {
            $t = DB::table('transferencias')->where('id', $transferenciaId)->where('status', 'pendente')->first();
            if (!$t) {
                throw new RuntimeException('Transferência não encontrada ou já finalizada.');
            }

            $itens = DB::table('transferencia_itens')->where('transferencia_id', $transferenciaId)->get();
            foreach ($itens as $it) {
                EstoqueService::somarOuCriar($centralId ?? (int) $t->clinica_origem_id, (int) $it->medicamento_id, (int) $it->quantidade_enviada);
            }

            DB::table('transferencias')->where('id', $transferenciaId)->update([
                'status' => 'cancelada',
                'usuario_cancelamento_id' => $usuarioId,
                'cancelado_em' => now(),
                'motivo_cancelamento' => $motivo,
            ]);

            AuditoriaService::registrar(
                'TRANSFERENCIA_CANCELADA',
                'nota ' . ($t->codigo_nota ?? "#{$transferenciaId}"),
                'Cancelada. Motivo: ' . $motivo
            );
        });
    }
}

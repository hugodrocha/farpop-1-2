<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReservaService
{
    public static function inicializar(): void
    {
        Fase4SchemaService::garantir();
    }

    public static function criar(
        int $pacienteId,
        int $medicamentoId,
        int $clinicaId,
        int $quantidade,
        ?string $validadeReserva,
        ?string $observacao,
        int $usuarioId
    ): int {
        self::inicializar();

        if ($quantidade <= 0) {
            throw new RuntimeException('Quantidade inválida.');
        }

        $est = EstoqueService::getAtual($clinicaId, $medicamentoId);
        $reservado = self::quantidadeReservadaAtiva($clinicaId, $medicamentoId);
        if ($est - $reservado < $quantidade) {
            throw new RuntimeException('Estoque disponível insuficiente para reserva (considerando reservas ativas).');
        }

        return DB::table('reservas_medicamentos')->insertGetId([
            'paciente_id' => $pacienteId,
            'medicamento_id' => $medicamentoId,
            'clinica_id' => $clinicaId,
            'quantidade' => $quantidade,
            'validade_reserva' => $validadeReserva ?: null,
            'observacao' => $observacao,
            'usuario_id' => $usuarioId,
            'status' => 'ativa',
            'criado_em' => now(),
            'atualizado_em' => now(),
        ]);
    }

    public static function cancelar(int $reservaId, int $usuarioId): void
    {
        self::inicializar();

        DB::table('reservas_medicamentos')->where('id', $reservaId)->where('status', 'ativa')->update([
            'status' => 'cancelada',
            'atualizado_em' => now(),
        ]);

        AuditoriaService::registrar('RESERVA_CANCELADA', 'reserva#' . $reservaId, 'Usuário #' . $usuarioId);
    }

    public static function quantidadeReservadaAtiva(int $clinicaId, int $medicamentoId): int
    {
        self::inicializar();

        return (int) DB::table('reservas_medicamentos')
            ->where('clinica_id', $clinicaId)
            ->where('medicamento_id', $medicamentoId)
            ->where('status', 'ativa')
            ->selectRaw('COALESCE(SUM(quantidade - quantidade_atendida), 0) as t')
            ->value('t');
    }

    public static function quantidadeReservadaPaciente(int $pacienteId, int $clinicaId, int $medicamentoId): int
    {
        self::inicializar();

        return (int) DB::table('reservas_medicamentos')
            ->where('paciente_id', $pacienteId)
            ->where('clinica_id', $clinicaId)
            ->where('medicamento_id', $medicamentoId)
            ->where('status', 'ativa')
            ->selectRaw('COALESCE(SUM(quantidade - quantidade_atendida), 0) as t')
            ->value('t');
    }

    /**
     * Atualiza reservas ativas do paciente ao dispensar.
     */
    public static function consumirNaDispensa(int $pacienteId, int $medicamentoId, int $clinicaId, int $quantidade, int $saidaId): void
    {
        self::inicializar();

        $restante = $quantidade;
        $reservas = DB::table('reservas_medicamentos')
            ->where('paciente_id', $pacienteId)
            ->where('medicamento_id', $medicamentoId)
            ->where('clinica_id', $clinicaId)
            ->where('status', 'ativa')
            ->orderBy('validade_reserva')
            ->orderBy('criado_em')
            ->get();

        foreach ($reservas as $r) {
            if ($restante <= 0) {
                break;
            }
            $pendente = (int) $r->quantidade - (int) $r->quantidade_atendida;
            if ($pendente <= 0) {
                continue;
            }
            $usar = min($restante, $pendente);
            $novoAtendido = (int) $r->quantidade_atendida + $usar;
            $status = $novoAtendido >= (int) $r->quantidade ? 'atendida' : 'ativa';

            DB::table('reservas_medicamentos')->where('id', $r->id)->update([
                'quantidade_atendida' => $novoAtendido,
                'status' => $status,
                'saida_id' => $status === 'atendida' ? $saidaId : $r->saida_id,
                'atualizado_em' => now(),
            ]);
            $restante -= $usar;
        }
    }

    public static function expirarVencidas(): int
    {
        self::inicializar();

        return DB::table('reservas_medicamentos')
            ->where('status', 'ativa')
            ->whereNotNull('validade_reserva')
            ->whereDate('validade_reserva', '<', today())
            ->update(['status' => 'expirada', 'atualizado_em' => now()]);
    }
}

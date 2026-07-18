<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class SaidaService
{
    public static function limparCpf(string $cpf): string
    {
        return preg_replace('/\D/', '', $cpf);
    }

    public static function formatarCpf(string $cpf): string
    {
        $cpf = self::limparCpf($cpf);
        if (strlen($cpf) !== 11) {
            return $cpf;
        }

        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }

    public static function criarOuAtualizarPaciente(array $data): int
    {
        $cpf = self::limparCpf($data['paciente_cpf']);
        $paciente = DB::table('pacientes')->where('cpf', $cpf)->first();

        $payload = [
            'nome' => $data['paciente_nome'],
            'data_nascimento' => $data['paciente_nascimento'] ?? null,
            'telefone' => $data['paciente_telefone'] ?? null,
            'endereco' => $data['paciente_endereco'] ?? null,
            'cartao_sus' => $data['paciente_sus'] ?? null,
        ];

        if ($paciente) {
            DB::table('pacientes')->where('id', $paciente->id)->update($payload);

            return (int) $paciente->id;
        }

        return DB::table('pacientes')->insertGetId(array_merge(['cpf' => $cpf], $payload));
    }

    public static function registrarDispensacao(int $clinicaId, int $pacienteId, array $itens, array $extras, int $usuarioId): array
    {
        $erros = [];

        foreach ($itens as $it) {
            $dup = DB::table('saidas_medicamentos as s')
                ->join('clinicas as c', 'c.id', '=', 's.clinica_id')
                ->join('medicamentos as m', 'm.id', '=', 's.medicamento_id')
                ->where('s.paciente_id', $pacienteId)
                ->where('s.medicamento_id', $it['medicamento_id'])
                ->whereDate('s.data_saida', today())
                ->select('s.data_saida', 'c.nome as clinica', 'm.nome as medicamento')
                ->first();

            if ($dup) {
                $erros[] = $dup->medicamento . ' já foi retirado HOJE em ' . $dup->clinica . '.';
                AuditoriaService::registrar(
                    'BLOQUEIO_SAIDA',
                    'CPF ' . self::formatarCpf($extras['cpf'] ?? ''),
                    'Tentativa duplicada do medicamento id=' . $it['medicamento_id']
                );
            }

            $est = EstoqueService::getAtual($clinicaId, $it['medicamento_id']);
            if ($est < $it['quantidade']) {
                $nome = DB::table('medicamentos')->where('id', $it['medicamento_id'])->value('nome') ?: 'Medicamento';
                $erros[] = "Estoque insuficiente para {$nome}. Disponível: {$est}, pedido: {$it['quantidade']}.";
            }
        }

        if (!empty($erros)) {
            throw new RuntimeException(implode(' ', $erros));
        }

        return DB::transaction(function () use ($clinicaId, $pacienteId, $itens, $extras, $usuarioId) {
            $registrados = [];

            foreach ($itens as $it) {
                if (!EstoqueService::subtrair($clinicaId, $it['medicamento_id'], $it['quantidade'])) {
                    throw new RuntimeException('Falha ao debitar estoque.');
                }

                $saidaId = DB::table('saidas_medicamentos')->insertGetId([
                    'clinica_id' => $clinicaId,
                    'medicamento_id' => $it['medicamento_id'],
                    'paciente_id' => $pacienteId,
                    'quantidade' => $it['quantidade'],
                    'prescritor' => $extras['prescritor'] ?? null,
                    'numero_solicitacao' => $extras['numero_solicitacao'] ?? null,
                    'observacao' => $it['observacao'] ?? ($extras['observacao'] ?? null),
                    'usuario_id' => $usuarioId,
                    'data_saida' => now(),
                ]);

                $registrados[] = $saidaId;
            }

            AuditoriaService::registrar(
                'SAIDA_MEDICAMENTO',
                'CPF ' . self::formatarCpf($extras['cpf'] ?? ''),
                count($itens) . ' medicamento(s) dispensado(s)'
            );

            return $registrados;
        });
    }
}

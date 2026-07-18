<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ChatService
{
    public static function chatNaoLidasParaUsuario(int $userId): int
    {
        if (!self::tabelaExiste('chat_mensagens')) {
            return 0;
        }

        return (int) DB::table('chat_mensagens')
            ->where(function ($query) use ($userId) {
                $query->where('destinatario_id', $userId)
                    ->orWhereNull('destinatario_id');
            })
            ->whereNull('lida_em')
            ->where('remetente_id', '<>', $userId)
            ->count();
    }

    /**
     * Lista mensagens de uma conversa (canal geral ou privada) e marca como lidas.
     *
     * @return list<array<string, mixed>>
     */
    public static function listar(int $userId, string|int $alvo): array
    {
        if (!self::tabelaExiste('chat_mensagens')) {
            return [];
        }

        if ($alvo === 'geral') {
            $rows = DB::table('chat_mensagens as m')
                ->join('usuarios as u', 'u.id', '=', 'm.remetente_id')
                ->whereNull('m.destinatario_id')
                ->orderBy('m.enviado_em')
                ->limit(200)
                ->get([
                    'm.id',
                    'm.remetente_id',
                    'u.nome_completo as remetente_nome',
                    'm.mensagem',
                    'm.enviado_em',
                ]);

            DB::table('chat_mensagens')
                ->whereNull('destinatario_id')
                ->whereNull('lida_em')
                ->where('remetente_id', '<>', $userId)
                ->update(['lida_em' => now()]);
        } else {
            $outroId = (int) $alvo;
            if ($outroId <= 0) {
                return [];
            }

            $rows = DB::table('chat_mensagens as m')
                ->join('usuarios as u', 'u.id', '=', 'm.remetente_id')
                ->where(function ($query) use ($userId, $outroId) {
                    $query->where(function ($q) use ($userId, $outroId) {
                        $q->where('m.remetente_id', $userId)
                            ->where('m.destinatario_id', $outroId);
                    })->orWhere(function ($q) use ($userId, $outroId) {
                        $q->where('m.remetente_id', $outroId)
                            ->where('m.destinatario_id', $userId);
                    });
                })
                ->orderBy('m.enviado_em')
                ->limit(200)
                ->get([
                    'm.id',
                    'm.remetente_id',
                    'u.nome_completo as remetente_nome',
                    'm.mensagem',
                    'm.enviado_em',
                ]);

            DB::table('chat_mensagens')
                ->where('destinatario_id', $userId)
                ->where('remetente_id', $outroId)
                ->whereNull('lida_em')
                ->update(['lida_em' => now()]);
        }

        $mensagens = [];
        foreach ($rows as $row) {
            $mensagens[] = [
                'id' => (int) $row->id,
                'remetente_id' => (int) $row->remetente_id,
                'remetente_nome' => (string) $row->remetente_nome,
                'mensagem' => (string) $row->mensagem,
                'enviado_em' => date('d/m/Y H:i', strtotime((string) $row->enviado_em)),
                'eu' => (int) $row->remetente_id === $userId,
            ];
        }

        return $mensagens;
    }

    /**
     * Envia uma mensagem para o canal geral ou para um usuário específico.
     *
     * @return array{ok: bool, erro?: string}
     */
    public static function enviar(int $remetenteId, string|int $alvo, string $mensagem, bool $podeEnviarGeral = false): array
    {
        if (!self::tabelaExiste('chat_mensagens')) {
            return ['ok' => false, 'erro' => 'Chat indisponível.'];
        }

        $mensagem = trim($mensagem);
        if ($mensagem === '' || mb_strlen($mensagem) > 2000) {
            return ['ok' => false, 'erro' => 'Mensagem inválida (vazia ou maior que 2000 caracteres).'];
        }

        if ($alvo === 'geral') {
            if (!$podeEnviarGeral) {
                return ['ok' => false, 'erro' => 'Apenas o Super Usuário pode enviar mensagens no canal geral.'];
            }

            DB::table('chat_mensagens')->insert([
                'remetente_id' => $remetenteId,
                'destinatario_id' => null,
                'mensagem' => $mensagem,
            ]);
        } else {
            $destId = (int) $alvo;
            if ($destId <= 0) {
                return ['ok' => false, 'erro' => 'Destinatário inválido.'];
            }

            DB::table('chat_mensagens')->insert([
                'remetente_id' => $remetenteId,
                'destinatario_id' => $destId,
                'mensagem' => $mensagem,
            ]);
        }

        return ['ok' => true];
    }

    /**
     * Lista usuários disponíveis para conversa privada e contadores de não lidas.
     *
     * @return array{usuarios: list<array<string, mixed>>, geral_nao_lidas: int}
     */
    public static function listarUsuarios(int $userId): array
    {
        if (!self::tabelaExiste('chat_mensagens') || !self::tabelaExiste('usuarios')) {
            return ['usuarios' => [], 'geral_nao_lidas' => 0];
        }

        $usuarios = DB::table('usuarios as u')
            ->leftJoin('clinicas as c', 'c.id', '=', 'u.clinica_id')
            ->where('u.id', '<>', $userId)
            ->where('u.bloqueado', 0)
            ->orderBy('u.nome_completo')
            ->get([
                'u.id',
                'u.nome_completo',
                'u.perfil',
                'c.nome as clinica',
            ]);

        $lista = [];
        foreach ($usuarios as $usuario) {
            $naoLidas = (int) DB::table('chat_mensagens')
                ->where('remetente_id', $usuario->id)
                ->where('destinatario_id', $userId)
                ->whereNull('lida_em')
                ->count();

            $lista[] = [
                'id' => (int) $usuario->id,
                'nome_completo' => (string) $usuario->nome_completo,
                'perfil' => (string) $usuario->perfil,
                'clinica' => $usuario->clinica,
                'nao_lidas' => $naoLidas,
            ];
        }

        $geralNaoLidas = (int) DB::table('chat_mensagens')
            ->whereNull('destinatario_id')
            ->whereNull('lida_em')
            ->where('remetente_id', '<>', $userId)
            ->count();

        return [
            'usuarios' => $lista,
            'geral_nao_lidas' => $geralNaoLidas,
        ];
    }

    private static function tabelaExiste(string $tabela): bool
    {
        try {
            $result = DB::select('SHOW TABLES LIKE ?', [$tabela]);

            return count($result) > 0;
        } catch (\Throwable) {
            return false;
        }
    }
}

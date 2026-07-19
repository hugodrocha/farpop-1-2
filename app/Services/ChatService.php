<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ChatService
{
    public const ONLINE_SEGUNDOS = 90;
    public const MAX_CORPO = 4000;

    public static function inicializar(): void
    {
        ChatSchemaService::garantir();
    }

    public static function usuarioPodeUsar(int $userId): bool
    {
        if (!ConfigService::chatHabilitado()) {
            return false;
        }

        self::inicializar();

        $u = DB::table('usuarios')
            ->where('id', $userId)
            ->first(['bloqueado', 'chat_habilitado']);

        if (!$u || (int) ($u->bloqueado ?? 0) === 1) {
            return false;
        }

        return (int) ($u->chat_habilitado ?? 1) === 1;
    }

    public static function ping(int $userId): void
    {
        self::inicializar();
        DB::table('usuarios')->where('id', $userId)->update(['chat_ultimo_ping' => now()]);
    }

    public static function chatNaoLidasParaUsuario(int $userId): int
    {
        if (!self::usuarioPodeUsar($userId)) {
            return 0;
        }

        self::inicializar();

        $total = 0;

        $conversas = DB::table('chat_conversa_membros')
            ->where('usuario_id', $userId)
            ->pluck('conversa_id');

        foreach ($conversas as $conversaId) {
            $total += self::naoLidasNaConversa((int) $conversaId, $userId);
        }

        return $total;
    }

    /**
     * @return array{contatos: list<array<string, mixed>>, grupos: list<array<string, mixed>>, total_nao_lidas: int}
     */
    public static function contatos(int $meuId): array
    {
        self::ping($meuId);

        $rows = DB::table('usuarios as u')
            ->leftJoin('clinicas as c', 'c.id', '=', 'u.clinica_id')
            ->where('u.id', '<>', $meuId)
            ->where('u.bloqueado', 0)
            ->where('u.chat_habilitado', 1)
            ->orderBy('u.nome_completo')
            ->get([
                'u.id',
                'u.usuario',
                'u.nome_completo',
                'u.perfil',
                'u.cargo',
                'u.chat_ultimo_ping',
                'c.nome as clinica',
            ]);

        $contatos = [];
        foreach ($rows as $r) {
            $outroId = (int) $r->id;
            $conversaId = self::obterOuCriarConversaPrivada($meuId, $outroId);
            $ultima = DB::table('chat_mensagens')
                ->where('conversa_id', $conversaId)
                ->max('enviado_em');

            $contatos[] = [
                'id' => $outroId,
                'usuario' => (string) $r->usuario,
                'nome_completo' => (string) $r->nome_completo,
                'perfil' => (string) $r->perfil,
                'setor' => (string) ($r->clinica ?? $r->cargo ?? ''),
                'online' => self::estaOnline($r->chat_ultimo_ping),
                'nao_lidas' => self::naoLidasNaConversa($conversaId, $meuId, $outroId),
                'conversa_id' => $conversaId,
                'ultima_msg_em' => $ultima ?: null,
            ];
        }

        usort($contatos, function ($a, $b) {
            if ($a['online'] !== $b['online']) {
                return $b['online'] <=> $a['online'];
            }
            $ta = $a['ultima_msg_em'] ? strtotime((string) $a['ultima_msg_em']) : 0;
            $tb = $b['ultima_msg_em'] ? strtotime((string) $b['ultima_msg_em']) : 0;
            if ($ta !== $tb) {
                return $tb <=> $ta;
            }

            return strcasecmp($a['nome_completo'], $b['nome_completo']);
        });

        $grupos = self::listarGrupos($meuId);

        return [
            'contatos' => $contatos,
            'grupos' => $grupos,
            'total_nao_lidas' => self::chatNaoLidasParaUsuario($meuId),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function mensagens(int $meuId, int $conversaId, int $sinceId = 0): array
    {
        if (!self::usuarioParticipaConversa($meuId, $conversaId)) {
            return [];
        }

        $rows = DB::table('chat_mensagens as m')
            ->join('usuarios as u', 'u.id', '=', 'm.remetente_id')
            ->where('m.conversa_id', $conversaId)
            ->where('m.id', '>', $sinceId)
            ->orderBy('m.id')
            ->limit(500)
            ->get([
                'm.id',
                'm.remetente_id',
                'u.nome_completo as remetente_nome',
                'm.mensagem',
                'm.enviado_em',
            ]);

        $lista = [];
        foreach ($rows as $m) {
            $lista[] = [
                'id' => (int) $m->id,
                'remetente_id' => (int) $m->remetente_id,
                'remetente_nome' => (string) $m->remetente_nome,
                'corpo' => (string) $m->mensagem,
                'mensagem' => (string) $m->mensagem,
                'criado_em' => self::formatarDataHoraBr($m->enviado_em),
                'enviado_em' => $m->enviado_em,
                'eu' => (int) $m->remetente_id === $meuId,
            ];
        }

        return $lista;
    }

    public static function marcarLido(int $meuId, int $conversaId): int
    {
        if (!self::usuarioParticipaConversa($meuId, $conversaId)) {
            return 0;
        }

        $maxId = (int) DB::table('chat_mensagens')
            ->where('conversa_id', $conversaId)
            ->max('id');

        DB::statement('
            INSERT INTO chat_leituras (conversa_id, usuario_id, ultimo_id_lido, lido_em)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                ultimo_id_lido = GREATEST(ultimo_id_lido, VALUES(ultimo_id_lido)),
                lido_em = NOW()
        ', [$conversaId, $meuId, $maxId]);

        return $maxId;
    }

    /**
     * @return array{ok: bool, erro?: string, mensagem?: array<string, mixed>, conversa_id?: int}
     */
    public static function enviar(int $meuId, int $conversaId, string $texto): array
    {
        $texto = trim($texto);
        if ($texto === '') {
            return ['ok' => false, 'erro' => 'Mensagem vazia.'];
        }
        if (mb_strlen($texto) > self::MAX_CORPO) {
            return ['ok' => false, 'erro' => 'Mensagem muito longa (máx. 4000 caracteres).'];
        }
        if (!self::usuarioParticipaConversa($meuId, $conversaId)) {
            return ['ok' => false, 'erro' => 'Conversa indisponível.'];
        }

        $novoId = DB::table('chat_mensagens')->insertGetId([
            'conversa_id' => $conversaId,
            'remetente_id' => $meuId,
            'destinatario_id' => null,
            'mensagem' => $texto,
            'enviado_em' => now(),
        ]);

        DB::table('chat_conversas')->where('id', $conversaId)->update(['atualizado_em' => now()]);
        self::marcarLido($meuId, $conversaId);

        return [
            'ok' => true,
            'conversa_id' => $conversaId,
            'mensagem' => [
                'id' => $novoId,
                'remetente_id' => $meuId,
                'corpo' => $texto,
                'mensagem' => $texto,
                'criado_em' => self::formatarDataHoraBr(now()),
            ],
        ];
    }

    /**
     * @return array{ok: bool, erro?: string, conversa_id?: int}
     */
    public static function enviarParaUsuario(int $meuId, int $outroId, string $texto): array
    {
        if ($outroId <= 0 || $outroId === $meuId) {
            return ['ok' => false, 'erro' => 'Usuário inválido.'];
        }
        if (!self::usuarioPodeUsar($outroId)) {
            return ['ok' => false, 'erro' => 'Destinatário indisponível para o chat.'];
        }

        $conversaId = self::obterOuCriarConversaPrivada($meuId, $outroId);

        return self::enviar($meuId, $conversaId, $texto);
    }

    /**
     * @param list<int> $membrosIds
     * @return array{ok: bool, erro?: string, conversa_id?: int}
     */
    public static function criarGrupo(int $meuId, string $titulo, array $membrosIds): array
    {
        $titulo = trim($titulo);
        if ($titulo === '') {
            return ['ok' => false, 'erro' => 'Informe o nome do grupo.'];
        }

        $membros = array_unique(array_filter(array_map('intval', $membrosIds), fn ($id) => $id > 0 && $id !== $meuId));
        if (count($membros) < 1) {
            return ['ok' => false, 'erro' => 'Selecione ao menos um participante.'];
        }

        foreach ($membros as $mid) {
            if (!self::usuarioPodeUsar($mid)) {
                return ['ok' => false, 'erro' => 'Um ou mais participantes não podem usar o chat.'];
            }
        }

        $conversaId = DB::table('chat_conversas')->insertGetId([
            'tipo' => 'grupo',
            'titulo' => mb_substr($titulo, 0, 180),
            'criado_por' => $meuId,
            'criado_em' => now(),
            'atualizado_em' => now(),
        ]);

        $insert = [['conversa_id' => $conversaId, 'usuario_id' => $meuId]];
        foreach ($membros as $mid) {
            $insert[] = ['conversa_id' => $conversaId, 'usuario_id' => $mid];
        }
        DB::table('chat_conversa_membros')->insert($insert);

        return ['ok' => true, 'conversa_id' => (int) $conversaId];
    }

    public static function obterOuCriarConversaPrivada(int $a, int $b): int
    {
        self::inicializar();

        if ($a === $b) {
            return 0;
        }

        $menor = min($a, $b);
        $maior = max($a, $b);

        $existente = DB::table('chat_conversas')
            ->where('tipo', 'privada')
            ->where('usuario_menor_id', $menor)
            ->where('usuario_maior_id', $maior)
            ->value('id');

        if ($existente) {
            return (int) $existente;
        }

        $id = DB::table('chat_conversas')->insertGetId([
            'tipo' => 'privada',
            'usuario_menor_id' => $menor,
            'usuario_maior_id' => $maior,
            'criado_por' => $a,
            'criado_em' => now(),
            'atualizado_em' => now(),
        ]);

        DB::table('chat_conversa_membros')->insert([
            ['conversa_id' => $id, 'usuario_id' => $menor],
            ['conversa_id' => $id, 'usuario_id' => $maior],
        ]);

        return (int) $id;
    }

    public static function conversaIdPorOutro(int $meuId, int $outroId): int
    {
        return self::obterOuCriarConversaPrivada($meuId, $outroId);
    }

    private static function listarGrupos(int $meuId): array
    {
        $rows = DB::table('chat_conversas as c')
            ->join('chat_conversa_membros as cm', 'cm.conversa_id', '=', 'c.id')
            ->where('cm.usuario_id', $meuId)
            ->where('c.tipo', 'grupo')
            ->orderByDesc('c.atualizado_em')
            ->get(['c.id', 'c.titulo', 'c.atualizado_em']);

        $grupos = [];
        foreach ($rows as $g) {
            $cid = (int) $g->id;
            $grupos[] = [
                'conversa_id' => $cid,
                'titulo' => (string) $g->titulo,
                'nao_lidas' => self::naoLidasNaConversa($cid, $meuId),
                'ultima_msg_em' => $g->atualizado_em,
                'membros' => DB::table('chat_conversa_membros')->where('conversa_id', $cid)->count(),
            ];
        }

        return $grupos;
    }

    private static function naoLidasNaConversa(int $conversaId, int $meuId, ?int $somenteDe = null): int
    {
        $ultimoLido = (int) DB::table('chat_leituras')
            ->where('conversa_id', $conversaId)
            ->where('usuario_id', $meuId)
            ->value('ultimo_id_lido');

        $q = DB::table('chat_mensagens')
            ->where('conversa_id', $conversaId)
            ->where('id', '>', $ultimoLido)
            ->where('remetente_id', '<>', $meuId);

        if ($somenteDe !== null) {
            $q->where('remetente_id', $somenteDe);
        }

        return (int) $q->count();
    }

    private static function usuarioParticipaConversa(int $userId, int $conversaId): bool
    {
        return DB::table('chat_conversa_membros')
            ->where('conversa_id', $conversaId)
            ->where('usuario_id', $userId)
            ->exists();
    }

    private static function estaOnline(?string $ultimoPing): bool
    {
        if (!$ultimoPing) {
            return false;
        }

        return (time() - strtotime($ultimoPing)) <= self::ONLINE_SEGUNDOS;
    }

    private static function formatarDataHoraBr($dataHora): string
    {
        if (!$dataHora) {
            return '';
        }
        $ts = strtotime((string) $dataHora);

        return $ts ? date('d/m/Y H:i', $ts) : (string) $dataHora;
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChatSchemaService
{
    private static bool $garantido = false;

    public static function garantir(): void
    {
        if (self::$garantido) {
            return;
        }

        if (!Schema::hasTable('usuarios')) {
            return;
        }

        if (!Schema::hasColumn('usuarios', 'chat_habilitado')) {
            DB::statement('ALTER TABLE usuarios ADD COLUMN chat_habilitado TINYINT(1) NOT NULL DEFAULT 1');
        }
        if (!Schema::hasColumn('usuarios', 'chat_ultimo_ping')) {
            DB::statement('ALTER TABLE usuarios ADD COLUMN chat_ultimo_ping DATETIME NULL');
        }

        if (!Schema::hasTable('chat_conversas')) {
            DB::statement("
                CREATE TABLE chat_conversas (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    tipo ENUM('privada','grupo') NOT NULL DEFAULT 'privada',
                    titulo VARCHAR(180) NULL,
                    usuario_menor_id INT UNSIGNED NULL,
                    usuario_maior_id INT UNSIGNED NULL,
                    criado_por INT UNSIGNED NULL,
                    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY uk_chat_privada (usuario_menor_id, usuario_maior_id),
                    KEY idx_chat_tipo (tipo),
                    KEY idx_chat_atualizado (atualizado_em)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        if (!Schema::hasTable('chat_conversa_membros')) {
            DB::statement("
                CREATE TABLE chat_conversa_membros (
                    conversa_id BIGINT UNSIGNED NOT NULL,
                    usuario_id INT UNSIGNED NOT NULL,
                    PRIMARY KEY (conversa_id, usuario_id),
                    KEY idx_membro_usuario (usuario_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        if (!Schema::hasTable('chat_leituras')) {
            DB::statement("
                CREATE TABLE chat_leituras (
                    conversa_id BIGINT UNSIGNED NOT NULL,
                    usuario_id INT UNSIGNED NOT NULL,
                    ultimo_id_lido BIGINT UNSIGNED NOT NULL DEFAULT 0,
                    lido_em DATETIME NULL,
                    PRIMARY KEY (conversa_id, usuario_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        if (Schema::hasTable('chat_mensagens') && !Schema::hasColumn('chat_mensagens', 'conversa_id')) {
            DB::statement('ALTER TABLE chat_mensagens ADD COLUMN conversa_id BIGINT UNSIGNED NULL AFTER id');
            DB::statement('ALTER TABLE chat_mensagens ADD KEY idx_chat_msg_conversa (conversa_id, id)');
        }

        self::migrarMensagensLegadas();

        self::$garantido = true;
    }

    private static function criarConversaPrivada(int $a, int $b): int
    {
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

    private static function migrarMensagensLegadas(): void
    {
        if (!Schema::hasTable('chat_mensagens') || !Schema::hasColumn('chat_mensagens', 'destinatario_id')) {
            return;
        }

        $pendentes = DB::table('chat_mensagens')
            ->whereNull('conversa_id')
            ->whereNotNull('destinatario_id')
            ->select('remetente_id', 'destinatario_id')
            ->distinct()
            ->get();

        foreach ($pendentes as $row) {
            $conversaId = self::criarConversaPrivada((int) $row->remetente_id, (int) $row->destinatario_id);
            if ($conversaId <= 0) {
                continue;
            }

            DB::table('chat_mensagens')
                ->whereNull('conversa_id')
                ->where(function ($q) use ($row) {
                    $q->where(function ($q2) use ($row) {
                        $q2->where('remetente_id', $row->remetente_id)
                            ->where('destinatario_id', $row->destinatario_id);
                    })->orWhere(function ($q2) use ($row) {
                        $q2->where('remetente_id', $row->destinatario_id)
                            ->where('destinatario_id', $row->remetente_id);
                    });
                })
                ->update(['conversa_id' => $conversaId]);
        }
    }
}

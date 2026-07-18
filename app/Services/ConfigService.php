<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ConfigService
{
    private static array $cache = [];

    public static function get(string $chave, string $default = ''): string
    {
        if (array_key_exists($chave, self::$cache)) {
            return self::$cache[$chave];
        }

        $row = DB::table('configuracoes')->where('chave', $chave)->value('valor');

        self::$cache[$chave] = $row !== null ? (string) $row : $default;

        return self::$cache[$chave];
    }

    public static function set(string $chave, string $valor, ?string $descricao = null): void
    {
        DB::table('configuracoes')->updateOrInsert(
            ['chave' => $chave],
            array_filter([
                'valor' => $valor,
                'descricao' => $descricao,
            ], fn ($v) => $v !== null)
        );

        self::$cache[$chave] = $valor;
    }

    public static function chatHabilitado(): bool
    {
        return self::get('chat_habilitado', '1') === '1';
    }
}

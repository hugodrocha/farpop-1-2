<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AuditoriaService
{
    public static function registrar(string $acao, ?string $alvo = null, ?string $detalhes = null): void
    {
        DB::table('auditoria')->insert([
            'usuario_responsavel' => session('usuario', 'sistema'),
            'nome_responsavel' => session('nome_completo', 'Sistema'),
            'acao' => $acao,
            'alvo' => $alvo,
            'detalhes' => $detalhes,
            'criado_em' => now(),
        ]);
    }
}

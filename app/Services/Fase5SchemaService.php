<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Fase5SchemaService
{
    private static bool $garantido = false;

    public static function garantir(): void
    {
        if (self::$garantido) {
            return;
        }

        if (Schema::hasTable('transferencias') && !Schema::hasColumn('transferencias', 'tipo_transferencia')) {
            DB::statement("
                ALTER TABLE transferencias
                ADD COLUMN tipo_transferencia ENUM('matriz_unidade','entre_unidades','unidade_matriz')
                NOT NULL DEFAULT 'matriz_unidade'
                AFTER clinica_destino_id
            ");
        }

        self::$garantido = true;
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClinicaSchemaService
{
    private static bool $garantido = false;

    public static function garantir(): void
    {
        if (self::$garantido) {
            return;
        }

        if (!Schema::hasTable('clinicas')) {
            return;
        }

        if (!Schema::hasColumn('clinicas', 'cnes')) {
            DB::statement('ALTER TABLE clinicas ADD COLUMN cnes VARCHAR(20) NULL AFTER nome');
        }
        if (!Schema::hasColumn('clinicas', 'cnpj')) {
            DB::statement('ALTER TABLE clinicas ADD COLUMN cnpj VARCHAR(20) NULL AFTER cnes');
        }

        self::$garantido = true;
    }
}

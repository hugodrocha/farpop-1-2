<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LoteSchemaService
{
    private static bool $garantido = false;

    public static function garantir(): void
    {
        if (self::$garantido) {
            return;
        }

        if (!Schema::hasTable('estoque_lotes')) {
            DB::statement("
                CREATE TABLE estoque_lotes (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    clinica_id INT UNSIGNED NOT NULL,
                    medicamento_id INT UNSIGNED NOT NULL,
                    lote VARCHAR(80) NULL,
                    fabricante VARCHAR(120) NULL,
                    data_fabricacao DATE NULL,
                    validade DATE NULL,
                    quantidade INT NOT NULL DEFAULT 0,
                    quantidade_inicial INT NOT NULL DEFAULT 0,
                    entrada_id BIGINT UNSIGNED NULL,
                    bloqueado TINYINT(1) NOT NULL DEFAULT 0,
                    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    KEY idx_lote_clinica_med (clinica_id, medicamento_id),
                    KEY idx_lote_validade (validade),
                    KEY idx_lote_saldo (clinica_id, medicamento_id, quantidade)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        if (!Schema::hasTable('estoque_movimentacoes')) {
            DB::statement("
                CREATE TABLE estoque_movimentacoes (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    tipo VARCHAR(40) NOT NULL,
                    clinica_id INT UNSIGNED NOT NULL,
                    medicamento_id INT UNSIGNED NOT NULL,
                    lote_id BIGINT UNSIGNED NULL,
                    quantidade INT NOT NULL,
                    saldo_lote_apos INT NULL,
                    referencia_tipo VARCHAR(40) NULL,
                    referencia_id BIGINT UNSIGNED NULL,
                    observacao VARCHAR(255) NULL,
                    usuario_id INT UNSIGNED NULL,
                    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    KEY idx_mov_clinica_data (clinica_id, criado_em),
                    KEY idx_mov_med (medicamento_id),
                    KEY idx_mov_lote (lote_id),
                    KEY idx_mov_tipo (tipo)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        if (!Schema::hasTable('saidas_lotes')) {
            DB::statement("
                CREATE TABLE saidas_lotes (
                    saida_id BIGINT UNSIGNED NOT NULL,
                    lote_id BIGINT UNSIGNED NOT NULL,
                    quantidade INT NOT NULL,
                    PRIMARY KEY (saida_id, lote_id),
                    KEY idx_saida_lote (lote_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        if (Schema::hasTable('entradas_medicamentos') && !Schema::hasColumn('entradas_medicamentos', 'fabricante')) {
            DB::statement('ALTER TABLE entradas_medicamentos ADD COLUMN fabricante VARCHAR(120) NULL AFTER lote');
        }

        if (Schema::hasTable('entradas_medicamentos') && !Schema::hasColumn('entradas_medicamentos', 'tipo_entrada')) {
            DB::statement("ALTER TABLE entradas_medicamentos ADD COLUMN tipo_entrada VARCHAR(40) NULL DEFAULT 'entrada_compra' AFTER origem");
        }

        LoteService::migrarLegadoSeNecessario();

        self::$garantido = true;
    }
}

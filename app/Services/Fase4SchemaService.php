<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Fase4SchemaService
{
    private static bool $garantido = false;

    public static function garantir(): void
    {
        if (self::$garantido) {
            return;
        }

        if (!Schema::hasTable('fornecedores')) {
            DB::statement("
                CREATE TABLE fornecedores (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    nome VARCHAR(180) NOT NULL,
                    cnpj VARCHAR(20) NULL,
                    telefone VARCHAR(40) NULL,
                    email VARCHAR(180) NULL,
                    endereco VARCHAR(255) NULL,
                    ativo TINYINT(1) NOT NULL DEFAULT 1,
                    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        if (!Schema::hasTable('fabricantes')) {
            DB::statement("
                CREATE TABLE fabricantes (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    nome VARCHAR(180) NOT NULL,
                    cnpj VARCHAR(20) NULL,
                    ativo TINYINT(1) NOT NULL DEFAULT 1,
                    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        if (!Schema::hasTable('transferencia_solicitacoes')) {
            DB::statement("
                CREATE TABLE transferencia_solicitacoes (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    codigo VARCHAR(20) NOT NULL,
                    clinica_solicitante_id INT UNSIGNED NOT NULL,
                    clinica_origem_id INT UNSIGNED NOT NULL,
                    status ENUM('solicitada','aprovada','recusada','em_separacao','enviada','cancelada') NOT NULL DEFAULT 'solicitada',
                    observacao VARCHAR(500) NULL,
                    motivo_recusa VARCHAR(500) NULL,
                    usuario_solicitante_id INT UNSIGNED NULL,
                    usuario_aprovacao_id INT UNSIGNED NULL,
                    transferencia_id BIGINT UNSIGNED NULL,
                    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    aprovado_em DATETIME NULL,
                    separado_em DATETIME NULL,
                    enviado_em DATETIME NULL,
                    UNIQUE KEY uk_sol_codigo (codigo),
                    KEY idx_sol_status (status),
                    KEY idx_sol_solicitante (clinica_solicitante_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        if (!Schema::hasTable('transferencia_solicitacao_itens')) {
            DB::statement("
                CREATE TABLE transferencia_solicitacao_itens (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    solicitacao_id BIGINT UNSIGNED NOT NULL,
                    medicamento_id INT UNSIGNED NOT NULL,
                    quantidade_solicitada INT NOT NULL,
                    quantidade_aprovada INT NULL,
                    observacao VARCHAR(255) NULL,
                    KEY idx_sol_item (solicitacao_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        if (!Schema::hasTable('reservas_medicamentos')) {
            DB::statement("
                CREATE TABLE reservas_medicamentos (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    paciente_id INT UNSIGNED NOT NULL,
                    medicamento_id INT UNSIGNED NOT NULL,
                    clinica_id INT UNSIGNED NOT NULL,
                    quantidade INT NOT NULL,
                    quantidade_atendida INT NOT NULL DEFAULT 0,
                    status ENUM('ativa','atendida','cancelada','expirada') NOT NULL DEFAULT 'ativa',
                    validade_reserva DATE NULL,
                    observacao VARCHAR(255) NULL,
                    usuario_id INT UNSIGNED NULL,
                    saida_id BIGINT UNSIGNED NULL,
                    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    KEY idx_reserva_paciente (paciente_id),
                    KEY idx_reserva_status (status, clinica_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        if (Schema::hasTable('entradas_medicamentos') && !Schema::hasColumn('entradas_medicamentos', 'fornecedor_id')) {
            DB::statement('ALTER TABLE entradas_medicamentos ADD COLUMN fornecedor_id INT UNSIGNED NULL AFTER origem');
        }

        self::$garantido = true;
    }
}

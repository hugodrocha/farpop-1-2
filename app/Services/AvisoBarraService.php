<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class AvisoBarraService
{
    /**
     * Retorna os avisos vigentes (ativos, dentro do período) destinados
     * a uma determinada clínica (ou todos os globais se $clinicaId for null).
     *
     * Regra: aparece se `para_todas = 1` OU se a clínica está em `aviso_clinicas`.
     *
     * @return list<array<string, mixed>>
     */
    public static function buscarAvisosBarraAtivos(?int $clinicaId): array
    {
        if (!self::tabelaExiste('avisos_barra')) {
            return [];
        }

        $orderRaw = "FIELD(a.prioridade, 'urgente','alerta','aviso','info'), a.criado_em DESC";

        if ($clinicaId !== null && $clinicaId > 0) {
            $rows = DB::select("
                SELECT DISTINCT a.id, a.mensagem, a.prioridade, a.cor, a.termina_em, a.para_todas,
                       u.nome_completo AS autor
                FROM avisos_barra a
                INNER JOIN usuarios u ON u.id = a.criado_por_id
                LEFT JOIN aviso_clinicas ac ON ac.aviso_id = a.id
                WHERE a.ativo = 1
                  AND a.inicia_em <= NOW()
                  AND a.termina_em >= NOW()
                  AND (a.para_todas = 1 OR ac.clinica_id = ?)
                ORDER BY {$orderRaw}
                LIMIT 20
            ", [$clinicaId]);
        } else {
            $rows = DB::select("
                SELECT a.id, a.mensagem, a.prioridade, a.cor, a.termina_em, a.para_todas,
                       u.nome_completo AS autor
                FROM avisos_barra a
                INNER JOIN usuarios u ON u.id = a.criado_por_id
                WHERE a.ativo = 1
                  AND a.inicia_em <= NOW()
                  AND a.termina_em >= NOW()
                ORDER BY {$orderRaw}
                LIMIT 20
            ");
        }

        return array_map(static fn ($row) => (array) $row, $rows);
    }

    /**
     * Retorna a cor padrão de cada prioridade (gradiente/CSS).
     *
     * @return array{bg: string, label: string, border: string}
     */
    public static function corPrioridadeAviso(string $prio): array
    {
        $mapa = [
            'info' => [
                'bg' => 'linear-gradient(135deg,#0f766e,#14b8a6)',
                'label' => 'ℹ️ INFO',
                'border' => '#064e3b',
            ],
            'aviso' => [
                'bg' => 'linear-gradient(135deg,#064e3b,#0f766e)',
                'label' => '📣 AVISO',
                'border' => '#022c22',
            ],
            'alerta' => [
                'bg' => 'linear-gradient(135deg,#d97706,#b45309)',
                'label' => '⚠️ ALERTA',
                'border' => '#92400e',
            ],
            'urgente' => [
                'bg' => 'linear-gradient(135deg,#c53030,#9b2c2c)',
                'label' => '🚨 URGENTE',
                'border' => '#7a2222',
            ],
        ];

        return $mapa[$prio] ?? $mapa['info'];
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

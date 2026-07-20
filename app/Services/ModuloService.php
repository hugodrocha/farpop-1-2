<?php

namespace App\Services;

class ModuloService
{
    public static function labelPerfil(string $perfil): string
    {
        return config("farpop.perfis.{$perfil}.label")
            ?? PerfilService::nome($perfil);
    }

    public static function descricaoPerfil(string $perfil): string
    {
        return (string) config("farpop.perfis.{$perfil}.descricao", '');
    }

    public static function modulos(): array
    {
        return config('farpop.modulos', []);
    }

    public static function statusModulo(string $modulo): string
    {
        return (string) (config("farpop.modulos.{$modulo}.status") ?? 'planejado');
    }

    public static function moduloAtivo(string $modulo): bool
    {
        return in_array(self::statusModulo($modulo), ['ativo', 'parcial'], true);
    }

    /**
     * Verifica se o perfil pode acessar um módulo principal.
     */
    public static function podeAcessar(string $perfil, string $modulo): bool
    {
        if (!self::moduloAtivo($modulo)) {
            return false;
        }

        $perfis = config("farpop.modulos.{$modulo}.perfis", []);

        return in_array($perfil, $perfis, true);
    }

    /**
     * Verifica submódulo (ex.: cadastros.pacientes).
     */
    public static function podeAcessarSubmodulo(string $perfil, string $modulo, string $submodulo): bool
    {
        if (!self::podeAcessar($perfil, $modulo)) {
            return false;
        }

        $sub = config("farpop.modulos.{$modulo}.submodulos.{$submodulo}");
        if (!$sub) {
            return true;
        }

        if (($sub['status'] ?? '') === 'planejado') {
            return false;
        }

        $perfisSub = $sub['perfis'] ?? config("farpop.modulos.{$modulo}.perfis", []);

        return in_array($perfil, $perfisSub, true);
    }

    /**
     * @return list<array{modulo: string, label: string, status: string, icone: string}>
     */
    public static function menuPrincipal(string $perfil): array
    {
        $itens = [];

        foreach (self::modulos() as $chave => $cfg) {
            if (!self::podeAcessar($perfil, $chave)) {
                continue;
            }

            $itens[] = [
                'modulo' => $chave,
                'label' => (string) ($cfg['label'] ?? $chave),
                'status' => (string) ($cfg['status'] ?? 'planejado'),
                'icone' => (string) ($cfg['icone'] ?? 'fa-circle'),
            ];
        }

        return $itens;
    }

    public static function roadmap(): array
    {
        return config('farpop.roadmap', []);
    }

    public static function tiposMovimentacao(): array
    {
        return config('farpop.movimentacoes', []);
    }

    /**
     * Relatórios previstos vs disponíveis (hub).
     *
     * @return list<array{slug: string, label: string, status: string}>
     */
    public static function catalogoRelatorios(): array
    {
        return [
            ['slug' => 'estoque_geral', 'label' => 'Estoque geral', 'status' => 'ativo'],
            ['slug' => 'estoque_unidade', 'label' => 'Estoque por unidade', 'status' => 'ativo'],
            ['slug' => 'vencendo', 'label' => 'Medicamentos vencendo', 'status' => 'ativo'],
            ['slug' => 'vencidos', 'label' => 'Medicamentos vencidos', 'status' => 'ativo'],
            ['slug' => 'dispensa_paciente', 'label' => 'Dispensa por paciente', 'status' => 'ativo'],
            ['slug' => 'dispensa_medicamento', 'label' => 'Dispensa por medicamento', 'status' => 'ativo'],
            ['slug' => 'transferencias', 'label' => 'Transferências', 'status' => 'ativo'],
            ['slug' => 'movimentacoes', 'label' => 'Histórico de movimentações', 'status' => 'ativo'],
            ['slug' => 'inventario', 'label' => 'Inventário (lotes)', 'status' => 'ativo'],
            ['slug' => 'consumo_mensal', 'label' => 'Consumo mensal', 'status' => 'ativo'],
            ['slug' => 'consumo_anual', 'label' => 'Consumo anual', 'status' => 'ativo'],
            ['slug' => 'curva_abc', 'label' => 'Curva ABC (consumo)', 'status' => 'ativo'],
        ];
    }
}

<?php

namespace App\Services;

class PerfilService
{
    public static function nome(string $perfil): string
    {
        return match ($perfil) {
            'superuser' => 'Super Usuário',
            'admin' => 'Administrador',
            'gestor' => 'Gestor',
            'farmaceutico' => 'Farmacêutico',
            'tecnico' => 'Técnico',
            'consulta' => 'Consulta',
            default => ucfirst($perfil),
        };
    }

    public static function ehAdmin(string $perfil): bool
    {
        return in_array($perfil, ['superuser', 'admin', 'gestor'], true);
    }

    public static function ehOperacional(string $perfil): bool
    {
        return in_array($perfil, ['superuser', 'admin', 'gestor', 'farmaceutico', 'tecnico'], true);
    }

    public static function ehConsulta(string $perfil): bool
    {
        return $perfil === 'consulta';
    }

    public static function podeAcessarGestaoUsuarios(string $perfil): bool
    {
        return in_array($perfil, ['superuser', 'admin', 'gestor', 'farmaceutico'], true);
    }

    public static function perfisQuePodeCriar(string $perfil): array
    {
        return match ($perfil) {
            'superuser' => ['superuser', 'admin', 'gestor', 'farmaceutico', 'tecnico', 'consulta'],
            'admin' => ['gestor', 'farmaceutico', 'tecnico', 'consulta'],
            'gestor' => ['farmaceutico', 'tecnico'],
            'farmaceutico' => ['farmaceutico', 'tecnico'],
            default => [],
        };
    }

    public static function podeGerenciarUsuarioAlvo(
        string $perfilLogado,
        ?int $clinicaLogadaId,
        string $perfilAlvo,
        ?int $clinicaAlvoId
    ): bool {
        if ($perfilLogado === 'superuser') {
            return true;
        }
        if ($perfilLogado === 'admin') {
            return in_array($perfilAlvo, ['gestor', 'farmaceutico', 'tecnico', 'consulta'], true);
        }
        if ($perfilLogado === 'gestor') {
            return in_array($perfilAlvo, ['farmaceutico', 'tecnico'], true);
        }
        if ($perfilLogado === 'farmaceutico') {
            if (!$clinicaLogadaId || !$clinicaAlvoId || $clinicaLogadaId !== $clinicaAlvoId) {
                return false;
            }

            return in_array($perfilAlvo, ['farmaceutico', 'tecnico'], true);
        }

        return false;
    }

    public static function podeCancelarTransferencia(string $perfil, int $clinicaUsuarioId, ?int $centralId): bool
    {
        if (in_array($perfil, ['superuser', 'admin'], true)) {
            return true;
        }

        return $perfil === 'farmaceutico' && $centralId && $clinicaUsuarioId === $centralId;
    }

    public static function podeEnviarTransferencia(string $perfil, int $clinicaUsuarioId, ?int $centralId): bool
    {
        if (self::ehAdmin($perfil)) {
            return true;
        }

        return $perfil === 'farmaceutico' && $centralId && $clinicaUsuarioId === $centralId;
    }
}

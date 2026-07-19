<?php

namespace App\Services;

class ValidadeService
{
    public static function normalizar(?string $valor): ?string
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            return $valor;
        }

        if (preg_match('/^(\d{1,2})\/(\d{4})$/', $valor, $m)) {
            $mes = (int) $m[1];
            $ano = (int) $m[2];
            if ($mes >= 1 && $mes <= 12) {
                $ultimoDia = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

                return sprintf('%04d-%02d-%02d', $ano, $mes, $ultimoDia);
            }
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $valor, $m)) {
            $dia = (int) $m[1];
            $mes = (int) $m[2];
            $ano = (int) $m[3];
            if (checkdate($mes, $dia, $ano)) {
                return sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
            }
        }

        return null;
    }

    public static function formatar(?string $valor): ?string
    {
        if ($valor === null || trim($valor) === '') {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{2})-\d{2}$/', $valor, $m)) {
            return $m[2] . '/' . $m[1];
        }

        return $valor;
    }

    public static function valida(?string $valor): bool
    {
        if ($valor === null || trim($valor) === '') {
            return true;
        }

        return self::normalizar($valor) !== null;
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MedicamentoBuscaService
{
    public static function normalizarCodigo(string $codigo): string
    {
        return preg_replace('/\D/', '', trim($codigo));
    }

    public static function buscarPorCodigoOuBarras(string $termo): ?object
    {
        $termo = trim($termo);
        if ($termo === '') {
            return null;
        }

        $digitos = self::normalizarCodigo($termo);
        $formatado = strlen($digitos) === 11 ? RemumeCodigoService::formatarCodigo($digitos) : $termo;

        return DB::table('medicamentos')
            ->where('ativo', 1)
            ->where(function ($q) use ($termo, $digitos, $formatado) {
                $q->where('codigo_barras', $termo)
                    ->orWhere('codigo', $termo)
                    ->orWhere('codigo', $formatado);
                if ($digitos !== '') {
                    $q->orWhereRaw("REPLACE(REPLACE(REPLACE(codigo, '.', ''), '-', ''), ' ', '') = ?", [$digitos]);
                }
            })
            ->first();
    }

    public static function autocomplete(string $termo, int $limit = 15): Collection
    {
        $termo = trim($termo);
        if (mb_strlen($termo) < 2) {
            return collect();
        }

        return DB::table('medicamentos')
            ->where('ativo', 1)
            ->where(function ($q) use ($termo) {
                $q->where('nome', 'like', '%' . $termo . '%')
                    ->orWhere('principio_ativo', 'like', '%' . $termo . '%')
                    ->orWhere('codigo', 'like', '%' . $termo . '%')
                    ->orWhere('codigo_barras', 'like', '%' . $termo . '%');
            })
            ->orderBy('nome')
            ->limit($limit)
            ->get(['id', 'nome', 'dosagem', 'forma_farmaceutica', 'unidade', 'codigo', 'codigo_barras']);
    }

    public static function payload(object $med, ?int $clinicaId = null): array
    {
        $estoque = $clinicaId ? EstoqueService::getAtual($clinicaId, (int) $med->id) : null;

        return [
            'id' => (int) $med->id,
            'nome' => $med->nome,
            'dosagem' => $med->dosagem,
            'forma_farmaceutica' => $med->forma_farmaceutica ?? null,
            'unidade' => $med->unidade,
            'codigo' => $med->codigo,
            'codigo_barras' => $med->codigo_barras,
            'estoque' => $estoque,
            'label' => trim($med->nome . ' ' . ($med->dosagem ?? '') . ' ' . ($med->forma_farmaceutica ?? '')),
        ];
    }
}

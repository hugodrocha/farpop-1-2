<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class EstoqueService
{
    public static function getAtual(int $clinicaId, int $medicamentoId): int
    {
        return (int) DB::table('estoque')
            ->where('clinica_id', $clinicaId)
            ->where('medicamento_id', $medicamentoId)
            ->value('quantidade');
    }

    public static function somarOuCriar(int $clinicaId, int $medicamentoId, int $quantidade): void
    {
        $estoque = DB::table('estoque')
            ->where('clinica_id', $clinicaId)
            ->where('medicamento_id', $medicamentoId)
            ->first();

        if ($estoque) {
            DB::table('estoque')
                ->where('clinica_id', $clinicaId)
                ->where('medicamento_id', $medicamentoId)
                ->update([
                    'quantidade' => (int) $estoque->quantidade + $quantidade,
                    'atualizado_em' => now(),
                ]);
        } else {
            DB::table('estoque')->insert([
                'clinica_id' => $clinicaId,
                'medicamento_id' => $medicamentoId,
                'quantidade' => $quantidade,
                'atualizado_em' => now(),
            ]);
        }
    }

    public static function subtrair(int $clinicaId, int $medicamentoId, int $quantidade): bool
    {
        $affected = DB::table('estoque')
            ->where('clinica_id', $clinicaId)
            ->where('medicamento_id', $medicamentoId)
            ->where('quantidade', '>=', $quantidade)
            ->update([
                'quantidade' => DB::raw('quantidade - ' . (int) $quantidade),
                'atualizado_em' => now(),
            ]);

        return $affected > 0;
    }

    public static function classificar(int $quantidade, int $estoqueMinimo): string
    {
        if ($estoqueMinimo <= 0) {
            return 'sem_minimo';
        }

        $pctAmarelo = (int) ConfigService::get('alerta_amarelo_percentual', '100');
        $pctVermelho = (int) ConfigService::get('alerta_vermelho_percentual', '50');

        $limAmarelo = ($estoqueMinimo * $pctAmarelo) / 100;
        $limVermelho = ($estoqueMinimo * $pctVermelho) / 100;

        if ($quantidade <= $limVermelho) {
            return 'vermelho';
        }
        if ($quantidade <= $limAmarelo) {
            return 'amarelo';
        }

        return 'verde';
    }

    public static function badge(string $classificacao): array
    {
        return match ($classificacao) {
            'verde' => ['success', 'OK'],
            'amarelo' => ['warning', 'ATENÇÃO'],
            'vermelho' => ['danger', 'CRÍTICO'],
            default => ['secondary', '—'],
        };
    }

    public static function getCentralId(): ?int
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $id = DB::table('clinicas')->where('eh_central', 1)->value('id');

        return $cache = $id ? (int) $id : null;
    }

    public static function buscarMedicamentoPorCodigo(string $codigo): ?object
    {
        return MedicamentoBuscaService::buscarPorCodigoOuBarras($codigo);
    }

    public static function zerarTudo(): void
    {
        LoteService::inicializar();
        DB::table('estoque_lotes')->update(['quantidade' => 0, 'atualizado_em' => now()]);
        DB::table('estoque')->update(['quantidade' => 0, 'atualizado_em' => now()]);
    }

    public static function zerarClinica(int $clinicaId): void
    {
        LoteService::inicializar();
        DB::table('estoque_lotes')->where('clinica_id', $clinicaId)->update(['quantidade' => 0, 'atualizado_em' => now()]);
        DB::table('estoque')->where('clinica_id', $clinicaId)->update([
            'quantidade' => 0,
            'atualizado_em' => now(),
        ]);
    }

    public static function zerarMedicamento(int $medicamentoId): void
    {
        LoteService::inicializar();
        DB::table('estoque_lotes')->where('medicamento_id', $medicamentoId)->update(['quantidade' => 0, 'atualizado_em' => now()]);
        DB::table('estoque')->where('medicamento_id', $medicamentoId)->update([
            'quantidade' => 0,
            'atualizado_em' => now(),
        ]);
    }
}

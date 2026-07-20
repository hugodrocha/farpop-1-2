<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class LoteService
{
    public static function inicializar(): void
    {
        LoteSchemaService::garantir();
    }

    /**
     * @return list<array{lote_id: int, quantidade: int, lote: ?string, validade: ?string, fabricante: ?string}>
     */
    public static function baixarPepe(
        int $clinicaId,
        int $medicamentoId,
        int $quantidade,
        string $tipoMov,
        ?string $refTipo = null,
        ?int $refId = null,
        ?int $usuarioId = null,
        ?string $lotePreferido = null,
        ?string $validadePreferida = null
    ): array {
        self::inicializar();

        if ($quantidade <= 0) {
            return [];
        }

        return DB::transaction(function () use (
            $clinicaId, $medicamentoId, $quantidade, $tipoMov, $refTipo, $refId, $usuarioId, $lotePreferido, $validadePreferida
        ) {
            $restante = $quantidade;
            $baixas = [];

            if ($lotePreferido !== null && $lotePreferido !== '') {
                $validadeNorm = $validadePreferida ? ValidadeService::normalizar($validadePreferida) : null;
                $q = DB::table('estoque_lotes')
                    ->where('clinica_id', $clinicaId)
                    ->where('medicamento_id', $medicamentoId)
                    ->where('lote', $lotePreferido)
                    ->when($validadeNorm, fn ($q) => $q->where('validade', $validadeNorm))
                    ->where('quantidade', '>', 0)
                    ->where('bloqueado', 0)
                    ->orderBy('validade')
                    ->lockForUpdate()
                    ->get();

                foreach ($q as $lote) {
                    if ($restante <= 0) {
                        break;
                    }
                    $deduz = min($restante, (int) $lote->quantidade);
                    $baixas[] = self::debitarLote($lote, $deduz, $tipoMov, $refTipo, $refId, $usuarioId);
                    $restante -= $deduz;
                }
            }

            if ($restante > 0) {
                $lotes = DB::table('estoque_lotes')
                    ->where('clinica_id', $clinicaId)
                    ->where('medicamento_id', $medicamentoId)
                    ->where('quantidade', '>', 0)
                    ->where('bloqueado', 0)
                    ->orderByRaw('CASE WHEN validade IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('validade')
                    ->orderBy('criado_em')
                    ->lockForUpdate()
                    ->get();

                foreach ($lotes as $lote) {
                    if ($restante <= 0) {
                        break;
                    }
                    $deduz = min($restante, (int) $lote->quantidade);
                    $baixas[] = self::debitarLote($lote, $deduz, $tipoMov, $refTipo, $refId, $usuarioId);
                    $restante -= $deduz;
                }
            }

            if ($restante > 0) {
                $nome = DB::table('medicamentos')->where('id', $medicamentoId)->value('nome') ?: 'Medicamento';
                throw new RuntimeException("Estoque por lote insuficiente para {$nome}. Faltam {$restante} un.");
            }

            self::sincronizarEstoqueAgregado($clinicaId, $medicamentoId);

            return $baixas;
        });
    }

    public static function adicionarLote(
        int $clinicaId,
        int $medicamentoId,
        int $quantidade,
        string $tipoMov,
        ?string $lote = null,
        ?string $validade = null,
        ?string $fabricante = null,
        ?string $dataFabricacao = null,
        ?int $entradaId = null,
        ?string $refTipo = null,
        ?int $refId = null,
        ?int $usuarioId = null,
        ?string $observacao = null
    ): int {
        self::inicializar();

        if ($quantidade <= 0) {
            throw new RuntimeException('Quantidade deve ser positiva.');
        }

        $validadeNorm = $validade ? ValidadeService::normalizar($validade) : null;
        $lote = $lote !== null && trim($lote) !== '' ? trim($lote) : null;
        $fabricante = $fabricante !== null && trim($fabricante) !== '' ? trim($fabricante) : null;

        return DB::transaction(function () use (
            $clinicaId, $medicamentoId, $quantidade, $tipoMov, $lote, $validadeNorm, $fabricante,
            $dataFabricacao, $entradaId, $refTipo, $refId, $usuarioId, $observacao
        ) {
            $existente = DB::table('estoque_lotes')
                ->where('clinica_id', $clinicaId)
                ->where('medicamento_id', $medicamentoId)
                ->where('lote', $lote)
                ->where(function ($q) use ($validadeNorm) {
                    if ($validadeNorm) {
                        $q->where('validade', $validadeNorm);
                    } else {
                        $q->whereNull('validade');
                    }
                })
                ->lockForUpdate()
                ->first();

            if ($existente) {
                $novaQtd = (int) $existente->quantidade + $quantidade;
                DB::table('estoque_lotes')->where('id', $existente->id)->update([
                    'quantidade' => $novaQtd,
                    'quantidade_inicial' => (int) $existente->quantidade_inicial + $quantidade,
                    'fabricante' => $fabricante ?: $existente->fabricante,
                    'atualizado_em' => now(),
                ]);
                $loteId = (int) $existente->id;
                $saldo = $novaQtd;
            } else {
                $loteId = DB::table('estoque_lotes')->insertGetId([
                    'clinica_id' => $clinicaId,
                    'medicamento_id' => $medicamentoId,
                    'lote' => $lote,
                    'fabricante' => $fabricante,
                    'data_fabricacao' => $dataFabricacao ?: null,
                    'validade' => $validadeNorm,
                    'quantidade' => $quantidade,
                    'quantidade_inicial' => $quantidade,
                    'entrada_id' => $entradaId,
                    'criado_em' => now(),
                    'atualizado_em' => now(),
                ]);
                $saldo = $quantidade;
            }

            self::registrarMovimentacao(
                $tipoMov,
                $clinicaId,
                $medicamentoId,
                $loteId,
                $quantidade,
                $saldo,
                $refTipo,
                $refId,
                $usuarioId,
                $observacao
            );

            self::sincronizarEstoqueAgregado($clinicaId, $medicamentoId);

            return $loteId;
        });
    }

    /**
     * Ajuste tipado: perda, vencimento, ajuste_estoque, devolucao.
     */
    public static function registrarAjuste(
        int $loteId,
        int $quantidade,
        string $tipo,
        ?int $usuarioId = null,
        ?string $observacao = null
    ): void {
        self::inicializar();

        $tiposPermitidos = ['ajuste_estoque', 'perda', 'vencimento', 'devolucao'];
        if (!in_array($tipo, $tiposPermitidos, true)) {
            throw new RuntimeException('Tipo de ajuste inválido.');
        }

        DB::transaction(function () use ($loteId, $quantidade, $tipo, $usuarioId, $observacao) {
            $lote = DB::table('estoque_lotes')->where('id', $loteId)->lockForUpdate()->first();
            if (!$lote) {
                throw new RuntimeException('Lote não encontrado.');
            }

            $delta = $quantidade;
            $novaQtd = (int) $lote->quantidade + $delta;
            if ($novaQtd < 0) {
                throw new RuntimeException('Ajuste resultaria em quantidade negativa no lote.');
            }

            DB::table('estoque_lotes')->where('id', $loteId)->update([
                'quantidade' => $novaQtd,
                'atualizado_em' => now(),
            ]);

            self::registrarMovimentacao(
                $tipo,
                (int) $lote->clinica_id,
                (int) $lote->medicamento_id,
                $loteId,
                $delta,
                $novaQtd,
                'ajuste',
                $loteId,
                $usuarioId,
                $observacao
            );

            self::sincronizarEstoqueAgregado((int) $lote->clinica_id, (int) $lote->medicamento_id);
        });
    }

    /**
     * Inventário: define quantidade física contada para um lote.
     */
    public static function inventariarLote(int $loteId, int $quantidadeContada, ?int $usuarioId = null, ?string $obs = null): void
    {
        self::inicializar();

        DB::transaction(function () use ($loteId, $quantidadeContada, $usuarioId, $obs) {
            $lote = DB::table('estoque_lotes')->where('id', $loteId)->lockForUpdate()->first();
            if (!$lote) {
                throw new RuntimeException('Lote não encontrado.');
            }

            $atual = (int) $lote->quantidade;
            $delta = $quantidadeContada - $atual;

            if ($delta === 0) {
                return;
            }

            DB::table('estoque_lotes')->where('id', $loteId)->update([
                'quantidade' => $quantidadeContada,
                'atualizado_em' => now(),
            ]);

            self::registrarMovimentacao(
                'inventario',
                (int) $lote->clinica_id,
                (int) $lote->medicamento_id,
                $loteId,
                $delta,
                $quantidadeContada,
                'inventario',
                $loteId,
                $usuarioId,
                $obs ?: 'Inventário físico'
            );

            self::sincronizarEstoqueAgregado((int) $lote->clinica_id, (int) $lote->medicamento_id);
        });
    }

    public static function sincronizarEstoqueAgregado(int $clinicaId, int $medicamentoId): void
    {
        $total = (int) DB::table('estoque_lotes')
            ->where('clinica_id', $clinicaId)
            ->where('medicamento_id', $medicamentoId)
            ->sum('quantidade');

        $estoque = DB::table('estoque')
            ->where('clinica_id', $clinicaId)
            ->where('medicamento_id', $medicamentoId)
            ->first();

        if ($estoque) {
            DB::table('estoque')
                ->where('clinica_id', $clinicaId)
                ->where('medicamento_id', $medicamentoId)
                ->update(['quantidade' => $total, 'atualizado_em' => now()]);
        } elseif ($total > 0) {
            DB::table('estoque')->insert([
                'clinica_id' => $clinicaId,
                'medicamento_id' => $medicamentoId,
                'quantidade' => $total,
                'atualizado_em' => now(),
            ]);
        }
    }

    /**
     * @return Collection<int, object>
     */
    public static function listarLotes(?int $clinicaId = null, ?int $medicamentoId = null, bool $apenasComSaldo = true): Collection
    {
        self::inicializar();

        $q = DB::table('estoque_lotes as l')
            ->join('clinicas as c', 'c.id', '=', 'l.clinica_id')
            ->join('medicamentos as m', 'm.id', '=', 'l.medicamento_id')
            ->select('l.*', 'c.nome as clinica', 'm.nome as medicamento', 'm.codigo')
            ->orderBy('c.nome')
            ->orderBy('m.nome')
            ->orderByRaw('CASE WHEN l.validade IS NULL THEN 1 ELSE 0 END')
            ->orderBy('l.validade')
            ->orderBy('l.criado_em');

        if ($clinicaId > 0) {
            $q->where('l.clinica_id', $clinicaId);
        }
        if ($medicamentoId > 0) {
            $q->where('l.medicamento_id', $medicamentoId);
        }
        if ($apenasComSaldo) {
            $q->where('l.quantidade', '>', 0);
        }

        return $q->get()->map(function ($row) {
            $row->validade_fmt = ValidadeService::formatar($row->validade);

            return $row;
        });
    }

    public static function lotesPorMedicamento(int $clinicaId, int $medicamentoId): Collection
    {
        return self::listarLotes($clinicaId, $medicamentoId, true);
    }

    public static function migrarLegadoSeNecessario(): void
    {
        if (!DB::table('estoque_lotes')->exists() && Schema::hasTable('estoque')) {
            $estoques = DB::table('estoque')->where('quantidade', '>', 0)->get();

            foreach ($estoques as $est) {
                $clinicaId = (int) $est->clinica_id;
                $medId = (int) $est->medicamento_id;
                $restante = (int) $est->quantidade;

                if (Schema::hasTable('entradas_medicamentos')) {
                    $entradas = DB::table('entradas_medicamentos')
                        ->where('clinica_id', $clinicaId)
                        ->where('medicamento_id', $medId)
                        ->orderBy('data_entrada')
                        ->get();

                    foreach ($entradas as $ent) {
                        if ($restante <= 0) {
                            break;
                        }
                        $alloc = min($restante, (int) $ent->quantidade);
                        if ($alloc <= 0) {
                            continue;
                        }

                        DB::table('estoque_lotes')->insert([
                            'clinica_id' => $clinicaId,
                            'medicamento_id' => $medId,
                            'lote' => $ent->lote ?: null,
                            'fabricante' => $ent->fabricante ?? null,
                            'validade' => $ent->validade,
                            'quantidade' => $alloc,
                            'quantidade_inicial' => $alloc,
                            'entrada_id' => $ent->id,
                            'criado_em' => $ent->data_entrada ?? now(),
                            'atualizado_em' => now(),
                        ]);
                        $restante -= $alloc;
                    }
                }

                if ($restante > 0) {
                    DB::table('estoque_lotes')->insert([
                        'clinica_id' => $clinicaId,
                        'medicamento_id' => $medId,
                        'lote' => 'LEGADO',
                        'fabricante' => null,
                        'validade' => null,
                        'quantidade' => $restante,
                        'quantidade_inicial' => $restante,
                        'criado_em' => now(),
                        'atualizado_em' => now(),
                    ]);
                }
            }
        }
    }

    public static function registrarMovimentacao(
        string $tipo,
        int $clinicaId,
        int $medicamentoId,
        ?int $loteId,
        int $quantidade,
        ?int $saldoLoteApos,
        ?string $refTipo,
        ?int $refId,
        ?int $usuarioId,
        ?string $observacao
    ): void {
        DB::table('estoque_movimentacoes')->insert([
            'tipo' => $tipo,
            'clinica_id' => $clinicaId,
            'medicamento_id' => $medicamentoId,
            'lote_id' => $loteId,
            'quantidade' => $quantidade,
            'saldo_lote_apos' => $saldoLoteApos,
            'referencia_tipo' => $refTipo,
            'referencia_id' => $refId,
            'observacao' => $observacao,
            'usuario_id' => $usuarioId,
            'criado_em' => now(),
        ]);
    }

    /**
     * @return array{lote_id: int, quantidade: int, lote: ?string, validade: ?string, fabricante: ?string}
     */
    private static function debitarLote(object $lote, int $deduz, string $tipoMov, ?string $refTipo, ?int $refId, ?int $usuarioId): array
    {
        $novaQtd = (int) $lote->quantidade - $deduz;
        DB::table('estoque_lotes')->where('id', $lote->id)->update([
            'quantidade' => $novaQtd,
            'atualizado_em' => now(),
        ]);

        self::registrarMovimentacao(
            $tipoMov,
            (int) $lote->clinica_id,
            (int) $lote->medicamento_id,
            (int) $lote->id,
            -$deduz,
            $novaQtd,
            $refTipo,
            $refId,
            $usuarioId,
            null
        );

        return [
            'lote_id' => (int) $lote->id,
            'quantidade' => $deduz,
            'lote' => $lote->lote,
            'validade' => $lote->validade,
            'fabricante' => $lote->fabricante ?? null,
        ];
    }
}

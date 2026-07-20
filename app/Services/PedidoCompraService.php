<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PedidoCompraService
{
    public const STATUS = [
        'rascunho' => 'Rascunho',
        'enviado' => 'Enviado',
        'atendido' => 'Atendido',
        'cancelado' => 'Cancelado',
    ];

    public static function tabelaDisponivel(): bool
    {
        return Schema::hasTable('pedidos_compra');
    }

    public static function listar(?int $clinicaId = null, int $limite = 100): Collection
    {
        if (!self::tabelaDisponivel()) {
            return collect();
        }

        $q = DB::table('pedidos_compra as p')
            ->leftJoin('clinicas as c', 'c.id', '=', 'p.clinica_id')
            ->select(
                'p.*',
                'c.nome as clinica_nome',
                DB::raw('(SELECT COUNT(*) FROM pedidos_compra_itens i WHERE i.pedido_id = p.id) as total_itens'),
                DB::raw('(SELECT COALESCE(SUM(i.qtd_pedido), 0) FROM pedidos_compra_itens i WHERE i.pedido_id = p.id) as total_unidades')
            )
            ->orderByDesc('p.id')
            ->limit($limite);

        if ($clinicaId > 0) {
            $q->where('p.clinica_id', $clinicaId);
        }

        return $q->get()->map(fn ($row) => self::enriquecer($row));
    }

    public static function buscar(int $id): ?object
    {
        if (!self::tabelaDisponivel()) {
            return null;
        }

        $pedido = DB::table('pedidos_compra as p')
            ->leftJoin('clinicas as c', 'c.id', '=', 'p.clinica_id')
            ->where('p.id', $id)
            ->select('p.*', 'c.nome as clinica_nome')
            ->first();

        if (!$pedido) {
            return null;
        }

        $pedido->itens = self::itens($id);
        $pedido->total_itens = $pedido->itens->count();
        $pedido->total_unidades = (int) $pedido->itens->sum('qtd_pedido');

        return self::enriquecer($pedido);
    }

    public static function itens(int $pedidoId): Collection
    {
        return DB::table('pedidos_compra_itens')
            ->where('pedido_id', $pedidoId)
            ->orderBy('medicamento')
            ->get();
    }

    /**
     * Gera pedido registrado a partir da lista de reposição atual.
     */
    public static function criarFromReposicao(
        ?int $clinicaId,
        bool $agrupar,
        ?string $observacao = null
    ): int {
        if (!self::tabelaDisponivel()) {
            throw new \RuntimeException('Tabela pedidos_compra não disponível. Execute a migration.');
        }

        $itens = ReposicaoService::pedidoReposicao($clinicaId ?: null, $agrupar);
        if ($itens->isEmpty()) {
            throw new \InvalidArgumentException('Não há itens para registrar no pedido.');
        }

        return DB::transaction(function () use ($clinicaId, $agrupar, $observacao, $itens) {
            $pedidoId = DB::table('pedidos_compra')->insertGetId([
                'numero' => self::gerarNumero(),
                'clinica_id' => $clinicaId > 0 ? $clinicaId : null,
                'status' => 'rascunho',
                'agrupado' => $agrupar ? 1 : 0,
                'observacao' => $observacao ?: null,
                'usuario_id' => session('usuario_id'),
                'usuario_nome' => session('nome_completo') ?: session('usuario'),
                'criado_em' => now(),
            ]);

            foreach ($itens as $it) {
                DB::table('pedidos_compra_itens')->insert([
                    'pedido_id' => $pedidoId,
                    'medicamento_id' => $it->medicamento_id ?? null,
                    'clinica_id' => $it->clinica_id ?? null,
                    'unidade_nome' => $it->unidade ?? null,
                    'codigo' => $it->codigo ?? null,
                    'medicamento' => $it->medicamento,
                    'dosagem' => $it->dosagem ?? null,
                    'estoque_atual' => (int) ($it->quantidade ?? 0),
                    'qtd_pedido' => (int) ($it->qtd_sugerida ?? 0),
                    'prioridade' => $it->situacao_label ?? null,
                ]);
            }

            AuditoriaService::registrar(
                'Pedido de compra registrado',
                'pedidos_compra',
                sprintf('Pedido %s com %d item(ns)', self::buscar($pedidoId)?->numero ?? $pedidoId, $itens->count())
            );

            return $pedidoId;
        });
    }

    public static function atualizarStatus(int $id, string $status): bool
    {
        if (!isset(self::STATUS[$status])) {
            throw new \InvalidArgumentException('Status inválido.');
        }

        $pedido = DB::table('pedidos_compra')->where('id', $id)->first();
        if (!$pedido) {
            return false;
        }

        $transicoes = [
            'rascunho' => ['enviado', 'cancelado'],
            'enviado' => ['atendido', 'cancelado'],
        ];

        $permitidos = $transicoes[$pedido->status] ?? [];
        if (!in_array($status, $permitidos, true)) {
            throw new \InvalidArgumentException('Transição de status não permitida.');
        }

        $update = [
            'status' => $status,
            'atualizado_em' => now(),
        ];

        if ($status === 'enviado') {
            $update['enviado_em'] = now();
        }
        if ($status === 'atendido') {
            $update['atendido_em'] = now();
        }

        DB::table('pedidos_compra')->where('id', $id)->update($update);

        AuditoriaService::registrar(
            'Pedido de compra — ' . self::STATUS[$status],
            'pedidos_compra',
            ($pedido->numero ?? (string) $id)
        );

        return true;
    }

    private static function gerarNumero(): string
    {
        $ano = date('Y');
        $prefixo = 'PC-' . $ano . '-';
        $ultimo = DB::table('pedidos_compra')
            ->where('numero', 'like', $prefixo . '%')
            ->orderByDesc('id')
            ->value('numero');

        $seq = 1;
        if ($ultimo && preg_match('/-(\d+)$/', $ultimo, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefixo . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private static function enriquecer(object $row): object
    {
        $row->status_label = self::STATUS[$row->status] ?? $row->status;
        $row->status_badge = match ($row->status) {
            'rascunho' => 'secondary',
            'enviado' => 'primary',
            'atendido' => 'success',
            'cancelado' => 'dark',
            default => 'light',
        };
        $row->unidade_label = $row->clinica_nome ?? 'Central (todas as unidades)';

        return $row;
    }
}

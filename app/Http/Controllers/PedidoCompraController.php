<?php

namespace App\Http\Controllers;

use App\Services\ModuloService;
use App\Services\PedidoCompraService;
use App\Services\PerfilService;
use App\Services\RelatorioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoCompraController extends Controller
{
    public function index(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'alertas', 'pedidos_compra')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $ehAdmin = PerfilService::ehAdmin($perfil);
        $clinicaId = $ehAdmin
            ? (int) $request->query('clinica_id', 0)
            : (int) $request->session()->get('clinica_id', 0);

        $pedidos = PedidoCompraService::listar($clinicaId ?: null);
        $clinicas = $ehAdmin
            ? DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get()
            : collect();

        return view('pedidos-compra.index', compact('pedidos', 'clinicaId', 'clinicas', 'ehAdmin'));
    }

    public function store(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'alertas', 'pedidos_compra')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $ehAdmin = PerfilService::ehAdmin($perfil);
        $clinicaId = $ehAdmin
            ? (int) $request->input('clinica_id', 0)
            : (int) $request->session()->get('clinica_id', 0);
        $agrupar = $ehAdmin && !$clinicaId && $request->boolean('agrupar');

        try {
            $pedidoId = PedidoCompraService::criarFromReposicao(
                $clinicaId ?: null,
                $agrupar,
                trim((string) $request->input('observacao', '')) ?: null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('pedidos-compra.show', $pedidoId)
            ->with('success', 'Pedido de compra registrado com sucesso.');
    }

    public function show(Request $request, int $id)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'alertas', 'pedidos_compra')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $pedido = PedidoCompraService::buscar($id);
        if (!$pedido) {
            abort(404);
        }

        if (!PerfilService::ehAdmin($perfil)) {
            $clinicaSessao = (int) $request->session()->get('clinica_id', 0);
            if ($pedido->clinica_id && (int) $pedido->clinica_id !== $clinicaSessao) {
                abort(403);
            }
        }

        return view('pedidos-compra.show', compact('pedido'));
    }

    public function enviar(Request $request, int $id)
    {
        return $this->transicao($request, $id, 'enviado', 'Pedido marcado como enviado.');
    }

    public function atender(Request $request, int $id)
    {
        return $this->transicao($request, $id, 'atendido', 'Pedido marcado como atendido.');
    }

    public function cancelar(Request $request, int $id)
    {
        return $this->transicao($request, $id, 'cancelado', 'Pedido cancelado.');
    }

    public function exportar(Request $request, int $id)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'alertas', 'pedidos_compra')) {
            abort(403);
        }

        $pedido = PedidoCompraService::buscar($id);
        if (!$pedido) {
            abort(404);
        }

        $rows = $pedido->itens->map(fn ($it) => [
            $it->unidade_nome ?? '',
            $it->codigo ?? '',
            $it->medicamento,
            $it->dosagem ?? '',
            $it->estoque_atual,
            $it->qtd_pedido,
            $it->prioridade ?? '',
        ])->all();

        return RelatorioService::exportarCsv(
            'pedido_compra_' . ($pedido->numero ?? $id) . '.csv',
            ['Unidade', 'Código', 'Medicamento', 'Dosagem', 'Estoque', 'Qtd pedido', 'Prioridade'],
            $rows
        );
    }

    public function imprimir(Request $request, int $id)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'alertas', 'pedidos_compra')) {
            abort(403);
        }

        $pedido = PedidoCompraService::buscar($id);
        if (!$pedido) {
            abort(404);
        }

        return view('pedidos-compra.print', compact('pedido'));
    }

    private function transicao(Request $request, int $id, string $status, string $msg)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'alertas', 'pedidos_compra')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        try {
            PedidoCompraService::atualizarStatus($id, $status);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', $msg);
    }
}

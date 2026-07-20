<?php

namespace App\Http\Controllers;

use App\Services\ModuloService;
use App\Services\PerfilService;
use App\Services\RelatorioService;
use App\Services\ReposicaoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoReposicaoController extends Controller
{
    public function index(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'alertas', 'pedido_reposicao')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $ehAdmin = PerfilService::ehAdmin($perfil);
        $clinicaId = $ehAdmin
            ? (int) $request->query('clinica_id', 0)
            : (int) $request->session()->get('clinica_id', 0);

        $agrupar = $ehAdmin && !$clinicaId && $request->boolean('agrupar');
        $itens = ReposicaoService::pedidoReposicao($clinicaId ?: null, $agrupar);
        $clinicas = $ehAdmin
            ? DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get()
            : collect();

        return view('pedidos-reposicao.index', compact(
            'itens',
            'clinicaId',
            'clinicas',
            'ehAdmin',
            'agrupar'
        ));
    }

    public function exportar(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'alertas', 'pedido_reposicao')) {
            abort(403);
        }

        $ehAdmin = PerfilService::ehAdmin($perfil);
        $clinicaId = $ehAdmin
            ? (int) $request->query('clinica_id', 0)
            : (int) $request->session()->get('clinica_id', 0);
        $agrupar = $ehAdmin && !$clinicaId && $request->boolean('agrupar');

        $rows = ReposicaoService::pedidoReposicao($clinicaId ?: null, $agrupar)->map(fn ($r) => [
            $r->unidade,
            $r->codigo,
            $r->medicamento,
            $r->dosagem ?: '',
            $r->quantidade,
            $r->qtd_sugerida,
            $r->situacao_label,
        ])->all();

        return RelatorioService::exportarCsv(
            'pedido_reposicao_' . date('Y-m-d') . '.csv',
            ['Unidade', 'Código', 'Medicamento', 'Dosagem', 'Estoque atual', 'Qtd pedido', 'Prioridade'],
            $rows
        );
    }

    public function imprimir(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'alertas', 'pedido_reposicao')) {
            abort(403);
        }

        $ehAdmin = PerfilService::ehAdmin($perfil);
        $clinicaId = $ehAdmin
            ? (int) $request->query('clinica_id', 0)
            : (int) $request->session()->get('clinica_id', 0);
        $agrupar = $ehAdmin && !$clinicaId && $request->boolean('agrupar');

        $itens = ReposicaoService::pedidoReposicao($clinicaId ?: null, $agrupar);
        $titulo = 'Pedido de reposição';
        $totalItens = $itens->count();
        $totalUnidades = (int) $itens->sum('qtd_sugerida');

        return view('pedidos-reposicao.print', compact('itens', 'titulo', 'totalItens', 'totalUnidades', 'agrupar'));
    }
}

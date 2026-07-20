<?php

namespace App\Http\Controllers;

use App\Services\LoteService;
use App\Services\ModuloService;
use App\Services\PerfilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AjusteController extends Controller
{
    public function create(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'estoque', 'ajustes')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $clinicaId = PerfilService::ehAdmin($perfil)
            ? (int) $request->query('clinica_id', 0)
            : (int) $request->session()->get('clinica_id', 0);

        $lotes = LoteService::listarLotes($clinicaId ?: null, null, true);
        $tipos = config('farpop.movimentacoes');

        return view('ajustes.create', compact('lotes', 'tipos', 'clinicaId'));
    }

    public function store(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'estoque', 'ajustes')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $data = $request->validate([
            'lote_id' => 'required|integer',
            'tipo' => 'required|in:ajuste_estoque,perda,vencimento,devolucao',
            'quantidade' => 'required|integer',
            'observacao' => 'nullable|max:255',
        ]);

        $lote = DB::table('estoque_lotes')->where('id', $data['lote_id'])->first();
        if (!$lote) {
            return back()->withErrors(['error' => 'Lote não encontrado.']);
        }

        if (!PerfilService::ehAdmin($perfil) && (int) $lote->clinica_id !== (int) $request->session()->get('clinica_id')) {
            return back()->withErrors(['error' => 'Sem permissão para ajustar este lote.']);
        }

        $qtd = (int) $data['quantidade'];
        if (in_array($data['tipo'], ['perda', 'vencimento'], true) && $qtd > 0) {
            $qtd = -$qtd;
        }

        LoteService::registrarAjuste(
            (int) $data['lote_id'],
            $qtd,
            $data['tipo'],
            (int) $request->session()->get('usuario_id'),
            $data['observacao'] ?? null
        );

        return redirect()->route('lotes.index')->with('success', 'Ajuste registrado com sucesso.');
    }
}

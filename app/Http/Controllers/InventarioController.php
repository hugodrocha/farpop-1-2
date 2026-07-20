<?php

namespace App\Http\Controllers;

use App\Services\AuditoriaService;
use App\Services\LoteService;
use App\Services\ModuloService;
use App\Services\PerfilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'estoque', 'inventario')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $clinicaId = PerfilService::ehAdmin($perfil)
            ? (int) $request->query('clinica_id', 0)
            : (int) $request->session()->get('clinica_id', 0);

        $lotes = LoteService::listarLotes($clinicaId ?: null, null, false);
        $clinicas = PerfilService::ehAdmin($perfil)
            ? DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get()
            : collect();

        return view('inventario.index', compact('lotes', 'clinicaId', 'perfil', 'clinicas'));
    }

    public function store(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'estoque', 'inventario')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $data = $request->validate([
            'contagem' => 'required|array',
            'contagem.*' => 'nullable|integer|min:0',
        ]);

        $usuarioId = (int) $request->session()->get('usuario_id');
        $ajustados = 0;

        foreach ($data['contagem'] as $loteId => $qtd) {
            if ($qtd === null || $qtd === '') {
                continue;
            }
            LoteService::inventariarLote((int) $loteId, (int) $qtd, $usuarioId);
            $ajustados++;
        }

        AuditoriaService::registrar('INVENTARIO', 'clinica', "{$ajustados} lote(s) inventariado(s)");

        return redirect()->route('inventario.index')->with('success', "Inventário salvo ({$ajustados} lote(s) atualizados).");
    }
}

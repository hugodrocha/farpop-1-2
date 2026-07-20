<?php

namespace App\Http\Controllers;

use App\Services\AuditoriaService;
use App\Services\InventarioImportService;
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

    public function importar(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'estoque', 'inventario')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $request->validate([
            'arquivo' => 'required|file|max:10240',
        ]);

        $clinicaEscopo = PerfilService::ehAdmin($perfil)
            ? (int) $request->input('clinica_id', 0)
            : (int) $request->session()->get('clinica_id', 0);

        $usuarioId = (int) $request->session()->get('usuario_id');
        $resultado = InventarioImportService::importarDeArquivo(
            $request->file('arquivo')->getRealPath(),
            $clinicaEscopo ?: null,
            $usuarioId
        );

        AuditoriaService::registrar(
            'INVENTARIO_IMPORT',
            'clinica',
            "CSV: {$resultado['ajustados']} ajuste(s), {$resultado['processados']} linha(s)"
        );

        $msg = "Importação concluída: {$resultado['ajustados']} lote(s) ajustado(s), {$resultado['ignorados']} linha(s) sem contagem.";
        if ($resultado['erros'] !== []) {
            $preview = implode(' ', array_slice($resultado['erros'], 0, 3));
            $extra = count($resultado['erros']) > 3 ? ' (+' . (count($resultado['erros']) - 3) . ' avisos)' : '';

            return redirect()->route('inventario.index')
                ->with('success', $msg)
                ->withErrors(['error' => $preview . $extra]);
        }

        return redirect()->route('inventario.index')->with('success', $msg);
    }
}

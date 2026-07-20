<?php

namespace App\Http\Controllers;

use App\Services\HorusExportService;
use App\Services\ModuloService;
use App\Services\PerfilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HorusController extends Controller
{
    public function index(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'integracoes', 'horus')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        $de = $request->query('de', now()->subDays(30)->format('Y-m-d'));
        $ate = $request->query('ate', now()->format('Y-m-d'));

        return view('integracoes.horus', compact('clinicas', 'de', 'ate'));
    }

    public function exportar(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'integracoes', 'horus')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $data = $request->validate([
            'de' => 'required|date',
            'ate' => 'required|date|after_or_equal:de',
            'clinica_id' => 'nullable|integer',
        ]);

        $clinicaId = (int) ($data['clinica_id'] ?? 0);
        if (!PerfilService::ehAdmin($perfil) && $request->session()->get('clinica_id')) {
            $clinicaId = (int) $request->session()->get('clinica_id');
        }

        return HorusExportService::exportarMovimentos(
            $clinicaId > 0 ? $clinicaId : null,
            $data['de'],
            $data['ate']
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\IntegracaoService;
use App\Services\ModuloService;
use Illuminate\Http\Request;

class IntegracaoController extends Controller
{
    public function index(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessar($perfil, 'integracoes')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $integracoes = IntegracaoService::catalogo();

        return view('integracoes.index', compact('integracoes'));
    }
}

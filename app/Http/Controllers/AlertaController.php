<?php

namespace App\Http\Controllers;

use App\Services\AlertaVencimentoService;
use App\Services\ModuloService;
use Illuminate\Http\Request;

class AlertaController extends Controller
{
    public function vencimento(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessar($perfil, 'estoque')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $clinicaId = $this->clinicaEscopo($request);
        $tipo = $request->query('tipo', 'todos');
        if (!in_array($tipo, ['todos', 'vencido', 'vencendo'], true)) {
            $tipo = 'todos';
        }

        $itens = AlertaVencimentoService::listar($clinicaId ?: null, $tipo === 'todos' ? null : $tipo);
        $contagens = AlertaVencimentoService::contar($clinicaId ?: null);

        return view('alertas.vencimento', compact('itens', 'contagens', 'tipo', 'clinicaId'));
    }

    private function clinicaEscopo(Request $request): int
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (in_array($perfil, ['superuser', 'admin', 'gestor'], true)) {
            return (int) $request->query('clinica_id', 0);
        }

        return (int) $request->session()->get('clinica_id', 0);
    }
}

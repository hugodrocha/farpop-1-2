<?php

namespace App\Http\Controllers;

use App\Services\AlertaOperacionalService;
use App\Services\AlertaVencimentoService;
use App\Services\ModuloService;
use Illuminate\Http\Request;

class AlertaController extends Controller
{
    public function index(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessar($perfil, 'alertas')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $clinicaId = $this->clinicaEscopo($request);
        $resumo = AlertaOperacionalService::resumo($clinicaId ?: null);
        $pendencias = AlertaOperacionalService::listarPendencias($clinicaId ?: null)->take(15);

        return view('alertas.index', compact('resumo', 'pendencias', 'clinicaId'));
    }

    public function estoque(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'alertas', 'estoque_baixo')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $clinicaId = $this->clinicaEscopo($request);
        $nivel = $request->query('nivel', 'todos');
        if (!in_array($nivel, ['todos', 'critico', 'atencao'], true)) {
            $nivel = 'todos';
        }

        $itens = AlertaOperacionalService::listarEstoque($clinicaId ?: null, $nivel);
        $contagens = AlertaOperacionalService::contarEstoque($clinicaId ?: null);

        return view('alertas.estoque', compact('itens', 'contagens', 'nivel', 'clinicaId'));
    }

    public function pendencias(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'alertas', 'pendencias')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $clinicaId = $this->clinicaEscopo($request);
        $pendencias = AlertaOperacionalService::listarPendencias($clinicaId ?: null);
        $resumo = AlertaOperacionalService::resumo($clinicaId ?: null);

        return view('alertas.pendencias', compact('pendencias', 'resumo', 'clinicaId'));
    }

    public function vencimento(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'alertas', 'vencimento')) {
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

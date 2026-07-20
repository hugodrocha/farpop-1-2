<?php

namespace App\Http\Controllers;

use App\Services\Fase4SchemaService;
use App\Services\ModuloService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FabricanteController extends Controller
{
    public function index(Request $request)
    {
        if (!$this->autorizado($request)) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        Fase4SchemaService::garantir();
        $fabricantes = DB::table('fabricantes')->orderBy('nome')->get();

        return view('fabricantes.index', compact('fabricantes'));
    }

    public function create(Request $request)
    {
        if (!$this->autorizado($request)) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        return view('fabricantes.create');
    }

    public function store(Request $request)
    {
        if (!$this->autorizado($request)) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $data = $request->validate([
            'nome' => 'required|max:180',
            'cnpj' => 'nullable|max:20',
            'ativo' => 'boolean',
        ]);

        Fase4SchemaService::garantir();

        DB::table('fabricantes')->insert([
            ...$data,
            'ativo' => $request->boolean('ativo', true),
            'criado_em' => now(),
            'atualizado_em' => now(),
        ]);

        return redirect()->route('fabricantes.index')->with('success', 'Fabricante cadastrado.');
    }

    public function edit(Request $request, int $id)
    {
        if (!$this->autorizado($request)) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        Fase4SchemaService::garantir();
        $fabricante = DB::table('fabricantes')->where('id', $id)->firstOrFail();

        return view('fabricantes.edit', compact('fabricante'));
    }

    public function update(Request $request, int $id)
    {
        if (!$this->autorizado($request)) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $data = $request->validate([
            'nome' => 'required|max:180',
            'cnpj' => 'nullable|max:20',
            'ativo' => 'boolean',
        ]);

        DB::table('fabricantes')->where('id', $id)->update([
            ...$data,
            'ativo' => $request->boolean('ativo'),
            'atualizado_em' => now(),
        ]);

        return redirect()->route('fabricantes.index')->with('success', 'Fabricante atualizado.');
    }

    public function destroy(Request $request, int $id)
    {
        if (!$this->autorizado($request)) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        DB::table('fabricantes')->where('id', $id)->delete();

        return redirect()->route('fabricantes.index')->with('success', 'Fabricante removido.');
    }

    private function autorizado(Request $request): bool
    {
        return ModuloService::podeAcessarSubmodulo((string) $request->session()->get('perfil', ''), 'cadastros', 'fabricantes');
    }
}

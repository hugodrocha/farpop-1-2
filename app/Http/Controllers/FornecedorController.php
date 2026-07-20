<?php

namespace App\Http\Controllers;

use App\Services\Fase4SchemaService;
use App\Services\ModuloService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FornecedorController extends Controller
{
    public function index(Request $request)
    {
        if (!$this->autorizado($request)) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        Fase4SchemaService::garantir();
        $fornecedores = DB::table('fornecedores')->orderBy('nome')->get();

        return view('fornecedores.index', compact('fornecedores'));
    }

    public function create(Request $request)
    {
        if (!$this->autorizado($request)) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        return view('fornecedores.create');
    }

    public function store(Request $request)
    {
        if (!$this->autorizado($request)) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $data = $request->validate([
            'nome' => 'required|max:180',
            'cnpj' => 'nullable|max:20',
            'telefone' => 'nullable|max:40',
            'email' => 'nullable|email|max:180',
            'endereco' => 'nullable|max:255',
            'ativo' => 'boolean',
        ]);

        Fase4SchemaService::garantir();

        DB::table('fornecedores')->insert([
            ...$data,
            'ativo' => $request->boolean('ativo', true),
            'criado_em' => now(),
            'atualizado_em' => now(),
        ]);

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor cadastrado.');
    }

    public function edit(Request $request, int $id)
    {
        if (!$this->autorizado($request)) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        Fase4SchemaService::garantir();
        $fornecedor = DB::table('fornecedores')->where('id', $id)->firstOrFail();

        return view('fornecedores.edit', compact('fornecedor'));
    }

    public function update(Request $request, int $id)
    {
        if (!$this->autorizado($request)) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $data = $request->validate([
            'nome' => 'required|max:180',
            'cnpj' => 'nullable|max:20',
            'telefone' => 'nullable|max:40',
            'email' => 'nullable|email|max:180',
            'endereco' => 'nullable|max:255',
            'ativo' => 'boolean',
        ]);

        DB::table('fornecedores')->where('id', $id)->update([
            ...$data,
            'ativo' => $request->boolean('ativo'),
            'atualizado_em' => now(),
        ]);

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor atualizado.');
    }

    public function destroy(Request $request, int $id)
    {
        if (!$this->autorizado($request)) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        DB::table('fornecedores')->where('id', $id)->delete();

        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor removido.');
    }

    private function autorizado(Request $request): bool
    {
        return ModuloService::podeAcessarSubmodulo((string) $request->session()->get('perfil', ''), 'cadastros', 'fornecedores');
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\ClinicaSchemaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClinicaController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        ClinicaSchemaService::garantir();

        $clinicas = DB::table('clinicas')->orderBy('nome')->get();

        return view('clinicas.index', compact('clinicas'));
    }

    public function create(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        return view('clinicas.create');
    }

    public function store(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'nome' => 'required|max:180',
            'cnes' => 'nullable|max:20',
            'cnpj' => 'nullable|max:20',
            'endereco' => 'nullable|max:255',
            'bairro' => 'nullable|max:120',
            'telefone' => 'nullable|max:40',
            'responsavel' => 'nullable|max:150',
            'eh_central' => 'boolean',
            'ativo' => 'boolean',
        ]);

        ClinicaSchemaService::garantir();

        DB::table('clinicas')->insert([
            ...$data,
            'criado_em' => now(),
            'atualizado_em' => now(),
        ]);

        return redirect()->route('clinicas.index')->with('success', 'Clínica cadastrada com sucesso.');
    }

    public function edit(Request $request, $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $clinica = DB::table('clinicas')->where('id', $id)->firstOrFail();

        return view('clinicas.edit', compact('clinica'));
    }

    public function update(Request $request, $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'nome' => 'required|max:180',
            'cnes' => 'nullable|max:20',
            'cnpj' => 'nullable|max:20',
            'endereco' => 'nullable|max:255',
            'bairro' => 'nullable|max:120',
            'telefone' => 'nullable|max:40',
            'responsavel' => 'nullable|max:150',
            'eh_central' => 'boolean',
            'ativo' => 'boolean',
        ]);

        ClinicaSchemaService::garantir();

        DB::table('clinicas')->where('id', $id)->update([
            ...$data,
            'atualizado_em' => now(),
        ]);

        return redirect()->route('clinicas.index')->with('success', 'Clínica atualizada com sucesso.');
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        DB::table('clinicas')->where('id', $id)->delete();

        return redirect()->route('clinicas.index')->with('success', 'Clínica removida com sucesso.');
    }
}

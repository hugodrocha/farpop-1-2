<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicamentoController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $busca = trim($request->query('q', ''));

        $query = DB::table('medicamentos');

        if ($busca !== '') {
            $digitos = preg_replace('/\D/', '', $busca);
            $query->where(function ($q) use ($busca, $digitos) {
                $q->where('nome', 'like', '%' . $busca . '%')
                    ->orWhere('principio_ativo', 'like', '%' . $busca . '%')
                    ->orWhere('codigo', 'like', '%' . $busca . '%')
                    ->orWhere('codigo_barras', 'like', '%' . $busca . '%');
                if ($digitos !== '') {
                    $q->orWhereRaw("REPLACE(REPLACE(REPLACE(codigo, '.', ''), '-', ''), ' ', '') LIKE ?", ['%' . $digitos . '%'])
                        ->orWhereRaw("REPLACE(codigo_barras, ' ', '') LIKE ?", ['%' . $digitos . '%']);
                }
            });
        }

        $medicamentos = $query->orderBy('nome')->get();

        return view('medicamentos.index', compact('medicamentos', 'busca'));
    }

    public function create(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        return view('medicamentos.create');
    }

    public function store(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'nome' => 'required|max:180',
            'principio_ativo' => 'nullable|max:180',
            'forma_farmaceutica' => 'nullable|max:80',
            'dosagem' => 'nullable|max:80',
            'unidade' => 'required|max:40',
            'codigo' => 'nullable|max:80',
            'codigo_barras' => 'nullable|max:60',
            'estoque_minimo' => 'nullable|integer',
            'controlado' => 'boolean',
            'ativo' => 'boolean',
        ]);

        DB::table('medicamentos')->insert([
            ...$data,
            'criado_em' => now(),
            'atualizado_em' => now(),
        ]);

        return redirect()->route('medicamentos.index')->with('success', 'Medicamento cadastrado com sucesso.');
    }

    public function edit(Request $request, $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $medicamento = DB::table('medicamentos')->where('id', $id)->firstOrFail();

        return view('medicamentos.edit', compact('medicamento'));
    }

    public function update(Request $request, $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'nome' => 'required|max:180',
            'principio_ativo' => 'nullable|max:180',
            'forma_farmaceutica' => 'nullable|max:80',
            'dosagem' => 'nullable|max:80',
            'unidade' => 'required|max:40',
            'codigo' => 'nullable|max:80',
            'codigo_barras' => 'nullable|max:60',
            'estoque_minimo' => 'nullable|integer',
            'controlado' => 'boolean',
            'ativo' => 'boolean',
        ]);

        DB::table('medicamentos')->where('id', $id)->update([
            ...$data,
            'atualizado_em' => now(),
        ]);

        return redirect()->route('medicamentos.index')->with('success', 'Medicamento atualizado com sucesso.');
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        DB::table('medicamentos')->where('id', $id)->delete();

        return redirect()->route('medicamentos.index')->with('success', 'Medicamento removido com sucesso.');
    }
}

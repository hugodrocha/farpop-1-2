<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntradaController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $entradas = DB::table('entradas_medicamentos as en')
            ->join('clinicas as c', 'c.id', '=', 'en.clinica_id')
            ->join('medicamentos as m', 'm.id', '=', 'en.medicamento_id')
            ->leftJoin('usuarios as u', 'u.id', '=', 'en.usuario_id')
            ->select('en.*', 'c.nome as clinica_nome', 'm.nome as medicamento_nome', 'm.dosagem', 'u.nome_completo as usuario_nome')
            ->orderByDesc('en.data_entrada')
            ->limit(100)
            ->get();

        return view('entradas.index', compact('entradas'));
    }

    public function create(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        $medicamentos = DB::table('medicamentos')->where('ativo', 1)->orderBy('nome')->get();

        return view('entradas.create', compact('clinicas', 'medicamentos'));
    }

    public function store(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'clinica_id' => 'required|integer',
            'medicamento_id' => 'required|integer',
            'quantidade' => 'required|integer|min:1',
            'lote' => 'nullable|max:80',
            'validade' => 'nullable|max:20',
            'nota_fiscal' => 'nullable|max:80',
            'origem' => 'nullable|max:160',
            'observacao' => 'nullable|max:255',
        ]);

        DB::beginTransaction();
        try {
            $uid = $request->session()->get('usuario_id');
            DB::table('entradas_medicamentos')->insert([
                'clinica_id' => $data['clinica_id'],
                'medicamento_id' => $data['medicamento_id'],
                'quantidade' => $data['quantidade'],
                'lote' => $data['lote'] ?? null,
                'validade' => $data['validade'] ?? null,
                'nota_fiscal' => $data['nota_fiscal'] ?? null,
                'origem' => $data['origem'] ?? null,
                'observacao' => $data['observacao'] ?? null,
                'usuario_id' => $uid,
                'data_entrada' => now(),
            ]);

            $this->somarOuCriarEstoque($data['clinica_id'], $data['medicamento_id'], $data['quantidade']);
            DB::commit();

            return redirect()->route('entradas.index')->with('success', 'Entrada registrada com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit(Request $request, string $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $entrada = DB::table('entradas_medicamentos')->where('id', $id)->firstOrFail();
        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        $medicamentos = DB::table('medicamentos')->where('ativo', 1)->orderBy('nome')->get();

        return view('entradas.edit', compact('entrada', 'clinicas', 'medicamentos'));
    }

    public function update(Request $request, string $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'clinica_id' => 'required|integer',
            'medicamento_id' => 'required|integer',
            'quantidade' => 'required|integer|min:1',
            'lote' => 'nullable|max:80',
            'validade' => 'nullable|max:20',
            'nota_fiscal' => 'nullable|max:80',
            'origem' => 'nullable|max:160',
            'observacao' => 'nullable|max:255',
        ]);

        DB::table('entradas_medicamentos')->where('id', $id)->update([
            'clinica_id' => $data['clinica_id'],
            'medicamento_id' => $data['medicamento_id'],
            'quantidade' => $data['quantidade'],
            'lote' => $data['lote'] ?? null,
            'validade' => $data['validade'] ?? null,
            'nota_fiscal' => $data['nota_fiscal'] ?? null,
            'origem' => $data['origem'] ?? null,
            'observacao' => $data['observacao'] ?? null,
        ]);

        return redirect()->route('entradas.index')->with('success', 'Entrada atualizada com sucesso.');
    }

    public function destroy(Request $request, string $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        DB::table('entradas_medicamentos')->where('id', $id)->delete();

        return redirect()->route('entradas.index')->with('success', 'Entrada removida com sucesso.');
    }

    private function somarOuCriarEstoque(int $clinicaId, int $medicamentoId, int $quantidade): void
    {
        $estoque = DB::table('estoque')->where('clinica_id', $clinicaId)->where('medicamento_id', $medicamentoId)->first();

        if ($estoque) {
            DB::table('estoque')->where('clinica_id', $clinicaId)->where('medicamento_id', $medicamentoId)->update([
                'quantidade' => (int) $estoque->quantidade + $quantidade,
                'atualizado_em' => now(),
            ]);
        } else {
            DB::table('estoque')->insert([
                'clinica_id' => $clinicaId,
                'medicamento_id' => $medicamentoId,
                'quantidade' => $quantidade,
                'atualizado_em' => now(),
            ]);
        }
    }
}

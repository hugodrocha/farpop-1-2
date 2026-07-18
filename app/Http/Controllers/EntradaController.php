<?php

namespace App\Http\Controllers;

use App\Services\AuditoriaService;
use App\Services\EstoqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntradaController extends Controller
{
    public function index()
    {
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

    public function create()
    {
        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        $medicamentos = DB::table('medicamentos')->where('ativo', 1)->orderBy('nome')->get();

        return view('entradas.create', compact('clinicas', 'medicamentos'));
    }

    public function store(Request $request)
    {
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

        DB::transaction(function () use ($data) {
            $uid = session('usuario_id');
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

            EstoqueService::somarOuCriar((int) $data['clinica_id'], (int) $data['medicamento_id'], (int) $data['quantidade']);
            AuditoriaService::registrar('ENTRADA_MEDICAMENTO', 'clinica:' . $data['clinica_id'], 'Qtd ' . $data['quantidade']);
        });

        return redirect()->route('entradas.index')->with('success', 'Entrada registrada com sucesso.');
    }

    public function edit(string $id)
    {
        $entrada = DB::table('entradas_medicamentos')->where('id', $id)->firstOrFail();
        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        $medicamentos = DB::table('medicamentos')->where('ativo', 1)->orderBy('nome')->get();

        return view('entradas.edit', compact('entrada', 'clinicas', 'medicamentos'));
    }

    public function update(Request $request, string $id)
    {
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

        return redirect()->route('entradas.index')->with('success', 'Entrada atualizada.');
    }

    public function destroy(string $id)
    {
        DB::table('entradas_medicamentos')->where('id', $id)->delete();

        return redirect()->route('entradas.index')->with('success', 'Entrada removida.');
    }
}

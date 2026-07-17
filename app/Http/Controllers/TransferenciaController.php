<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferenciaController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $transferencias = DB::table('transferencias as t')
            ->join('clinicas as co', 'co.id', '=', 't.clinica_origem_id')
            ->join('clinicas as cd', 'cd.id', '=', 't.clinica_destino_id')
            ->leftJoin('usuarios as u', 'u.id', '=', 't.usuario_envio_id')
            ->select('t.*', 'co.nome as clinica_origem', 'cd.nome as clinica_destino', 'u.nome_completo as usuario_nome')
            ->orderByDesc('t.enviado_em')
            ->limit(100)
            ->get();

        return view('transferencias.index', compact('transferencias'));
    }

    public function create(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        $medicamentos = DB::table('medicamentos')->where('ativo', 1)->orderBy('nome')->get();

        return view('transferencias.create', compact('clinicas', 'medicamentos'));
    }

    public function store(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'clinica_origem_id' => 'required|integer',
            'clinica_destino_id' => 'required|integer',
            'medicamento_id' => 'required|integer',
            'quantidade' => 'required|integer|min:1',
            'observacao' => 'nullable|max:255',
        ]);

        if ((int) $data['clinica_origem_id'] === (int) $data['clinica_destino_id']) {
            return back()->withErrors(['error' => 'Origem e destino devem ser diferentes.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $uid = $request->session()->get('usuario_id');
            $this->subtrairEstoque($data['clinica_origem_id'], $data['medicamento_id'], $data['quantidade']);

            $transferenciaId = DB::table('transferencias')->insertGetId([
                'clinica_origem_id' => $data['clinica_origem_id'],
                'clinica_destino_id' => $data['clinica_destino_id'],
                'usuario_envio_id' => $uid,
                'status' => 'pendente',
                'observacao' => $data['observacao'] ?? null,
                'enviado_em' => now(),
            ]);

            DB::table('transferencia_itens')->insert([
                'transferencia_id' => $transferenciaId,
                'medicamento_id' => $data['medicamento_id'],
                'quantidade_enviada' => $data['quantidade'],
                'quantidade_recebida' => 0,
            ]);

            DB::commit();

            return redirect()->route('transferencias.index')->with('success', 'Transferência registrada com sucesso.');
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

        $transferencia = DB::table('transferencias')->where('id', $id)->firstOrFail();
        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        $medicamentos = DB::table('medicamentos')->where('ativo', 1)->orderBy('nome')->get();
        $item = DB::table('transferencia_itens')->where('transferencia_id', $id)->first();

        return view('transferencias.edit', compact('transferencia', 'clinicas', 'medicamentos', 'item'));
    }

    public function update(Request $request, string $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'clinica_origem_id' => 'required|integer',
            'clinica_destino_id' => 'required|integer',
            'medicamento_id' => 'required|integer',
            'quantidade' => 'required|integer|min:1',
            'observacao' => 'nullable|max:255',
        ]);

        if ((int) $data['clinica_origem_id'] === (int) $data['clinica_destino_id']) {
            return back()->withErrors(['error' => 'Origem e destino devem ser diferentes.'])->withInput();
        }

        DB::table('transferencias')->where('id', $id)->update([
            'clinica_origem_id' => $data['clinica_origem_id'],
            'clinica_destino_id' => $data['clinica_destino_id'],
            'observacao' => $data['observacao'] ?? null,
        ]);

        DB::table('transferencia_itens')->where('transferencia_id', $id)->update([
            'medicamento_id' => $data['medicamento_id'],
            'quantidade_enviada' => $data['quantidade'],
        ]);

        return redirect()->route('transferencias.index')->with('success', 'Transferência atualizada com sucesso.');
    }

    public function destroy(Request $request, string $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        DB::table('transferencia_itens')->where('transferencia_id', $id)->delete();
        DB::table('transferencias')->where('id', $id)->delete();

        return redirect()->route('transferencias.index')->with('success', 'Transferência removida com sucesso.');
    }

    private function subtrairEstoque(int $clinicaId, int $medicamentoId, int $quantidade): void
    {
        $estoque = DB::table('estoque')->where('clinica_id', $clinicaId)->where('medicamento_id', $medicamentoId)->first();

        if (!$estoque || (int) $estoque->quantidade < $quantidade) {
            throw new \RuntimeException('Estoque insuficiente na clínica de origem.');
        }

        DB::table('estoque')->where('clinica_id', $clinicaId)->where('medicamento_id', $medicamentoId)->update([
            'quantidade' => (int) $estoque->quantidade - $quantidade,
            'atualizado_em' => now(),
        ]);
    }
}

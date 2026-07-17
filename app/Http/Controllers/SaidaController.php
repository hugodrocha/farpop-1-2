<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaidaController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $saidas = DB::table('saidas_medicamentos as s')
            ->join('clinicas as c', 'c.id', '=', 's.clinica_id')
            ->join('medicamentos as m', 'm.id', '=', 's.medicamento_id')
            ->join('pacientes as p', 'p.id', '=', 's.paciente_id')
            ->leftJoin('usuarios as u', 'u.id', '=', 's.usuario_id')
            ->select('s.*', 'c.nome as clinica_nome', 'm.nome as medicamento_nome', 'm.dosagem', 'p.nome as paciente_nome', 'u.nome_completo as usuario_nome')
            ->orderByDesc('s.data_saida')
            ->limit(100)
            ->get();

        return view('saidas.index', compact('saidas'));
    }

    public function create(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        $medicamentos = DB::table('medicamentos')->where('ativo', 1)->orderBy('nome')->get();

        return view('saidas.create', compact('clinicas', 'medicamentos'));
    }

    public function store(Request $request)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'clinica_id' => 'required|integer',
            'medicamento_id' => 'required|integer',
            'paciente_cpf' => 'required|digits:11',
            'paciente_nome' => 'required|max:180',
            'quantidade' => 'required|integer|min:1',
            'prescritor' => 'nullable|max:180',
            'numero_solicitacao' => 'nullable|max:80',
            'observacao' => 'nullable|max:255',
        ]);

        $pacienteId = $this->criarOuAtualizarPaciente($data);

        DB::beginTransaction();
        try {
            $uid = $request->session()->get('usuario_id');
            $this->subtrairEstoque($data['clinica_id'], $data['medicamento_id'], $data['quantidade']);

            DB::table('saidas_medicamentos')->insert([
                'clinica_id' => $data['clinica_id'],
                'medicamento_id' => $data['medicamento_id'],
                'paciente_id' => $pacienteId,
                'quantidade' => $data['quantidade'],
                'prescritor' => $data['prescritor'] ?? null,
                'numero_solicitacao' => $data['numero_solicitacao'] ?? null,
                'observacao' => $data['observacao'] ?? null,
                'usuario_id' => $uid,
                'data_saida' => now(),
            ]);

            DB::commit();

            return redirect()->route('saidas.index')->with('success', 'Saída registrada com sucesso.');
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

        $saida = DB::table('saidas_medicamentos')->where('id', $id)->firstOrFail();
        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        $medicamentos = DB::table('medicamentos')->where('ativo', 1)->orderBy('nome')->get();
        $paciente = DB::table('pacientes')->where('id', $saida->paciente_id)->first();

        return view('saidas.edit', compact('saida', 'clinicas', 'medicamentos', 'paciente'));
    }

    public function update(Request $request, string $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'clinica_id' => 'required|integer',
            'medicamento_id' => 'required|integer',
            'paciente_cpf' => 'required|digits:11',
            'paciente_nome' => 'required|max:180',
            'quantidade' => 'required|integer|min:1',
            'prescritor' => 'nullable|max:180',
            'numero_solicitacao' => 'nullable|max:80',
            'observacao' => 'nullable|max:255',
        ]);

        $pacienteId = $this->criarOuAtualizarPaciente($data);

        DB::table('saidas_medicamentos')->where('id', $id)->update([
            'clinica_id' => $data['clinica_id'],
            'medicamento_id' => $data['medicamento_id'],
            'paciente_id' => $pacienteId,
            'quantidade' => $data['quantidade'],
            'prescritor' => $data['prescritor'] ?? null,
            'numero_solicitacao' => $data['numero_solicitacao'] ?? null,
            'observacao' => $data['observacao'] ?? null,
        ]);

        return redirect()->route('saidas.index')->with('success', 'Saída atualizada com sucesso.');
    }

    public function destroy(Request $request, string $id)
    {
        if (!$request->session()->has('usuario_id')) {
            return redirect()->route('login');
        }

        DB::table('saidas_medicamentos')->where('id', $id)->delete();

        return redirect()->route('saidas.index')->with('success', 'Saída removida com sucesso.');
    }

    private function criarOuAtualizarPaciente(array $data): int
    {
        $paciente = DB::table('pacientes')->where('cpf', $data['paciente_cpf'])->first();

        if ($paciente) {
            DB::table('pacientes')->where('id', $paciente->id)->update([
                'nome' => $data['paciente_nome'],
                'data_nascimento' => $data['paciente_nascimento'] ?? null,
                'telefone' => $data['paciente_telefone'] ?? null,
                'endereco' => $data['paciente_endereco'] ?? null,
                'cartao_sus' => $data['paciente_sus'] ?? null,
            ]);

            return $paciente->id;
        }

        return DB::table('pacientes')->insertGetId([
            'cpf' => $data['paciente_cpf'],
            'nome' => $data['paciente_nome'],
            'data_nascimento' => $data['paciente_nascimento'] ?? null,
            'telefone' => $data['paciente_telefone'] ?? null,
            'endereco' => $data['paciente_endereco'] ?? null,
            'cartao_sus' => $data['paciente_sus'] ?? null,
        ]);
    }

    private function subtrairEstoque(int $clinicaId, int $medicamentoId, int $quantidade): void
    {
        $estoque = DB::table('estoque')->where('clinica_id', $clinicaId)->where('medicamento_id', $medicamentoId)->first();

        if (!$estoque || (int) $estoque->quantidade < $quantidade) {
            throw new \RuntimeException('Estoque insuficiente para este medicamento.');
        }

        DB::table('estoque')->where('clinica_id', $clinicaId)->where('medicamento_id', $medicamentoId)->update([
            'quantidade' => (int) $estoque->quantidade - $quantidade,
            'atualizado_em' => now(),
        ]);
    }
}

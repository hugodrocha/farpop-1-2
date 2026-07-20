<?php

namespace App\Http\Controllers;

use App\Services\ModuloService;
use App\Services\SaidaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PacienteController extends Controller
{
    public function index(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'cadastros', 'pacientes')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão para acessar pacientes.']);
        }

        $q = trim((string) $request->query('q', ''));

        $query = DB::table('pacientes')->orderByDesc('id');

        if ($q !== '') {
            $cpfLimpo = SaidaService::limparCpf($q);
            $query->where(function ($sub) use ($q, $cpfLimpo) {
                $sub->where('nome', 'like', '%' . $q . '%');
                if ($cpfLimpo !== '') {
                    $sub->orWhere('cpf', 'like', '%' . $cpfLimpo . '%');
                }
                $sub->orWhere('cartao_sus', 'like', '%' . $q . '%');
            });
        }

        $pacientes = $query->limit(200)->get();

        return view('pacientes.index', compact('pacientes', 'q'));
    }

    public function show(Request $request, int $id)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'cadastros', 'pacientes')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão para acessar pacientes.']);
        }

        $paciente = DB::table('pacientes')->where('id', $id)->firstOrFail();

        $historico = DB::table('saidas_medicamentos as s')
            ->join('medicamentos as m', 'm.id', '=', 's.medicamento_id')
            ->join('clinicas as c', 'c.id', '=', 's.clinica_id')
            ->where('s.paciente_id', $id)
            ->orderByDesc('s.data_saida')
            ->limit(100)
            ->get([
                's.*',
                'm.nome as medicamento',
                'c.nome as clinica',
            ]);

        return view('pacientes.show', compact('paciente', 'historico'));
    }

    public function edit(Request $request, int $id)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'cadastros', 'pacientes')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $paciente = DB::table('pacientes')->where('id', $id)->firstOrFail();

        return view('pacientes.edit', compact('paciente'));
    }

    public function update(Request $request, int $id)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'cadastros', 'pacientes')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $paciente = DB::table('pacientes')->where('id', $id)->firstOrFail();

        $data = $request->validate([
            'nome' => 'required|max:180',
            'cpf' => 'nullable|max:14',
            'cartao_sus' => 'nullable|max:20',
            'data_nascimento' => 'nullable|date',
            'telefone' => 'nullable|max:40',
            'endereco' => 'nullable|max:255',
        ]);

        $cpf = SaidaService::limparCpf($data['cpf'] ?? '');
        if ($cpf !== '') {
            $dup = DB::table('pacientes')->where('cpf', $cpf)->where('id', '<>', $id)->exists();
            if ($dup) {
                return back()->withErrors(['cpf' => 'CPF já cadastrado para outro paciente.'])->withInput();
            }
        }

        DB::table('pacientes')->where('id', $id)->update([
            'nome' => $data['nome'],
            'cpf' => $cpf ?: null,
            'cartao_sus' => $data['cartao_sus'] ?: null,
            'data_nascimento' => $data['data_nascimento'] ?: null,
            'telefone' => $data['telefone'] ?: null,
            'endereco' => $data['endereco'] ?: null,
        ]);

        return redirect()->route('pacientes.show', $id)->with('success', 'Paciente atualizado.');
    }
}

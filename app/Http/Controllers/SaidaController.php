<?php

namespace App\Http\Controllers;

use App\Services\AuditoriaService;
use App\Services\EstoqueService;
use App\Services\PerfilService;
use App\Services\PecService;
use App\Services\SaidaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SaidaController extends Controller
{
    public function index()
    {
        $query = DB::table('saidas_medicamentos as s')
            ->join('clinicas as c', 'c.id', '=', 's.clinica_id')
            ->join('medicamentos as m', 'm.id', '=', 's.medicamento_id')
            ->join('pacientes as p', 'p.id', '=', 's.paciente_id')
            ->leftJoin('usuarios as u', 'u.id', '=', 's.usuario_id')
            ->select('s.*', 'c.nome as clinica_nome', 'm.nome as medicamento_nome', 'm.dosagem', 'p.nome as paciente_nome', 'u.nome_completo as usuario_nome')
            ->orderByDesc('s.data_saida')
            ->limit(100);

        if (!PerfilService::ehAdmin(session('perfil', '')) && session('clinica_id')) {
            $query->where('s.clinica_id', session('clinica_id'));
        }

        $saidas = $query->get();

        return view('saidas.index', compact('saidas'));
    }

    public function create()
    {
        $perfil = session('perfil', '');
        $clinicaFixa = !PerfilService::ehAdmin($perfil) && session('clinica_id');
        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        $medicamentos = DB::table('medicamentos')->where('ativo', 1)->orderBy('nome')->get();
        $pecStatus = PecService::status();

        return view('saidas.create', compact('clinicas', 'medicamentos', 'clinicaFixa', 'pecStatus'));
    }

    public function store(Request $request)
    {
        $perfil = session('perfil', '');
        $clinicaFixa = !PerfilService::ehAdmin($perfil) && session('clinica_id');

        $data = $request->validate([
            'clinica_id' => 'required|integer',
            'paciente_cpf' => 'required|digits:11',
            'paciente_nome' => 'required|max:180',
            'paciente_nascimento' => 'nullable|max:20',
            'paciente_telefone' => 'nullable|max:30',
            'paciente_endereco' => 'nullable|max:255',
            'paciente_sus' => 'nullable|max:30',
            'prescritor' => 'nullable|max:180',
            'numero_solicitacao' => 'nullable|max:80',
            'med_id' => 'required|array|min:1',
            'med_id.*' => 'integer',
            'med_qtd' => 'required|array',
            'med_qtd.*' => 'integer|min:1',
            'med_obs' => 'nullable|array',
        ]);

        $clinicaId = $clinicaFixa ? (int) session('clinica_id') : (int) $data['clinica_id'];

        $itens = [];
        $idsVistos = [];
        foreach ($data['med_id'] as $i => $medId) {
            $medId = (int) $medId;
            $qtd = (int) ($data['med_qtd'][$i] ?? 0);
            if ($medId <= 0 || $qtd <= 0) {
                continue;
            }
            if (isset($idsVistos[$medId])) {
                return back()->withErrors(['error' => 'Medicamento duplicado na lista.'])->withInput();
            }
            $idsVistos[$medId] = true;
            $itens[] = [
                'medicamento_id' => $medId,
                'quantidade' => $qtd,
                'observacao' => $data['med_obs'][$i] ?? null,
            ];
        }

        if (empty($itens)) {
            return back()->withErrors(['error' => 'Adicione ao menos um medicamento.'])->withInput();
        }

        try {
            $pacienteId = SaidaService::criarOuAtualizarPaciente($data);
            $saidaIds = SaidaService::registrarDispensacao(
                $clinicaId,
                $pacienteId,
                $itens,
                [
                    'cpf' => $data['paciente_cpf'],
                    'prescritor' => $data['prescritor'] ?? null,
                    'numero_solicitacao' => $data['numero_solicitacao'] ?? null,
                ],
                (int) session('usuario_id')
            );

            return redirect()->route('saidas.comprovante', $saidaIds[0])->with('success', 'Dispensação registrada com sucesso!');
        } catch (RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function comprovante(string $id)
    {
        $saida = DB::table('saidas_medicamentos as s')
            ->join('clinicas as c', 'c.id', '=', 's.clinica_id')
            ->join('medicamentos as m', 'm.id', '=', 's.medicamento_id')
            ->join('pacientes as p', 'p.id', '=', 's.paciente_id')
            ->leftJoin('usuarios as u', 'u.id', '=', 's.usuario_id')
            ->where('s.id', $id)
            ->select('s.*', 'c.nome as clinica', 'm.nome as medicamento', 'm.dosagem', 'm.unidade', 'p.nome as paciente', 'p.cpf', 'u.nome_completo as atendente')
            ->firstOrFail();

        $itens = DB::table('saidas_medicamentos as s')
            ->join('medicamentos as m', 'm.id', '=', 's.medicamento_id')
            ->where('s.paciente_id', $saida->paciente_id)
            ->where('s.clinica_id', $saida->clinica_id)
            ->whereDate('s.data_saida', date('Y-m-d', strtotime($saida->data_saida)))
            ->where('s.usuario_id', $saida->usuario_id)
            ->select('s.*', 'm.nome as medicamento', 'm.dosagem', 'm.unidade')
            ->get();

        return view('saidas.comprovante', compact('saida', 'itens'));
    }

    public function edit(string $id)
    {
        return redirect()->route('saidas.index');
    }

    public function update(Request $request, string $id)
    {
        return redirect()->route('saidas.index');
    }

    public function destroy(string $id)
    {
        return redirect()->route('saidas.index');
    }
}

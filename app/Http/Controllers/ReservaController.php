<?php

namespace App\Http\Controllers;

use App\Services\ModuloService;
use App\Services\PerfilService;
use App\Services\ReservaService;
use App\Services\SaidaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReservaController extends Controller
{
    public function index(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessar($perfil, 'dispensa')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        ReservaService::inicializar();

        $clinicaId = (int) $request->session()->get('clinica_id', 0);
        $q = DB::table('reservas_medicamentos as r')
            ->join('pacientes as p', 'p.id', '=', 'r.paciente_id')
            ->join('medicamentos as m', 'm.id', '=', 'r.medicamento_id')
            ->join('clinicas as c', 'c.id', '=', 'r.clinica_id')
            ->select('r.*', 'p.nome as paciente', 'p.cpf', 'm.nome as medicamento', 'c.nome as clinica')
            ->orderByDesc('r.criado_em')
            ->limit(200);

        if (!PerfilService::ehAdmin($perfil) && $clinicaId > 0) {
            $q->where('r.clinica_id', $clinicaId);
        }

        if ($request->query('status')) {
            $q->where('r.status', $request->query('status'));
        }

        $reservas = $q->get();

        return view('reservas.index', compact('reservas'));
    }

    public function create(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessar($perfil, 'dispensa')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $clinicaFixa = !PerfilService::ehAdmin($perfil) && $request->session()->get('clinica_id');
        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        $medicamentos = DB::table('medicamentos')->where('ativo', 1)->orderBy('nome')->limit(500)->get();

        return view('reservas.create', compact('clinicas', 'clinicaFixa', 'medicamentos'));
    }

    public function store(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessar($perfil, 'dispensa')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $data = $request->validate([
            'paciente_cpf' => 'required|max:20',
            'paciente_nome' => 'required|max:180',
            'medicamento_id' => 'required|integer',
            'clinica_id' => 'required|integer',
            'quantidade' => 'required|integer|min:1',
            'validade_reserva' => 'nullable|date',
            'observacao' => 'nullable|max:255',
        ]);

        $clinicaFixa = !PerfilService::ehAdmin($perfil) && $request->session()->get('clinica_id');
        $clinicaId = $clinicaFixa ? (int) $request->session()->get('clinica_id') : (int) $data['clinica_id'];

        $pacienteId = SaidaService::criarOuAtualizarPaciente([
            'paciente_cpf' => $data['paciente_cpf'],
            'paciente_nome' => $data['paciente_nome'],
        ]);

        try {
            ReservaService::criar(
                $pacienteId,
                (int) $data['medicamento_id'],
                $clinicaId,
                (int) $data['quantidade'],
                $data['validade_reserva'] ?? null,
                $data['observacao'] ?? null,
                (int) $request->session()->get('usuario_id')
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        return redirect()->route('reservas.index')->with('success', 'Reserva registrada.');
    }

    public function cancelar(Request $request, int $id)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessar($perfil, 'dispensa')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        ReservaService::cancelar($id, (int) $request->session()->get('usuario_id'));

        return back()->with('success', 'Reserva cancelada.');
    }
}

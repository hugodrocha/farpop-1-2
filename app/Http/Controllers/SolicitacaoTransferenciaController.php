<?php

namespace App\Http\Controllers;

use App\Services\EstoqueService;
use App\Services\ModuloService;
use App\Services\PerfilService;
use App\Services\SolicitacaoTransferenciaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SolicitacaoTransferenciaController extends Controller
{
    public function index(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'transferencias', 'solicitacao')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $clinicaId = (int) $request->session()->get('clinica_id', 0);
        $centralId = EstoqueService::getCentralId();
        $visaoCentral = PerfilService::ehAdmin($perfil) || ($centralId && $clinicaId === $centralId);

        $status = $request->query('status');
        $filtroClinica = $visaoCentral ? null : $clinicaId;
        $solicitacoes = SolicitacaoTransferenciaService::listar($filtroClinica, $status ?: null);

        return view('solicitacoes.index', compact('solicitacoes', 'visaoCentral', 'status'));
    }

    public function create(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'transferencias', 'solicitacao')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $clinicaId = (int) $request->session()->get('clinica_id', 0);
        $centralId = EstoqueService::getCentralId();
        if ($centralId && $clinicaId === $centralId && !PerfilService::ehAdmin($perfil)) {
            return redirect()->route('solicitacoes.index')->withErrors(['error' => 'A central utiliza envio direto de transferências.']);
        }

        return view('solicitacoes.create');
    }

    public function store(Request $request)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'transferencias', 'solicitacao')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        $data = $request->validate([
            'observacao' => 'nullable|max:500',
            'med_id' => 'required|array|min:1',
            'med_id.*' => 'integer',
            'med_qtd' => 'required|array',
            'med_qtd.*' => 'integer|min:1',
            'med_obs' => 'nullable|array',
        ]);

        $clinicaId = (int) $request->session()->get('clinica_id', 0);
        if ($clinicaId <= 0) {
            return back()->withErrors(['error' => 'Usuário sem unidade vinculada.'])->withInput();
        }

        $itens = [];
        foreach ($data['med_id'] as $i => $medId) {
            $qtd = (int) ($data['med_qtd'][$i] ?? 0);
            if ((int) $medId > 0 && $qtd > 0) {
                $itens[] = [
                    'medicamento_id' => (int) $medId,
                    'quantidade' => $qtd,
                    'observacao' => trim($data['med_obs'][$i] ?? '') ?: null,
                ];
            }
        }

        try {
            $id = SolicitacaoTransferenciaService::criar(
                $clinicaId,
                (int) $request->session()->get('usuario_id'),
                $data['observacao'] ?? null,
                $itens
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        return redirect()->route('solicitacoes.show', $id)->with('success', 'Solicitação registrada. Aguarde aprovação da central.');
    }

    public function show(Request $request, int $id)
    {
        $perfil = (string) $request->session()->get('perfil', '');
        if (!ModuloService::podeAcessarSubmodulo($perfil, 'transferencias', 'solicitacao')) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Sem permissão.']);
        }

        SolicitacaoTransferenciaService::inicializar();
        $sol = DB::table('transferencia_solicitacoes as s')
            ->join('clinicas as cs', 'cs.id', '=', 's.clinica_solicitante_id')
            ->join('clinicas as co', 'co.id', '=', 's.clinica_origem_id')
            ->leftJoin('usuarios as u', 'u.id', '=', 's.usuario_solicitante_id')
            ->where('s.id', $id)
            ->select('s.*', 'cs.nome as solicitante', 'co.nome as origem', 'u.nome_completo as usuario_nome')
            ->firstOrFail();

        $clinicaId = (int) $request->session()->get('clinica_id', 0);
        $centralId = EstoqueService::getCentralId();
        $visaoCentral = PerfilService::ehAdmin($perfil) || ($centralId && $clinicaId === $centralId);
        if (!$visaoCentral && $clinicaId !== (int) $sol->clinica_solicitante_id) {
            return redirect()->route('solicitacoes.index')->withErrors(['error' => 'Sem permissão para ver esta solicitação.']);
        }

        $itens = SolicitacaoTransferenciaService::itens($id);

        return view('solicitacoes.show', compact('sol', 'itens', 'visaoCentral'));
    }

    public function aprovar(Request $request, int $id)
    {
        if (!$this->podeGerenciarCentral($request)) {
            return back()->withErrors(['error' => 'Somente a central pode aprovar solicitações.']);
        }

        $qtds = $request->input('quantidade_aprovada', []);

        try {
            SolicitacaoTransferenciaService::aprovar($id, (int) $request->session()->get('usuario_id'), $qtds);
        } catch (RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Solicitação aprovada.');
    }

    public function recusar(Request $request, int $id)
    {
        if (!$this->podeGerenciarCentral($request)) {
            return back()->withErrors(['error' => 'Somente a central pode recusar solicitações.']);
        }

        $data = $request->validate(['motivo' => 'required|max:500']);

        try {
            SolicitacaoTransferenciaService::recusar($id, (int) $request->session()->get('usuario_id'), $data['motivo']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Solicitação recusada.');
    }

    public function separar(Request $request, int $id)
    {
        if (!$this->podeGerenciarCentral($request)) {
            return back()->withErrors(['error' => 'Somente a central pode iniciar separação.']);
        }

        try {
            SolicitacaoTransferenciaService::marcarSeparacao($id, (int) $request->session()->get('usuario_id'));
        } catch (RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Separação iniciada.');
    }

    public function enviar(Request $request, int $id)
    {
        if (!$this->podeGerenciarCentral($request)) {
            return back()->withErrors(['error' => 'Somente a central pode enviar.']);
        }

        try {
            $transferenciaId = SolicitacaoTransferenciaService::enviar($id, (int) $request->session()->get('usuario_id'));
        } catch (RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('transferencias.nota', $transferenciaId)->with('success', 'Transferência gerada a partir da solicitação.');
    }

    private function podeGerenciarCentral(Request $request): bool
    {
        $perfil = (string) $request->session()->get('perfil', '');
        $clinicaId = (int) $request->session()->get('clinica_id', 0);
        $centralId = EstoqueService::getCentralId();

        return PerfilService::ehAdmin($perfil)
            || PerfilService::podeEnviarTransferencia($perfil, $clinicaId, $centralId);
    }
}

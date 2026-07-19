<?php

namespace App\Http\Controllers;

use App\Services\MedicamentoBuscaService;
use App\Services\EstoqueService;
use App\Services\PerfilService;
use App\Services\SaidaService;
use App\Services\TransferenciaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TransferenciaController extends Controller
{
    public function index()
    {
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

    public function create()
    {
        $centralId = EstoqueService::getCentralId();
        $perfil = session('perfil', '');
        $clinicaUsuario = (int) session('clinica_id', 0);

        if (!PerfilService::podeEnviarTransferencia($perfil, $clinicaUsuario, $centralId)) {
            return redirect()->route('transferencias.transito')->withErrors(['error' => 'Apenas admin ou farmacêutico da Central pode enviar lotes.']);
        }

        $clinicas = DB::table('clinicas')->where('ativo', 1)->where('id', '!=', $centralId)->orderBy('nome')->get();
        $medicamentos = DB::table('medicamentos')->where('ativo', 1)->orderBy('nome')->get();

        return view('transferencias.create', compact('clinicas', 'medicamentos', 'centralId'));
    }

    public function buscarMedicamento(Request $request)
    {
        $codigo = trim($request->query('codigo', ''));
        $id = (int) $request->query('id', 0);
        $centralId = EstoqueService::getCentralId();

        if ($codigo !== '') {
            $med = MedicamentoBuscaService::buscarPorCodigoOuBarras($codigo);
        } elseif ($id > 0) {
            $med = DB::table('medicamentos')->where('id', $id)->where('ativo', 1)->first();
        } else {
            return response()->json(['ok' => false, 'erro' => 'Informe código ou id.']);
        }

        if (!$med) {
            return response()->json(['ok' => false, 'erro' => 'Medicamento não encontrado.']);
        }

        return response()->json([
            'ok' => true,
            'medicamento' => [
                'id' => (int) $med->id,
                'nome' => $med->nome,
                'dosagem' => $med->dosagem,
                'unidade' => $med->unidade,
                'codigo' => $med->codigo,
                'codigo_barras' => $med->codigo_barras,
                'estoque_central' => EstoqueService::getAtual($centralId, (int) $med->id),
            ],
        ]);
    }

    public function finalizarLote(Request $request)
    {
        $centralId = EstoqueService::getCentralId();
        $perfil = session('perfil', '');
        $clinicaUsuario = (int) session('clinica_id', 0);

        if (!PerfilService::podeEnviarTransferencia($perfil, $clinicaUsuario, $centralId)) {
            return back()->withErrors(['error' => 'Sem permissão.']);
        }

        $data = $request->validate([
            'clinica_destino_id' => 'required|integer',
            'observacao' => 'nullable|max:500',
            'itens_json' => 'required|string',
        ]);

        $itens = json_decode($data['itens_json'], true);
        if (!is_array($itens)) {
            return back()->withErrors(['error' => 'Lista de itens inválida.'])->withInput();
        }

        try {
            $id = TransferenciaService::finalizarLote(
                $centralId,
                (int) $data['clinica_destino_id'],
                (int) session('usuario_id'),
                $data['observacao'] ?? null,
                $itens
            );

            return redirect()->route('transferencias.nota', $id)->with('success', 'Lote enviado com sucesso!');
        } catch (RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function cancelar(Request $request, string $id)
    {
        $request->validate(['motivo' => 'required|max:255']);

        try {
            TransferenciaService::cancelar(
                (int) $id,
                (int) session('usuario_id'),
                $request->input('motivo'),
                session('perfil', ''),
                (int) session('clinica_id', 0)
            );

            return redirect()->route('transferencias.index')->with('success', 'Transferência cancelada.');
        } catch (RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        return $this->finalizarLote($request);
    }

    public function edit(string $id)
    {
        return redirect()->route('transferencias.index');
    }

    public function update(Request $request, string $id)
    {
        return redirect()->route('transferencias.index');
    }

    public function destroy(Request $request, string $id)
    {
        $request->validate(['motivo' => 'required|max:255']);

        return $this->cancelar($request, $id);
    }
}

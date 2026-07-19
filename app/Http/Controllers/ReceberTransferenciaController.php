<?php

namespace App\Http\Controllers;

use App\Services\PerfilService;
use App\Services\TransferenciaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReceberTransferenciaController extends Controller
{
    public function index(Request $request)
    {
        $perfil = session('perfil', '');
        if (!in_array($perfil, ['superuser', 'admin', 'gestor', 'farmaceutico', 'tecnico'], true)) {
            return redirect()->route('transferencias.transito');
        }

        $codigo = $request->query('codigo');

        return view('transferencias.receber', compact('codigo'));
    }

    public function buscarNota(Request $request)
    {
        $codigo = trim($request->query('codigo', ''));
        if ($codigo === '') {
            return response()->json(['ok' => false, 'erro' => 'Código vazio.']);
        }

        $tid = (int) $codigo;
        $t = DB::table('transferencias as t')
            ->join('clinicas as co', 'co.id', '=', 't.clinica_origem_id')
            ->join('clinicas as cd', 'cd.id', '=', 't.clinica_destino_id')
            ->join('usuarios as ue', 'ue.id', '=', 't.usuario_envio_id')
            ->where(function ($q) use ($codigo, $tid) {
                $q->where('t.codigo_nota', $codigo)->orWhere('t.id', $tid);
            })
            ->select('t.*', 'co.nome as clinica_origem', 'cd.nome as clinica_destino', 'ue.nome_completo as enviado_por')
            ->first();

        if (!$t) {
            return response()->json(['ok' => false, 'erro' => "Nota '{$codigo}' não encontrada."]);
        }

        $ehAdmin = PerfilService::ehAdmin(session('perfil', ''));
        $clinicaUsuario = (int) session('clinica_id', 0);
        $podeReceber = $ehAdmin || (in_array(session('perfil'), ['farmaceutico', 'tecnico'], true) && $clinicaUsuario === (int) $t->clinica_destino_id);

        if (!$podeReceber) {
            return response()->json(['ok' => false, 'erro' => 'Esta nota é destinada a outra clínica.']);
        }

        if ($t->status !== 'pendente') {
            return response()->json(['ok' => false, 'erro' => 'Esta nota não está pendente (status: ' . $t->status . ').']);
        }

        $itens = DB::table('transferencia_itens as ti')
            ->join('medicamentos as m', 'm.id', '=', 'ti.medicamento_id')
            ->where('ti.transferencia_id', $t->id)
            ->select('ti.*', 'm.nome', 'm.dosagem', 'm.unidade', 'm.codigo_barras')
            ->orderBy('m.nome')
            ->get();

        return response()->json([
            'ok' => true,
            'transferencia' => [
                'id' => (int) $t->id,
                'codigo_nota' => $t->codigo_nota,
                'clinica_origem' => $t->clinica_origem,
                'clinica_destino' => $t->clinica_destino,
                'enviado_por' => $t->enviado_por,
                'enviado_em' => $t->enviado_em,
                'observacao' => $t->observacao,
            ],
            'itens' => $itens,
        ]);
    }

    public function confirmar(Request $request)
    {
        $data = $request->validate([
            'transferencia_id' => 'required|integer',
            'tipo_recebimento' => 'required|in:ok,parcial,nao_recebido',
            'observacao_recebimento' => 'nullable|max:500',
            'recebidas_json' => 'required|string',
        ]);

        $recebidas = json_decode($data['recebidas_json'], true);
        if (!is_array($recebidas)) {
            return back()->withErrors(['error' => 'Dados de recebimento inválidos.']);
        }

        try {
            TransferenciaService::confirmarRecebimento(
                (int) $data['transferencia_id'],
                (int) session('usuario_id'),
                (int) session('clinica_id', 0),
                PerfilService::ehAdmin(session('perfil', '')),
                $recebidas,
                $data['observacao_recebimento'] ?? null,
                $data['tipo_recebimento']
            );

            return redirect()->route('transferencias.receber')->with('success', 'Recebimento confirmado com sucesso!');
        } catch (RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

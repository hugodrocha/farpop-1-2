<?php

namespace App\Http\Controllers;

use App\Services\AuditoriaService;
use App\Services\EstoqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditoriaController extends Controller
{
    public const FRASE_ZERAR_TUDO = 'ZERAR TODO O ESTOQUE';

    public function index(Request $request)
    {
        $query = DB::table('auditoria')->orderByDesc('criado_em');

        if ($request->filled('acao')) {
            $query->where('acao', 'like', '%' . $request->acao . '%');
        }
        if ($request->filled('usuario')) {
            $query->where(function ($q) use ($request) {
                $q->where('usuario_responsavel', 'like', '%' . $request->usuario . '%')
                    ->orWhere('nome_responsavel', 'like', '%' . $request->usuario . '%');
            });
        }

        $registros = $query->limit(200)->get();
        $clinicas = DB::table('clinicas')->where('ativo', 1)->orderBy('nome')->get();
        $medicamentos = DB::table('medicamentos')->where('ativo', 1)->orderBy('nome')->limit(200)->get();
        $ehSuperuser = session('perfil') === 'superuser';

        return view('auditoria.index', compact('registros', 'clinicas', 'medicamentos', 'ehSuperuser'));
    }

    public function acaoCritica(Request $request)
    {
        if (session('perfil') !== 'superuser') {
            return back()->withErrors(['error' => 'Apenas superuser pode executar ações críticas.']);
        }

        $data = $request->validate([
            'acao_critica' => 'required|in:zerar_estoque,zerar_estoque_clinica,zerar_estoque_medicamento',
            'frase_confirmacao' => 'required|string',
            'clinica_id' => 'nullable|integer',
            'medicamento_id' => 'nullable|integer',
        ]);

        switch ($data['acao_critica']) {
            case 'zerar_estoque':
                if ($data['frase_confirmacao'] !== self::FRASE_ZERAR_TUDO) {
                    return back()->withErrors(['error' => 'Frase de confirmação incorreta.']);
                }
                EstoqueService::zerarTudo();
                AuditoriaService::registrar('ZERAR_ESTOQUE', 'estoque_global', 'Todo estoque zerado pelo superuser');
                break;

            case 'zerar_estoque_clinica':
                $clinica = DB::table('clinicas')->where('id', $data['clinica_id'])->first();
                if (!$clinica) {
                    return back()->withErrors(['error' => 'Clínica inválida.']);
                }
                $frase = 'ZERAR ESTOQUE DA CLINICA: ' . strtoupper($clinica->nome);
                if ($data['frase_confirmacao'] !== $frase) {
                    return back()->withErrors(['error' => 'Frase de confirmação incorreta.']);
                }
                EstoqueService::zerarClinica((int) $clinica->id);
                AuditoriaService::registrar('ZERAR_ESTOQUE_CLINICA', 'clinica:' . $clinica->id, $clinica->nome);
                break;

            case 'zerar_estoque_medicamento':
                $med = DB::table('medicamentos')->where('id', $data['medicamento_id'])->first();
                if (!$med) {
                    return back()->withErrors(['error' => 'Medicamento inválido.']);
                }
                $frase = 'ZERAR ESTOQUE DO MEDICAMENTO: ' . strtoupper($med->nome);
                if ($data['frase_confirmacao'] !== $frase) {
                    return back()->withErrors(['error' => 'Frase de confirmação incorreta.']);
                }
                EstoqueService::zerarMedicamento((int) $med->id);
                AuditoriaService::registrar('ZERAR_ESTOQUE_MEDICAMENTO', 'medicamento:' . $med->id, $med->nome);
                break;
        }

        return back()->with('success', 'Ação crítica executada e registrada na auditoria.');
    }
}

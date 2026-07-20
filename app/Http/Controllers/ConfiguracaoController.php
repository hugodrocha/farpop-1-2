<?php

namespace App\Http\Controllers;

use App\Services\AuditoriaService;
use App\Services\ConfigService;
use App\Services\EstoqueService;
use App\Services\PecService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        $configs = DB::table('configuracoes')->orderBy('chave')->get()->keyBy('chave');
        $pecStatus = PecService::status();

        return view('configuracoes.index', compact('configs', 'pecStatus'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'alerta_amarelo_percentual' => 'required|integer|min:1|max:500',
            'alerta_vermelho_percentual' => 'required|integer|min:1|max:500',
            'alerta_vencimento_dias' => 'required|integer|min:7|max:365',
            'chat_habilitado' => 'required|in:0,1',
        ]);

        ConfigService::set('alerta_amarelo_percentual', (string) $data['alerta_amarelo_percentual'], 'Percentual do mínimo para alerta amarelo');
        ConfigService::set('alerta_vermelho_percentual', (string) $data['alerta_vermelho_percentual'], 'Percentual do mínimo para alerta vermelho');
        ConfigService::set('alerta_vencimento_dias', (string) $data['alerta_vencimento_dias'], 'Dias de antecedência para alerta de vencimento');
        ConfigService::set('chat_habilitado', (string) $data['chat_habilitado'], 'Chat habilitado (1/0)');

        AuditoriaService::registrar('CONFIG_ATUALIZADA', 'configuracoes', 'Alertas e chat atualizados');

        return back()->with('success', 'Configurações salvas com sucesso.');
    }

    public function testarPec(Request $request)
    {
        $cpf = preg_replace('/\D/', '', $request->input('cpf_teste', ''));
        $resultado = PecService::status();

        if ($cpf !== '' && strlen($cpf) === 11) {
            $cidadao = PecService::buscarCidadaoPorCpf($cpf);
            $resultado['teste_cpf'] = $cidadao;
        }

        return back()->with('pec_teste', $resultado);
    }

    public function backup(Request $request)
    {
        $exit = \Illuminate\Support\Facades\Artisan::call('farpop:backup-banco');
        $output = trim(\Illuminate\Support\Facades\Artisan::output());

        if ($exit !== 0) {
            return back()->withErrors(['error' => $output ?: 'Falha ao gerar backup. Verifique mysqldump no servidor.']);
        }

        AuditoriaService::registrar('BACKUP_BANCO', 'manual', $output);

        return back()->with('success', 'Backup gerado: ' . ($output ?: 'OK'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\AlertaEmailService;
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
            'alerta_email_habilitado' => 'required|in:0,1',
            'alerta_email_destino' => 'nullable|string|max:500',
            'reposicao_dias_historico' => 'required|integer|min:30|max:365',
            'reposicao_dias_cobertura' => 'required|integer|min:7|max:180',
            'reposicao_dias_critico' => 'required|integer|min:1|max:30',
        ]);

        ConfigService::set('alerta_amarelo_percentual', (string) $data['alerta_amarelo_percentual'], 'Percentual do mínimo para alerta amarelo');
        ConfigService::set('alerta_vermelho_percentual', (string) $data['alerta_vermelho_percentual'], 'Percentual do mínimo para alerta vermelho');
        ConfigService::set('alerta_vencimento_dias', (string) $data['alerta_vencimento_dias'], 'Dias de antecedência para alerta de vencimento');
        ConfigService::set('chat_habilitado', (string) $data['chat_habilitado'], 'Chat habilitado (1/0)');
        ConfigService::set('alerta_email_habilitado', (string) $data['alerta_email_habilitado'], 'E-mail diário de alertas (1/0)');
        ConfigService::set('alerta_email_destino', trim((string) ($data['alerta_email_destino'] ?? '')), 'E-mails extras para alertas (separados por vírgula)');
        ConfigService::set('reposicao_dias_historico', (string) $data['reposicao_dias_historico'], 'Dias de histórico para previsão de reposição');
        ConfigService::set('reposicao_dias_cobertura', (string) $data['reposicao_dias_cobertura'], 'Dias de cobertura alvo na reposição');
        ConfigService::set('reposicao_dias_critico', (string) $data['reposicao_dias_critico'], 'Dias restantes para alerta crítico de ruptura');

        AuditoriaService::registrar('CONFIG_ATUALIZADA', 'configuracoes', 'Alertas, reposição e chat atualizados');

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

    public function testarAlertaEmail(Request $request)
    {
        $resultado = AlertaEmailService::enviarResumoDiario();

        if ($resultado['enviados'] > 0) {
            return back()->with('success', "E-mail de teste enviado para {$resultado['enviados']} destinatário(s).");
        }

        $msg = $resultado['erros'][0] ?? 'Falha ao enviar e-mail.';

        return back()->withErrors(['error' => $msg]);
    }
}

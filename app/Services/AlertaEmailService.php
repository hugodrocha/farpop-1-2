<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AlertaEmailService
{
    public static function habilitado(): bool
    {
        return ConfigService::get('alerta_email_habilitado', '0') === '1';
    }

    /**
     * @return list<string>
     */
    public static function destinatarios(): array
    {
        $extra = trim(ConfigService::get('alerta_email_destino', ''));
        if ($extra !== '') {
            return array_values(array_filter(array_map('trim', preg_split('/[,;]/', $extra))));
        }

        return DB::table('usuarios')
            ->where('ativo', 1)
            ->whereIn('perfil', ['superuser', 'admin', 'gestor'])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('email')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array{enviados: int, erros: list<string>}
     */
    public static function enviarResumoDiario(): array
    {
        if (!self::habilitado()) {
            return ['enviados' => 0, 'erros' => ['E-mail de alertas desabilitado nas configurações.']];
        }

        $destinatarios = self::destinatarios();
        if ($destinatarios === []) {
            return ['enviados' => 0, 'erros' => ['Nenhum destinatário com e-mail cadastrado (admin/gestor).']];
        }

        $resumo = AlertaOperacionalService::resumo(null);
        $reposicao = ReposicaoService::contar(null);
        $corpo = self::montarCorpo($resumo, $reposicao);
        $assunto = 'FARPOP — Resumo diário de alertas (' . now()->format('d/m/Y') . ')';

        $enviados = 0;
        $erros = [];

        foreach ($destinatarios as $email) {
            try {
                Mail::raw($corpo, function ($message) use ($email, $assunto) {
                    $message->to($email)->subject($assunto);
                });
                $enviados++;
            } catch (\Throwable $e) {
                Log::warning('FARPOP alerta e-mail falhou: ' . $e->getMessage(), ['email' => $email]);
                $erros[] = "{$email}: {$e->getMessage()}";
            }
        }

        return compact('enviados', 'erros');
    }

    /**
     * @param array<string, mixed> $resumo
     * @param array{ruptura: int, critico: int, atencao: int} $reposicao
     */
    public static function montarCorpo(array $resumo, array $reposicao): string
    {
        $url = url('/alertas');

        return implode("\n", [
            'FARPOP 1.1 — Farmácia Popular',
            'Resumo operacional — ' . now()->format('d/m/Y H:i'),
            '',
            'ESTOQUE',
            '- Críticos: ' . ($resumo['estoque_critico'] ?? 0),
            '- Atenção: ' . ($resumo['estoque_atencao'] ?? 0),
            '',
            'VALIDADE',
            '- Vencidos: ' . ($resumo['vencidos'] ?? 0),
            '- Vencendo (' . ($resumo['dias_vencimento'] ?? 90) . 'd): ' . ($resumo['vencendo'] ?? 0),
            '',
            'REPOSIÇÃO / RUPTURA',
            '- Ruptura (zerado): ' . $reposicao['ruptura'],
            '- Crítico (≤ ' . ReposicaoService::diasCritico() . ' dias): ' . $reposicao['critico'],
            '- Atenção (≤ 30 dias): ' . $reposicao['atencao'],
            '',
            'PENDÊNCIAS',
            '- Transferências: ' . ($resumo['transf_pendentes'] ?? 0),
            '- Solicitações: ' . ($resumo['solicitacoes_pendentes'] ?? 0),
            '- Reservas ativas: ' . ($resumo['reservas_ativas'] ?? 0),
            '',
            'Acesse a central de alertas: ' . $url,
        ]);
    }
}

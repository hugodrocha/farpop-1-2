<?php

namespace App\Console\Commands;

use App\Services\AlertaEmailService;
use App\Services\AuditoriaService;
use Illuminate\Console\Command;

class AlertasEmailCommand extends Command
{
    protected $signature = 'farpop:alertas-email';

    protected $description = 'Envia resumo diário de alertas operacionais por e-mail';

    public function handle(): int
    {
        $resultado = AlertaEmailService::enviarResumoDiario();

        if ($resultado['enviados'] > 0) {
            $this->info("E-mails enviados: {$resultado['enviados']}");
            AuditoriaService::registrar('ALERTA_EMAIL', 'sistema', "{$resultado['enviados']} destinatário(s)");
        }

        foreach ($resultado['erros'] as $erro) {
            $this->warn($erro);
        }

        if ($resultado['enviados'] === 0 && $resultado['erros'] !== []) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}

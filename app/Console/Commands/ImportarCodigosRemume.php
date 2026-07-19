<?php

namespace App\Console\Commands;

use App\Services\RemumeCodigoService;
use Illuminate\Console\Command;

class ImportarCodigosRemume extends Command
{
    protected $signature = 'farpop:importar-codigos-remume {pasta? : Pasta com xl/ extraído do REMUME1.xlsx}';

    protected $description = 'Importa códigos REMUME para medicamentos.codigo';

    public function handle(): int
    {
        $pasta = $this->argument('pasta') ?: storage_path('app/remume1_unzip');
        $resultado = RemumeCodigoService::importarDeXmlExtraido($pasta);

        $this->info("Atualizados: {$resultado['atualizados']} de {$resultado['total']}");

        return self::SUCCESS;
    }
}

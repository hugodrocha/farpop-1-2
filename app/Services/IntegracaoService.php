<?php

namespace App\Services;

class IntegracaoService
{
    /**
     * Catálogo de integrações disponíveis no FARPOP.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function catalogo(): array
    {
        $pec = PecService::status();

        return [
            [
                'slug' => 'pec',
                'label' => 'PEC / e-SUS',
                'descricao' => 'Busca de cidadão por CPF na base PostgreSQL do e-SUS.',
                'status' => $pec['ok'] ? 'ativo' : 'indisponivel',
                'status_label' => $pec['ok'] ? 'Configurado' : 'Indisponível',
                'detalhe' => $pec['motivo'],
                'icone' => 'fa-hospital-user',
                'url' => route('configuracoes.index') . '#pec',
                'url_label' => 'Configurações PEC',
            ],
            [
                'slug' => 'horus',
                'label' => 'Hórus / SUS (legado)',
                'descricao' => 'Exportação CSV de movimentações para envio manual. Integração em descontinuação pelo SUS.',
                'status' => 'legado',
                'status_label' => 'Exportação manual',
                'detalhe' => 'Exporte por período e unidade; ajuste o CSV conforme exigência local.',
                'icone' => 'fa-file-export',
                'url' => route('integracoes.horus'),
                'url_label' => 'Exportar CSV',
            ],
            [
                'slug' => 'codigo_barras',
                'label' => 'Código de barras',
                'descricao' => 'Leitura por scanner nas telas operacionais e etiquetas CODE128 nos lotes.',
                'status' => 'ativo',
                'status_label' => 'Ativo',
                'detalhe' => 'Suporte a REMUME, EAN e CODE128 em entradas, dispensas e transferências.',
                'icone' => 'fa-barcode',
                'url' => route('lotes.index'),
                'url_label' => 'Ver lotes e etiquetas',
            ],
        ];
    }
}

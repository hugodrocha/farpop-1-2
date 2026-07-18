<?php

return [
    'brasao_url' => 'https://prefeitura.mesquita.rj.gov.br/wp-content/uploads/2025/11/Brasao-Cidade-de-Mesquita-Colorido-1.png',

    'pec' => [
        'habilitado' => env('PEC_HABILITADO', false),
        'host' => env('PEC_HOST', '127.0.0.1'),
        'port' => env('PEC_PORT', 5433),
        'database' => env('PEC_DB', 'esus'),
        'username' => env('PEC_USER', 'postgres'),
        'password' => env('PEC_PASSWORD', ''),
        'schema' => env('PEC_SCHEMA', 'public'),
        'timeout' => env('PEC_TIMEOUT', 3),
    ],
];

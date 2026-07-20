<?php

/**
 * FARPOP — configuração modular do sistema de gestão farmacêutica municipal.
 *
 * Cada módulo declara perfis autorizados e status de implementação:
 * - ativo: funcionalidade disponível
 * - parcial: parcialmente implementado
 * - planejado: previsto no roadmap
 */
return [
    'versao' => '1.2',

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

    /*
    |--------------------------------------------------------------------------
    | Perfis de acesso (alinhados à gestão farmacêutica municipal)
    |--------------------------------------------------------------------------
    */
    'perfis' => [
        'superuser' => [
            'label' => 'Super Usuário',
            'descricao' => 'Acesso total ao sistema, auditoria crítica e configurações.',
        ],
        'admin' => [
            'label' => 'Administrador',
            'descricao' => 'Gerencia usuários, unidades, estoque e configurações.',
        ],
        'gestor' => [
            'label' => 'Gestor',
            'descricao' => 'Cadastros, usuários limitados e operações de estoque.',
        ],
        'farmaceutico' => [
            'label' => 'Farmacêutico',
            'descricao' => 'Dispensas, transferências, controle de estoque e usuários da unidade.',
        ],
        'tecnico' => [
            'label' => 'Auxiliar de Farmácia',
            'descricao' => 'Registra entradas, saídas e auxilia dispensas, sem configurações administrativas.',
        ],
        'consulta' => [
            'label' => 'Consulta',
            'descricao' => 'Consulta de disponibilidade (Aqui Tem) e chat, se habilitado.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Módulos do sistema
    |--------------------------------------------------------------------------
    */
    'modulos' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'icone' => 'fa-home',
            'status' => 'ativo',
            'perfis' => ['superuser', 'admin', 'gestor', 'farmaceutico', 'tecnico'],
        ],
        'cadastros' => [
            'label' => 'Cadastros',
            'icone' => 'fa-database',
            'status' => 'ativo',
            'perfis' => ['superuser', 'admin', 'gestor'],
            'submodulos' => [
                'usuarios' => ['label' => 'Usuários', 'status' => 'ativo', 'perfis' => ['superuser', 'admin', 'gestor', 'farmaceutico']],
                'unidades' => ['label' => 'Unidades de saúde', 'status' => 'ativo', 'perfis' => ['superuser', 'admin', 'gestor']],
                'pacientes' => ['label' => 'Pacientes', 'status' => 'ativo', 'perfis' => ['superuser', 'admin', 'gestor', 'farmaceutico', 'tecnico']],
                'medicamentos' => ['label' => 'Medicamentos', 'status' => 'ativo', 'perfis' => ['superuser', 'admin', 'gestor']],
                'fornecedores' => ['label' => 'Fornecedores', 'status' => 'ativo', 'perfis' => ['superuser', 'admin', 'gestor']],
                'fabricantes' => ['label' => 'Fabricantes', 'status' => 'ativo', 'perfis' => ['superuser', 'admin', 'gestor']],
            ],
        ],
        'estoque' => [
            'label' => 'Controle de estoque',
            'icone' => 'fa-boxes',
            'status' => 'ativo',
            'perfis' => ['superuser', 'admin', 'gestor', 'farmaceutico', 'tecnico'],
            'submodulos' => [
                'consulta' => ['label' => 'Consultar estoque', 'status' => 'ativo'],
                'entradas' => ['label' => 'Entradas', 'status' => 'ativo'],
                'lotes' => ['label' => 'Controle por lote (PEPS/FIFO)', 'status' => 'ativo'],
                'ajustes' => ['label' => 'Ajustes e perdas', 'status' => 'ativo'],
                'inventario' => ['label' => 'Inventário', 'status' => 'ativo'],
            ],
        ],
        'dispensa' => [
            'label' => 'Dispensa de medicamentos',
            'icone' => 'fa-pills',
            'status' => 'ativo',
            'perfis' => ['superuser', 'admin', 'gestor', 'farmaceutico', 'tecnico'],
        ],
        'transferencias' => [
            'label' => 'Transferências',
            'icone' => 'fa-truck',
            'status' => 'ativo',
            'perfis' => ['superuser', 'admin', 'gestor', 'farmaceutico', 'tecnico'],
            'submodulos' => [
                'envio' => ['label' => 'Envio (matriz → unidade)', 'status' => 'ativo'],
                'recebimento' => ['label' => 'Recebimento', 'status' => 'ativo'],
                'transito' => ['label' => 'Em transporte', 'status' => 'ativo'],
                'guia' => ['label' => 'Guia de transferência', 'status' => 'ativo'],
                'solicitacao' => ['label' => 'Solicitação e aprovação', 'status' => 'ativo'],
                'entre_unidades' => ['label' => 'Transferência entre unidades', 'status' => 'ativo'],
            ],
        ],
        'alertas' => [
            'label' => 'Alertas',
            'icone' => 'fa-bell',
            'status' => 'ativo',
            'perfis' => ['superuser', 'admin', 'gestor', 'farmaceutico', 'tecnico'],
            'submodulos' => [
                'estoque_baixo' => ['label' => 'Estoque baixo/crítico', 'status' => 'ativo'],
                'avisos_barra' => ['label' => 'Barra de avisos', 'status' => 'ativo'],
                'vencimento' => ['label' => 'Medicamentos vencendo/vencidos', 'status' => 'ativo'],
                'pendencias' => ['label' => 'Solicitações e transferências pendentes', 'status' => 'ativo'],
                'ruptura' => ['label' => 'Ruptura e previsão de reposição', 'status' => 'ativo'],
                'pedido_reposicao' => ['label' => 'Pedido de reposição', 'status' => 'ativo'],
                'pedidos_compra' => ['label' => 'Pedidos de compra', 'status' => 'ativo'],
            ],
        ],
        'relatorios' => [
            'label' => 'Relatórios',
            'icone' => 'fa-chart-bar',
            'status' => 'ativo',
            'perfis' => ['superuser', 'admin', 'gestor', 'farmaceutico'],
        ],
        'auditoria' => [
            'label' => 'Auditoria e segurança',
            'icone' => 'fa-shield-alt',
            'status' => 'ativo',
            'perfis' => ['superuser', 'admin'],
        ],
        'configuracoes' => [
            'label' => 'Configurações',
            'icone' => 'fa-cog',
            'status' => 'ativo',
            'perfis' => ['superuser', 'admin'],
        ],
        'aqui_tem' => [
            'label' => 'Aqui Tem',
            'icone' => 'fa-map-marker-alt',
            'status' => 'ativo',
            'perfis' => ['consulta', 'superuser', 'admin', 'farmaceutico', 'tecnico'],
        ],
        'chat' => [
            'label' => 'Chat interno',
            'icone' => 'fa-comments',
            'status' => 'ativo',
            'perfis' => ['superuser', 'admin', 'gestor', 'farmaceutico', 'tecnico', 'consulta'],
        ],
        'integracoes' => [
            'label' => 'Integrações',
            'icone' => 'fa-plug',
            'status' => 'ativo',
            'perfis' => ['superuser', 'admin'],
            'submodulos' => [
                'pec' => ['label' => 'PEC / e-SUS (busca cidadão)', 'status' => 'ativo'],
                'horus' => ['label' => 'Hórus / SUS', 'status' => 'ativo'],
                'codigo_barras' => ['label' => 'Leitura de código de barras', 'status' => 'ativo'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tipos de movimentação de estoque (histórico unificado — roadmap)
    |--------------------------------------------------------------------------
    */
    'movimentacoes' => [
        'entrada_compra' => 'Entrada por compra',
        'entrada_doacao' => 'Entrada por doação',
        'transferencia_matriz_unidade' => 'Transferência Matriz → Unidade',
        'transferencia_unidade_matriz' => 'Transferência Unidade → Matriz',
        'transferencia_entre_unidades' => 'Transferência entre unidades',
        'dispensa_paciente' => 'Dispensa ao paciente',
        'ajuste_estoque' => 'Ajuste de estoque',
        'perda' => 'Perda',
        'vencimento' => 'Vencimento',
        'devolucao' => 'Devolução',
    ],

    /*
    |--------------------------------------------------------------------------
    | Roadmap de evolução (fases sugeridas)
    |--------------------------------------------------------------------------
    */
    'roadmap' => [
        'fase_1' => [
            'titulo' => 'Fundação operacional',
            'itens' => [
                'Cadastros básicos (usuários, unidades, medicamentos)',
                'Entradas, dispensas e estoque agregado',
                'Transferência matriz → unidade com guia e recebimento',
                'Dashboard, auditoria e chat',
            ],
            'status' => 'concluido',
        ],
        'fase_2' => [
            'titulo' => 'Cadastros e visibilidade',
            'itens' => [
                'Cadastro de pacientes com histórico de dispensa',
                'Hub de relatórios (PDF/Excel)',
                'Alertas de vencimento',
                'Campos CNES/CNPJ em unidades',
            ],
            'status' => 'concluido',
        ],
        'fase_3' => [
            'titulo' => 'Controle por lote (PEPS/FIFO)',
            'itens' => [
                'Tabela de lotes com fabricante e validade',
                'Baixa automática pelo lote mais antigo',
                'Inventário e ajustes tipados',
                'Etiquetas e código de barras avançado',
            ],
            'status' => 'concluido',
        ],
        'fase_4' => [
            'titulo' => 'Workflow e integrações',
            'itens' => [
                'Solicitação → aprovação → separação → envio → recebimento',
                'Fornecedores e fabricantes',
                'Reserva de medicamentos por paciente',
                'Integração Hórus / exportação SUS',
                'Backup automático',
            ],
            'status' => 'concluido',
        ],
        'fase_5' => [
            'titulo' => 'Rede de unidades',
            'itens' => [
                'Transferência direta entre unidades de saúde',
                'Devolução de medicamentos unidade → matriz',
                'Tipo de movimentação e cancelamento por unidade de origem',
                'Histórico com identificação do tipo de transferência',
            ],
            'status' => 'concluido',
        ],
        'fase_6' => [
            'titulo' => 'Relatórios e etiquetas',
            'itens' => [
                'Relatório de consumo anual (CSV e impressão)',
                'Etiquetas de lote com CODE128 real',
            ],
            'status' => 'concluido',
        ],
        'fase_7' => [
            'titulo' => 'Governança operacional',
            'itens' => [
                'Central de alertas (estoque, validade, pendências)',
                'Relatório de inventário por lote (CSV e impressão)',
                'Fabricantes sugeridos nas entradas',
                'Módulos cadastros, estoque, transferências e alertas ativos',
            ],
            'status' => 'concluido',
        ],
        'fase_8' => [
            'titulo' => 'Dashboard e integrações',
            'itens' => [
                'Hub de integrações (PEC, Hórus legado, código de barras)',
                'Dashboard com KPIs ligados à central de alertas',
                'Inventário com filtro por unidade e exportação de planilha',
            ],
            'status' => 'concluido',
        ],
        'fase_9' => [
            'titulo' => 'Análise e inventário em lote',
            'itens' => [
                'Relatório curva ABC de consumo (CSV e impressão)',
                'Importação de inventário via CSV com ID de lote',
                'Planilha de inventário com coluna ID Lote para reimportação',
            ],
            'status' => 'concluido',
        ],
        'fase_10' => [
            'titulo' => 'Ruptura, reposição e e-mail',
            'itens' => [
                'Previsão de reposição com base no consumo médio',
                'Alertas de ruptura e quantidade sugerida',
                'Relatório de reposição (CSV e impressão)',
                'E-mail diário de alertas (comando agendado)',
            ],
            'status' => 'concluido',
        ],
        'fase_11' => [
            'titulo' => 'Pedido de reposição',
            'itens' => [
                'Lista de compras a partir da previsão de consumo',
                'Exportação CSV e impressão com assinaturas',
                'Agrupamento por medicamento na visão central',
            ],
            'status' => 'concluido',
        ],
        'fase_12' => [
            'titulo' => 'Consolidação final',
            'itens' => [
                'Painel de indicadores gerenciais mensais',
                'Comparativo de 6 meses e exportação CSV',
                'Correção da solicitação com bipagem e autocomplete',
                'Roadmap FARPOP 1.1 concluído (12 fases)',
            ],
            'status' => 'concluido',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Roadmap FARPOP 1.2
    |--------------------------------------------------------------------------
    */
    'roadmap_1_2' => [
        'fase_1' => [
            'titulo' => 'Pedidos de compra registrados',
            'itens' => [
                'Registro persistente a partir da lista de reposição',
                'Workflow rascunho → enviado → atendido',
                'Histórico, impressão e exportação CSV por pedido',
            ],
            'status' => 'concluido',
        ],
        'fase_2' => [
            'titulo' => 'Ficha farmacêutica aprimorada',
            'itens' => [
                'KPIs e medicamentos recentes na ficha do paciente',
                'Alerta de dispensa duplicada na tela de saída',
                'Exportação CSV do histórico de dispensas',
            ],
            'status' => 'planejado',
        ],
        'fase_3' => [
            'titulo' => 'Entrada vinculada ao pedido',
            'itens' => [
                'Marcar itens do pedido como recebidos na entrada',
                'Rastreio pedido → nota de entrada',
            ],
            'status' => 'planejado',
        ],
        'fase_4' => [
            'titulo' => 'Notificações in-app',
            'itens' => [
                'Central de notificações operacionais',
                'Alertas de pedidos pendentes e rupturas críticas',
            ],
            'status' => 'planejado',
        ],
        'fase_5' => [
            'titulo' => 'Relatórios por competência',
            'itens' => [
                'Consumo e dispensa por programa/competência REMUME',
                'Exportação para prestação de contas',
            ],
            'status' => 'planejado',
        ],
        'fase_6' => [
            'titulo' => 'API de consulta',
            'itens' => [
                'Endpoints REST para Aqui Tem e estoque por unidade',
                'Autenticação por token para integrações externas',
            ],
            'status' => 'planejado',
        ],
    ],
];

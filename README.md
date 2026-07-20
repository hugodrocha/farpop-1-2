# FARPOP 1.2

Sistema de **gestão farmacêutica municipal** da Prefeitura de Mesquita (Farmácia Popular), migrado do PHP legado (`htdocs/farpop`) para **Laravel 12**.

O FARPOP cobre cadastros, controle de estoque por lote (PEPS/FIFO), dispensa ao paciente, transferências (matriz, entre unidades e devolução), solicitações com aprovação, relatórios, alertas, chat interno, integrações (PEC/e-SUS, exportação Hórus) e backup do banco.

**Repositório oficial (1.2):** https://github.com/hugodrocha/farpop-1-2  
**Repositório congelado (1.1):** https://github.com/hugodrocha/farpop-1-1 — não recebe mais commits da linha 1.2.

---

## Stack

| Item | Tecnologia |
|------|------------|
| Backend | PHP 8.2+, Laravel 12 |
| Banco principal | MySQL (`farpop`) — schema legado |
| Frontend | Blade, Bootstrap 5, Font Awesome |
| Auth | Sessão (`usuario_id`, `perfil`, `clinica_id`) |
| Integração e-SUS | PostgreSQL (PEC), opcional |

---

## Roadmap (fases)

### FARPOP 1.1 — concluído (12 fases)

| Fase | Conteúdo | Status |
|------|----------|--------|
| **1** | Cadastros básicos, entradas/dispensas, transferências, dashboard, auditoria, chat | Concluída |
| **2** | Pacientes, relatórios (CSV/impressão), alertas de vencimento, CNES/CNPJ em unidades | Concluída |
| **3** | Lotes PEPS/FIFO, inventário, ajustes, movimentações, etiquetas | Concluída |
| **4** | Solicitação → aprovação → separação → envio, fornecedores/fabricantes, reservas, Hórus, backup | Concluída |
| **5** | Transferência entre unidades, devolução unidade → matriz, tipos de movimentação | Concluída |
| **6** | Consumo anual (CSV/impressão), etiquetas CODE128 | Concluída |
| **7** | Central de alertas, inventário por lote, fabricantes nas entradas | Concluída |
| **8** | Hub de integrações, dashboard com alertas, inventário aprimorado | Concluída |
| **9** | Curva ABC de consumo, importação de inventário via CSV | Concluída |
| **10** | Ruptura/reposição, previsão de consumo, e-mail diário de alertas | Concluída |
| **11** | Pedido de reposição (lista de compras) | Concluída |
| **12** | Indicadores gerenciais mensais, consolidação final | Concluída |

### FARPOP 1.2 — em andamento

| Fase | Conteúdo | Status |
|------|----------|--------|
| **1** | Pedidos de compra registrados (workflow rascunho → enviado → atendido) | Concluída |
| **2** | Ficha farmacêutica aprimorada + alertas na dispensa | Planejada |
| **3** | Entrada vinculada ao pedido de compra | Planejada |
| **4** | Notificações in-app | Planejada |
| **5** | Relatórios por competência/programa | Planejada |
| **6** | API REST de consulta | Planejada |

Detalhes dos módulos e perfis: `config/farpop.php`.

---

## Módulos implementados

### Operação diária

- **Dashboard** — visão global (admin/central) ou por unidade; KPIs (estoque, alertas, transferências, solicitações, validade)
- **Estoque** — consulta com semáforo verde/amarelo/vermelho
- **Entradas** — compra, doação ou devolução; lote/validade/fabricante; vínculo com fornecedor cadastrado
- **Dispensa (saídas)** — múltiplos itens, bloqueio de duplicidade no dia, comprovante, baixa PEPS
- **Reservas** — medicamento reservado por paciente; consumo automático na dispensa
- **Lotes (PEPS/FIFO)** — saldo por lote, etiqueta CODE128, histórico de movimentações
- **Inventário e ajustes** — contagem física e ajustes tipados (perda, vencimento etc.)
- **Aqui Tem** — busca de disponibilidade por unidade

### Transferências

- **Envio direto** (central → unidade) — montagem por código de barras, guia 26 dígitos
- **Entre unidades** — envio direto entre unidades de saúde (PEPS na origem)
- **Devolução à matriz** — unidade devolve medicamentos para a central
- **Recebimento** — confirmação na unidade destino (qualquer tipo)
- **Em trânsito** — acompanhamento
- **Solicitações** — unidade solicita → central aprova/recusa → separação → gera transferência

### Cadastros

- Unidades de saúde (CNES, CNPJ)
- Medicamentos + importação REMUME (CSV)
- Pacientes (histórico de dispensas)
- Fornecedores e fabricantes
- Usuários (perfis hierárquicos)

### Gestão e integrações

- **Relatórios** — hub com exportação CSV e impressão (estoque, validade, dispensas, transferências, consumo mensal/anual, inventário por lote, curva ABC)
- **Alertas** — central operacional (estoque baixo/crítico, validade, pendências, ruptura/reposição), barra de avisos, e-mail diário opcional
- **Chat interno** — widget flutuante, contatos, grupos, online/offline, auditoria de chat
- **PEC/e-SUS** — busca de cidadão por CPF (PostgreSQL)
- **Integrações** — hub PEC/e-SUS, exportação Hórus legada e código de barras
- **Auditoria** — log de ações + ações críticas
- **Configurações** — alertas, chat, teste PEC, backup manual
- **Backup automático** — comando Artisan + agendamento diário

---

## Perfis de acesso

| Perfil | Descrição resumida |
|--------|-------------------|
| `superuser` | Acesso total |
| `admin` | Administração municipal |
| `gestor` | Cadastros e operações |
| `farmaceutico` | Dispensa, estoque da unidade, usuários locais |
| `tecnico` | Auxiliar de farmácia (entradas/saídas) |
| `consulta` | Aqui Tem (+ chat, se habilitado) |

Controle centralizado em `ModuloService` e middleware `farpop.perfil`.

---

## Como executar

**Importante:** FARPOP e TELREG 2.0 usam portas diferentes na mesma máquina.

| Sistema | URL | Porta |
|---------|-----|-------|
| **FARPOP 1.1** | http://127.0.0.1:8001/login | **8001** |
| TELREG 2.0 | http://127.0.0.1:8000/login | 8000 |

### Instalação

```powershell
cd C:\laravel\farpop-1-1
composer install
copy .env.example .env
php artisan key:generate
```

Configure o `.env` (MySQL — ver seção abaixo).

### Subir o servidor

```powershell
php artisan serve --host=127.0.0.1 --port=8001
```

Ou dê duplo clique em **`serve-farpop.bat`**.

**Credenciais padrão:** `super.admin` / `farpop2026`

---

## Banco de dados

O sistema usa o banco MySQL **`farpop`** existente (schema do `instalar.php` legado). **Não há migrations Laravel** para as tabelas de negócio.

Novas estruturas (lotes, solicitações, reservas, fornecedores etc.) são criadas automaticamente na primeira execução pelos *schema services*:

- `LoteSchemaService` — lotes e movimentações
- `ClinicaSchemaService` — CNES/CNPJ
- `Fase4SchemaService` — solicitações, reservas, fornecedores, fabricantes
- `ChatSchemaService` — conversas e mensagens

### `.env` — MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=farpop
DB_USERNAME=root
DB_PASSWORD=
```

### `.env` — PEC/e-SUS (opcional)

```env
PEC_HABILITADO=false
PEC_HOST=127.0.0.1
PEC_PORT=5433
PEC_DB=esus
PEC_USER=postgres
PEC_PASSWORD=
PEC_SCHEMA=public
PEC_TIMEOUT=3
```

### `.env` — Backup (opcional)

Se `mysqldump` não estiver no PATH:

```env
FARPOP_MYSQLDUMP_PATH=C:\xampp\mysql\bin\mysqldump.exe
```

---

## Backup do banco

### Manual

```powershell
php artisan farpop:backup-banco
```

Ou em **Configurações → Backup do banco** (admin).

Arquivos gerados em: `storage/app/backups/farpop/`

### Automático

Agendado diariamente às **02:00** em `bootstrap/app.php`. No servidor, configure o cron / Agendador de Tarefas:

```powershell
# A cada minuto (Windows Task Scheduler ou cron Linux)
php C:\laravel\farpop-1-1\artisan schedule:run
```

Reservas vencidas são expiradas automaticamente às **01:00**.
Alertas por e-mail são enviados às **07:00** (se habilitado em Configurações).

---

## Comandos Artisan úteis

| Comando | Descrição |
|---------|-----------|
| `php artisan farpop:backup-banco` | Gera dump SQL do MySQL |
| `php artisan farpop:backup-banco --keep=30` | Mantém backups por 30 dias |
| `php artisan farpop:alertas-email` | Envia resumo diário de alertas por e-mail |
| `php artisan schedule:run` | Executa tarefas agendadas (backup, reservas, alertas) |

---

## Rotas principais

| Área | URL |
|------|-----|
| Login | `/login` |
| Dashboard | `/dashboard` |
| Estoque | `/estoque` |
| Entradas | `/entradas` |
| Dispensa | `/saidas` |
| Reservas | `/reservas` |
| Lotes | `/lotes` |
| Inventário | `/inventario` |
| Ajustes | `/ajustes/novo` |
| Movimentações | `/movimentacoes` |
| Transferências | `/transferencias` |
| Entre unidades | `/transferencias/entre-unidades` |
| Solicitações | `/solicitacoes` |
| Pacientes | `/pacientes` |
| Alertas (central) | `/alertas` |
| Alertas estoque | `/alertas/estoque` |
| Alertas pendências | `/alertas/pendencias` |
| Alertas ruptura | `/alertas/ruptura` |
| Pedido de reposição | `/pedidos-reposicao` |
| Alertas validade | `/alertas/vencimento` |
| Relatórios | `/relatorios` |
| Indicadores | `/indicadores` |
| Fornecedores | `/fornecedores` |
| Fabricantes | `/fabricantes` |
| Integrações | `/integracoes` |
| Hórus / SUS | `/integracoes/horus` |
| Configurações | `/configuracoes` |
| Auditoria | `/auditoria` |
| Chat API | `/chat/api` |

Lista completa: `php artisan route:list`

---

## Interface

- Bootstrap 5 + tema customizado (verde/teal/cyan)
- Sidebar colapsável (padrão TELREG 2.0)
- Brasão da Prefeitura como identidade visual
- Barra inferior de avisos + widget de chat arrastável

---

## Estrutura do projeto

```
app/
├── Console/Commands/     # BackupDatabaseCommand
├── Http/
│   ├── Controllers/      # Telas e rotas web
│   └── Middleware/       # FarpopAuth, PerfilAutorizado
└── Services/             # Regras de negócio
    ├── ModuloService.php
    ├── LoteService.php
    ├── SolicitacaoTransferenciaService.php
    ├── ReservaService.php
    ├── HorusExportService.php
    └── ...

config/farpop.php         # Módulos, perfis, roadmap
resources/views/          # Blade
routes/web.php            # Rotas
bootstrap/app.php         # Middleware + schedule
```

---

## Fluxo: transferência entre unidades

1. Farmacêutico/auxiliar da **unidade origem** acessa **Transferências → Entre unidades**
2. Escolhe **Entre unidades** ou **Devolução à matriz**
3. Escaneia medicamentos (estoque validado na origem) e envia
4. **Unidade destino** recebe em `/transferencias/receber` (mesmo fluxo da guia matriz)
5. Movimentação registrada como `transferencia_entre_unidades` ou `transferencia_unidade_matriz`

---

## Fluxo: solicitação de transferência

1. **Unidade** cria solicitação em `/solicitacoes/nova`
2. **Central** abre a solicitação → aprova quantidades ou recusa
3. Central inicia **separação** (opcional) e clica **Enviar transferência**
4. Sistema gera a transferência (PEPS) e guia de nota
5. **Unidade destino** recebe em `/transferencias/receber`

---

## Desenvolvimento

- Regras de negócio ficam em **Services**, não nos controllers
- Permissões: `ModuloService::podeAcessar()` / `podeAcessarSubmodulo()`
- Alterações de schema legado: criar ou estender um `*SchemaService::garantir()`

---

## Licença

Uso interno — Prefeitura Municipal de Mesquita / Farmácia Popular.

# FARPOP 1.1

Sistema de gestão farmacêutica da Prefeitura de Mesquita, migrado do PHP legado (`htdocs/farpop`) para Laravel 12.

## Módulos implementados

- **Autenticação** — login, bloqueio por tentativas, perfis de acesso
- **Dashboard** — visão global (admin/central) e visão por unidade, KPIs, gráficos SVG, alertas de estoque
- **Estoque** — consulta com semáforo verde/amarelo/vermelho
- **Entradas / Saídas** — movimentação com auditoria; saídas com múltiplos itens, bloqueio de duplicidade no dia, comprovante
- **Transferências** — lote com código de barras, nota 26 dígitos, recebimento, trânsito, cancelamento
- **Aqui Tem** — busca de medicamentos disponíveis por unidade
- **Cadastros** — clínicas, medicamentos, importação REMUME (CSV)
- **Usuários** — CRUD com perfis hierárquicos
- **Chat** — canal geral + mensagens privadas (widget flutuante)
- **Barra de avisos** — marquee no rodapé
- **Configurações** — alertas de estoque, chat, diagnóstico PEC/e-SUS
- **Auditoria** — log de ações + ações críticas (zerar estoque, superuser)
- **Trocar senha**

## Interface

- Bootstrap 5 + tema customizado com **degradês verde/teal/cyan**
- Sidebar colapsável (padrão TELREG 2.0)
- **Brasão da Prefeitura** como marca d'água no conteúdo
- Barra inferior de avisos + botão flutuante de chat

## Banco de dados

O sistema usa o banco MySQL **`farpop`** existente (mesmo schema do `instalar.php` legado). Configure no `.env`:

```env
DB_DATABASE=farpop
DB_USERNAME=root
DB_PASSWORD=
```

Opcional — integração PEC/e-SUS (PostgreSQL):

```env
PEC_HABILITADO=false
PEC_HOST=127.0.0.1
PEC_PORT=5433
PEC_DB=esus
PEC_USER=postgres
PEC_PASSWORD=
PEC_SCHEMA=public
```

## Como executar

**Importante:** o TELREG 2.0 e o FARPOP rodam em portas diferentes para não conflitar.

| Sistema | URL | Porta |
|---------|-----|-------|
| **FARPOP 1.1** | http://127.0.0.1:8001/login | 8001 |
| TELREG 2.0 | http://127.0.0.1:8000/login | 8000 |

Se abrir `http://127.0.0.1:8000/login`, verá o **TELREG** — isso é normal quando os dois projetos estão na mesma máquina.

### FARPOP (porta 8001)

```powershell
cd C:\laravel\farpop-1-1
composer install
php artisan serve --host=127.0.0.1 --port=8001
```

Ou dê duplo clique em **`serve-farpop.bat`** na pasta do projeto.

Acesse: **http://127.0.0.1:8001/login**

**Credenciais padrão:** `super.admin` / `farpop2026`

## Estrutura

- `app/Services/` — regras de negócio (estoque, transferências, saídas, PEC, chat…)
- `app/Http/Middleware/` — autenticação e controle de perfil
- `resources/views/` — telas Blade
- `config/farpop.php` — configurações do sistema

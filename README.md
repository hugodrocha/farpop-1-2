# 💊 FARPOP

🏥 Sistema de gestão farmacêutica municipal da Prefeitura de Mesquita, desenvolvido com **Laravel 12**. O FARPOP ajuda a organizar unidades de saúde, medicamentos, estoques, dispensas, transferências e relatórios.

✨ Esta documentação descreve exclusivamente o FARPOP. A aplicação e o MariaDB podem ser executados em contêineres Docker, sem instalar PHP, Composer ou um servidor de banco de dados na máquina.

## 🚀 Recursos

- 📊 Painel operacional e indicadores gerenciais.
- 🏥 Cadastro de unidades, medicamentos, pacientes e usuários.
- 📦 Estoque por unidade, controle por lote e inventário.
- 💊 Entradas, dispensas, reservas e ajustes de estoque.
- 🔄 Transferências entre unidades e solicitações de reposição.
- 📈 Relatórios, alertas, auditoria e exportação para Hórus.
- 💬 Chat interno e avisos para as unidades.
- 🛒 Pedidos de compra.
- 🔗 Integração opcional com PEC/e-SUS.
- 💾 Rotinas de backup do banco de dados.

## 🐳 Executar com Docker

### ✅ Requisitos

- Docker Engine com o plugin `docker compose`, ou Docker Desktop.
- O arquivo SQL do FARPOP, usado para inicializar o banco na primeira execução.

### ⚙️ Preparar o ambiente

No terminal, a partir da pasta do projeto:

```bash
cp .env.example .env
```

O arquivo `.env.example` aponta, por padrão, para `../../data_sql/farpop.sql`, caminho correspondente à pasta `data_sql` localizada dois níveis acima do projeto. Se o dump estiver em outro local, ajuste `FARPOP_SQL_FILE` no `.env` para o caminho correto, de preferência absoluto.

Gere uma chave para a aplicação:

```bash
docker compose run --rm --no-deps app php artisan key:generate --show
```

Copie o valor exibido para `APP_KEY` no `.env`. Antes de disponibilizar o sistema para outras pessoas, troque também `FARPOP_DB_PASSWORD` e `MARIADB_ROOT_PASSWORD` por senhas fortes e exclusivas.

### ▶️ Iniciar

```bash
docker compose up -d --build
```

O Docker inicia o MariaDB, importa o dump quando o banco ainda está vazio, aplica as migrações pendentes e inicia a aplicação.

🌐 Acesse: **http://127.0.0.1:8001/login**

🔐 Entre com uma conta presente no banco importado. Contas criadas fora do arquivo SQL — por exemplo, diretamente em outro MariaDB — não são copiadas para o banco isolado do Docker.

### 🧰 Comandos úteis

```bash
# Ver estado dos contêineres
docker compose ps

# Acompanhar os logs da aplicação e do banco
docker compose logs -f app database

# Parar os contêineres sem apagar os dados
docker compose down

# Executar testes automatizados
docker compose exec app php artisan test

# Consultar as migrações
docker compose exec app php artisan migrate:status

# Abrir um terminal MariaDB
docker compose exec database mariadb -u farpop -p farpop

# Criar um backup SQL manual
docker compose exec -T database sh -c 'mariadb-dump -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"' > farpop-backup.sql
```

🛡️ Os dados do banco ficam em um volume Docker persistente, inclusive quando os contêineres são parados ou recriados.

⚠️ **Atenção:** `docker compose down -v` também remove o volume com o banco e apaga os dados armazenados nele. Faça um backup antes de usar esse comando.

## 🗄️ Banco de dados

- 🐬 O MariaDB do Docker é independente de qualquer servidor de banco instalado na máquina.
- 📥 O dump SQL é importado automaticamente **somente na primeira inicialização** do volume de dados.
- 🔁 Alterar o arquivo SQL depois da inicialização não reimporta nem substitui os dados existentes.
- 🧱 As migrações Laravel criam as estruturas da versão atual que ainda não existirem.
- 🔒 Nunca publique o `.env`, senhas ou dumps que contenham dados pessoais.

Para configurar outra porta web, defina `FARPOP_PORT` no `.env`. Para ajustar o banco, use `DB_DATABASE`, `FARPOP_DB_USERNAME` e `FARPOP_DB_PASSWORD`.

## 🧑‍⚕️ Perfis de acesso

| Perfil | Permissões |
|---|---|
| `superuser` | Acesso total |
| `admin` | Administração municipal |
| `gestor` | Cadastros e operações |
| `farmaceutico` | Dispensa, estoque e usuários da unidade |
| `tecnico` | Apoio às operações da farmácia |
| `consulta` | Consulta de disponibilidade |

## 🧭 Rotas principais

| Área | Rota |
|---|---|
| 🔐 Login | `/login` |
| 📊 Painel | `/dashboard` |
| 📦 Estoque | `/estoque` |
| 📥 Entradas | `/entradas` |
| 💊 Dispensas | `/saidas` |
| 🧾 Lotes | `/lotes` |
| 🏥 Unidades | `/clinicas` |
| 🧑‍⚕️ Pacientes | `/pacientes` |
| 🔄 Transferências | `/transferencias` |
| 📋 Solicitações | `/solicitacoes` |
| 🚨 Alertas | `/alertas` |
| 📈 Relatórios | `/relatorios` |
| 🛒 Pedidos de compra | `/pedidos-compra` |
| ⚙️ Configurações | `/configuracoes` |

## 🔗 Integrações opcionais

### 🩺 PEC/e-SUS

A integração é opcional e desativada por padrão. Para configurá-la, defina `PEC_HABILITADO`, `PEC_HOST`, `PEC_PORT`, `PEC_DB`, `PEC_USER`, `PEC_PASSWORD` e `PEC_SCHEMA` no `.env`, com os dados do PostgreSQL autorizado para integração.

### ✉️ E-mail

Configure as variáveis `MAIL_*` no `.env` para habilitar o envio de mensagens. Sem essa configuração, o envio permanece no log da aplicação.

## 💾 Backup e manutenção

O comando de backup também pode ser executado no contêiner da aplicação:

```bash
docker compose exec app php artisan farpop:backup-banco
```

Os backups gerados ficam em `storage/app/backups/farpop/`. O diretório de arquivos da aplicação usa um volume persistente separado do banco.

📅 Para processar tarefas agendadas, configure um agendador externo para executar `php artisan schedule:run` periodicamente no contêiner `app`.

## 🧑‍💻 Desenvolvimento

```bash
# Instalar dependências PHP
composer install

# Instalar dependências JavaScript
npm install

# Gerar os arquivos de frontend
npm run build

# Executar os testes
php artisan test
```

🧩 As regras de negócio ficam principalmente em `app/Services`, as rotas web em `routes/web.php` e as telas Blade em `resources/views`.

## 📄 Licença

🔐 Uso interno — Prefeitura Municipal de Mesquita / Farmácia Popular.

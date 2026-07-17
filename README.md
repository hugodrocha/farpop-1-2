# FARPOP 1.1

Sistema web desenvolvido em Laravel para gestão farmacêutica, com foco em autenticação de usuários, painel inicial e módulos de cadastro e controle relacionados a clínicas, medicamentos e usuários.

## O que foi feito

### Funcionalidades implementadas
- Tela de login com autenticação baseada em usuário e senha.
- Controle de sessão para proteger páginas internas.
- Dashboard com resumo de dados principais do sistema.
- Módulos de navegação para clínicas, medicamentos e usuários.
- Layout compartilhado para padronizar a interface das páginas.
- Estrutura inicial de controllers para gestão de estoque, entradas, saídas, transferências e auditoria.

### Páginas principais
- Login: /
- Dashboard: /dashboard
- Clínicas: /clinicas
- Medicamentos: /medicamentos
- Usuários: /usuarios

## Tecnologias utilizadas
- PHP 8.2
- Laravel 12
- Blade templates
- Composer
- Vite / Node.js para assets frontend

## Como executar o projeto

1. Acesse a pasta do projeto:
   ```bash
   cd c:\laravel\farpop-1-1
   ```

2. Instale as dependências do PHP:
   ```bash
   composer install
   ```

3. Crie o arquivo de ambiente e gere a chave da aplicação:
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

4. Instale as dependências do frontend:
   ```bash
   npm install
   ```

5. Execute as migrações do banco de dados:
   ```bash
   php artisan migrate
   ```

6. Inicie o servidor local:
   ```bash
   php artisan serve
   ```

7. Acesse no navegador:
   ```text
   http://127.0.0.1:8000
   ```

## Acesso inicial

Credenciais informadas para acesso local:
- Usuário: super.admin
- Senha: farpop2026

## Estrutura do projeto

- app/Http/Controllers: controladores da aplicação
- resources/views: páginas Blade e layouts
- routes/web.php: rotas principais
- database/migrations: estrutura do banco
- public: arquivos públicos acessíveis pelo navegador

## Observações

Este projeto está em fase de evolução e possui a base inicial da aplicação pronta para expansão com novos módulos e regras de negócio.
usuário: super.admin
senha: farpop2026
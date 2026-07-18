<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FARPOP 1.1 — Acesso ao sistema</title>
    <link rel="icon" type="image/png" href="https://prefeitura.mesquita.rj.gov.br/wp-content/uploads/2025/11/Brasao-Cidade-de-Mesquita-Colorido-1.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --verde-escuro: #064e3b;
            --verde-medio: #0f766e;
            --verde-claro: #14b8a6;
            --brasao-url: url('https://prefeitura.mesquita.rj.gov.br/wp-content/uploads/2025/11/Brasao-Cidade-de-Mesquita-Colorido-1.png');
        }

        html, body {
            min-height: 100%;
            font-family: 'Inter', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body.login-body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
            background: linear-gradient(135deg, #022c22 0%, #064e3b 30%, #0f766e 55%, #14b8a6 78%, #0891b2 100%);
        }

        body.login-body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: var(--brasao-url) center/ min(520px, 72vw) no-repeat;
            opacity: 0.07;
            pointer-events: none;
            z-index: 0;
        }

        body.login-body::after {
            content: '';
            position: fixed;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(45, 212, 191, 0.22) 0%, transparent 70%);
            top: -200px;
            right: -150px;
            pointer-events: none;
            z-index: 0;
            animation: brilho 8s ease-in-out infinite alternate;
        }

        @keyframes brilho {
            from { transform: translate(0, 0) scale(1); opacity: 0.6; }
            to   { transform: translate(-40px, 30px) scale(1.1); opacity: 1; }
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1080px;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 20px;
            align-items: stretch;
        }

        .login-apresentacao,
        .login-container {
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.28), 0 0 0 1px rgba(255, 255, 255, 0.06);
        }

        .login-apresentacao {
            background: linear-gradient(160deg, rgba(255,255,255,0.14) 0%, rgba(255,255,255,0.04) 100%);
            color: #fff;
            padding: 40px 36px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .login-apresentacao .brasao-topo {
            width: 108px;
            height: auto;
            margin-bottom: 24px;
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            padding: 12px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
        }

        .login-apresentacao h1 {
            font-size: clamp(2rem, 4vw, 2.75rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.1;
            margin-bottom: 12px;
        }

        .login-apresentacao h1 span {
            background: linear-gradient(90deg, #99f6e4, #ccfbf1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-apresentacao .subtitulo-painel {
            font-size: 1.05rem;
            font-weight: 600;
            color: #d1fae5;
            margin-bottom: 16px;
        }

        .login-apresentacao .descricao {
            font-size: 0.95rem;
            line-height: 1.75;
            color: #a7f3d0;
            margin-bottom: 8px;
            max-width: 420px;
        }

        .login-apresentacao .destaque {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 10px 18px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .login-container {
            background: #ffffff;
            padding: 40px 32px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.8);
            animation: entrada 0.5s ease-out;
        }

        @keyframes entrada {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-logo-mobile {
            display: none;
            text-align: center;
            margin-bottom: 16px;
        }

        .login-logo-mobile img {
            width: 84px;
            background: #fff;
            border-radius: 18px;
            padding: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .login-container h2 {
            font-size: clamp(1.6rem, 3vw, 2rem);
            font-weight: 800;
            color: #0f172a;
            text-align: center;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .login-container h2 span { color: var(--verde-medio); }

        .login-subtitulo {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            line-height: 1.55;
            margin-bottom: 24px;
        }

        .erro-login {
            background: linear-gradient(135deg, #fef2f2, #fff5f5);
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 14px;
            padding: 13px 16px;
            margin-bottom: 18px;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .login-form { display: flex; flex-direction: column; gap: 16px; }

        .login-campo { display: flex; flex-direction: column; gap: 7px; }

        .login-campo label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        .login-campo input {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            color: #0f172a;
            background: #f8fafc;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        .login-campo input::placeholder { color: #94a3b8; }

        .login-campo input:focus {
            outline: none;
            border-color: var(--verde-claro);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.12);
        }

        .login-senha-wrap { position: relative; }
        .login-senha-wrap input { padding-right: 48px; }

        .btn-toggle-senha {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            border-radius: 8px;
            transition: color 0.2s, background 0.2s;
        }

        .btn-toggle-senha:hover {
            color: var(--verde-medio);
            background: #ecfdf5;
        }

        .btn-entrar {
            width: 100%;
            margin-top: 6px;
            padding: 14px 20px;
            background: linear-gradient(135deg, #064e3b 0%, #0f766e 40%, #14b8a6 70%, #0891b2 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(15, 118, 110, 0.35);
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .btn-entrar:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 28px rgba(15, 118, 110, 0.42);
        }

        .login-rodape {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.7;
        }

        .login-rodape strong { color: #64748b; font-weight: 600; }

        .versao-badge {
            display: inline-block;
            margin-top: 6px;
            font-size: 11px;
            color: #cbd5e1;
        }

        @media (max-width: 900px) {
            .login-wrapper { grid-template-columns: 1fr; max-width: 440px; }
            .login-apresentacao { display: none; }
            .login-logo-mobile { display: block; }
            .login-container { padding: 32px 24px; }
        }
    </style>
</head>
<body class="login-body">
    <div class="login-wrapper">
        <div class="login-apresentacao">
            <img src="https://prefeitura.mesquita.rj.gov.br/wp-content/uploads/2025/11/Brasao-Cidade-de-Mesquita-Colorido-1.png" alt="Brasão da Prefeitura de Mesquita" class="brasao-topo">
            <h1>FAR<span>POP</span> 1.1</h1>
            <p class="subtitulo-painel">Sistema de Gestão Farmacêutica</p>
            <p class="descricao">
                Controle integrado de estoque, entradas, saídas para pacientes, transferências entre unidades
                e auditoria — desenvolvido para a Farmácia Popular de Mesquita.
            </p>
            <span class="destaque">
                <i class="fas fa-pills"></i>
                Prefeitura de Mesquita • Acesso ao sistema
            </span>
        </div>

        <div class="login-container">
            <div class="login-logo-mobile">
                <img src="https://prefeitura.mesquita.rj.gov.br/wp-content/uploads/2025/11/Brasao-Cidade-de-Mesquita-Colorido-1.png" alt="Brasão Mesquita">
            </div>

            <h2>FAR<span>POP</span> 1.1</h2>
            <p class="login-subtitulo">Entre com seu usuário e senha para acessar o sistema.</p>

            @if ($errors->any())
                <div class="erro-login">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="login-form" autocomplete="on">
                @csrf
                <div class="login-campo">
                    <label for="usuario">Usuário</label>
                    <input type="text" id="usuario" name="usuario" value="{{ old('usuario') }}" placeholder="Digite seu usuário" required autofocus>
                </div>

                <div class="login-campo">
                    <label for="senha">Senha</label>
                    <div class="login-senha-wrap">
                        <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required autocomplete="current-password">
                        <button type="button" class="btn-toggle-senha" onclick="togglePassword()" aria-label="Mostrar senha">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-entrar">Entrar no sistema</button>
            </form>

            <div class="login-rodape">
                <strong>Ambiente interno de uso institucional.</strong><br>
                Prefeitura Municipal de Mesquita.
                <div class="versao-badge">{{ date('Y') }} · FARPOP 1.1</div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('senha');
            const icon = document.getElementById('toggleIcon');
            const visivel = input.type === 'text';
            input.type = visivel ? 'password' : 'text';
            icon.className = visivel ? 'fas fa-eye' : 'fas fa-eye-slash';
        }
    </script>
</body>
</html>

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
            --verde: #0f766e;
            --verde-escuro: #064e3b;
            --verde-claro: #ecfdf5;
            --texto: #134e4a;
            --texto-suave: #2d6a5f;
            --borda: #b8e0d6;
        }

        html, body {
            min-height: 100%;
            font-family: 'Inter', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body.login-body {
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            background: #ffffff;
        }

        .login-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            background: url('{{ asset('images/login-bg.png') }}') center / cover no-repeat;
        }

        .login-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0.5) 0%,
                rgba(236, 253, 245, 0.28) 45%,
                rgba(255, 255, 255, 0) 70%
            );
        }

        .login-wave {
            display: none;
        }

        .login-page {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1140px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 48px;
            align-items: center;
        }

        /* —— Painel esquerdo —— */
        .login-brand {
            padding: 8px 12px;
        }

        .login-logo {
            width: auto;
            height: auto;
            margin-bottom: 28px;
            flex-shrink: 0;
        }

        .login-logo img {
            width: auto;
            height: 96px;
            max-width: 120px;
            display: block;
            object-fit: contain;
        }

        .login-brand h1 {
            font-size: clamp(2.4rem, 4.5vw, 3.2rem);
            font-weight: 800;
            color: var(--texto);
            letter-spacing: -0.03em;
            line-height: 1.05;
            margin-bottom: 10px;
        }

        .login-brand .subtitulo {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--texto);
            margin-bottom: 18px;
        }

        .login-brand .divisor {
            width: 52px;
            height: 4px;
            background: linear-gradient(90deg, var(--verde-escuro), var(--verde), #14b8a6);
            border-radius: 999px;
            margin-bottom: 22px;
        }

        .login-brand .descricao {
            font-size: 1rem;
            line-height: 1.75;
            color: var(--texto-suave);
            max-width: 460px;
            margin-bottom: 32px;
        }

        .login-features {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }

        .login-feature {
            flex: 1 1 140px;
            max-width: 180px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(15, 118, 110, 0.14);
            border-radius: 16px;
            padding: 18px 14px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(15, 118, 110, 0.08);
        }

        .login-feature i {
            font-size: 1.35rem;
            color: var(--verde);
            margin-bottom: 10px;
            display: block;
        }

        .login-feature span {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--texto);
            line-height: 1.35;
            display: block;
        }

        /* —— Card de login —— */
        .login-card {
            background: #fff;
            border-radius: 24px;
            padding: 40px 36px 32px;
            box-shadow: 0 20px 50px rgba(15, 118, 110, 0.12), 0 0 0 1px rgba(15, 118, 110, 0.08);
            animation: entrada 0.45s ease-out;
        }

        @keyframes entrada {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-card-header {
            margin-bottom: 28px;
        }

        .login-card-header h2 {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--texto);
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .login-card-header p {
            font-size: 0.95rem;
            color: var(--texto-suave);
        }

        .erro-login {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .input-icon-wrap {
            position: relative;
        }

        .input-icon-wrap > i.field-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--verde);
            opacity: 0.65;
            font-size: 0.95rem;
            pointer-events: none;
        }

        .input-icon-wrap input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            border: 1.5px solid var(--borda);
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            color: var(--texto);
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-icon-wrap input::placeholder { color: #8aab7a; }

        .input-icon-wrap input:focus {
            outline: none;
            border-color: var(--verde);
            box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.14);
        }

        .input-senha input { padding-right: 48px; }

        .btn-toggle-senha {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--verde);
            opacity: 0.55;
            cursor: pointer;
            padding: 6px;
            border-radius: 8px;
            transition: color 0.2s, opacity 0.2s;
        }

        .btn-toggle-senha:hover { color: var(--verde); opacity: 1; }

        .btn-entrar {
            width: 100%;
            margin-top: 8px;
            padding: 15px 20px;
            background: linear-gradient(135deg, #064e3b 0%, #0f766e 50%, #14b8a6 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 8px 22px rgba(15, 118, 110, 0.32);
            transition: background 0.2s, transform 0.15s, box-shadow 0.15s;
        }

        .btn-entrar:hover {
            background: linear-gradient(135deg, #022c22 0%, #0d9488 55%, #2dd4bf 100%);
            transform: translateY(-1px);
            box-shadow: 0 12px 28px rgba(15, 118, 110, 0.38);
        }

        .login-rodape {
            margin-top: 28px;
            padding-top: 22px;
            border-top: 1px solid var(--verde-claro);
            text-align: center;
        }

        .login-rodape .institucional {
            font-size: 13px;
            font-weight: 600;
            color: var(--texto-suave);
            margin-bottom: 6px;
        }

        .login-rodape .creditos {
            font-size: 11px;
            color: #5b8a7e;
        }

        /* —— Mobile —— */
        .login-brand-mobile {
            display: none;
            text-align: center;
            margin-bottom: 24px;
        }

        .login-brand-mobile .login-logo {
            margin: 0 auto 12px;
        }

        .login-brand-mobile .login-logo img {
            height: 80px;
            margin: 0 auto;
        }

        .login-brand-mobile h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--texto);
        }

        @media (max-width: 960px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                max-width: 440px;
                gap: 0;
            }

            .login-brand { display: none; }

            .login-brand-mobile { display: block; }

            .login-wave { display: none; }

            .login-card { padding: 32px 24px 28px; }
        }
    </style>
</head>
<body class="login-body">
    <div class="login-bg" aria-hidden="true"></div>

    <svg class="login-wave" viewBox="0 0 1920 620" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <defs>
            <linearGradient id="waveGrad" x1="100%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#0c6e58"/>
                <stop offset="55%" stop-color="#107c63"/>
                <stop offset="100%" stop-color="#0a5f4d"/>
            </linearGradient>
        </defs>
        <path d="M1920 20 C1760 55 1580 130 1360 220 C1080 350 720 440 320 510 C140 535 0 548 0 560 L0 620 L1920 620 Z" fill="url(#waveGrad)"/>
        <path d="M1920 75 C1780 110 1620 185 1420 275 C1140 400 780 485 360 552 C160 575 0 588 0 598 L0 620 L1920 620 Z" fill="#0a6b55" fill-opacity="0.28"/>
    </svg>

    <div class="login-page">
        <div class="login-wrapper">
            <div class="login-brand">
                <div class="login-logo">
                    <img src="{{ asset('images/brasao-mesquita.png') }}" alt="Prefeitura Municipal de Mesquita">
                </div>

                <h1>FARPOP 1.1</h1>
                <p class="subtitulo">Sistema de Gestão Farmacêutica</p>
                <div class="divisor"></div>
                <p class="descricao">
                    Gestão completa do estoque de medicamentos com segurança, agilidade e controle.
                </p>

                <div class="login-features">
                    <div class="login-feature">
                        <i class="fas fa-boxes-stacked"></i>
                        <span>Estoque Centralizado</span>
                    </div>
                    <div class="login-feature">
                        <i class="fas fa-right-left"></i>
                        <span>Entradas e Saídas</span>
                    </div>
                    <div class="login-feature">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Relatórios e Auditoria</span>
                    </div>
                </div>
            </div>

            <div class="login-card">
                <div class="login-brand-mobile">
                    <div class="login-logo">
                        <img src="{{ asset('images/brasao-mesquita.png') }}" alt="Prefeitura Municipal de Mesquita">
                    </div>
                    <h1>FARPOP 1.1</h1>
                </div>

                <div class="login-card-header">
                    <h2>Bem-vindo!</h2>
                    <p>Faça login para continuar.</p>
                </div>

                @if ($errors->any())
                    <div class="erro-login">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" class="login-form" autocomplete="on">
                    @csrf

                    <div class="input-icon-wrap">
                        <i class="fas fa-user field-icon"></i>
                        <input type="text" id="usuario" name="usuario" value="{{ old('usuario') }}" placeholder="Digite seu usuário" required autofocus>
                    </div>

                    <div class="input-icon-wrap input-senha">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required autocomplete="current-password">
                        <button type="button" class="btn-toggle-senha" onclick="togglePassword()" aria-label="Mostrar senha">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>

                    <button type="submit" class="btn-entrar">Entrar</button>
                </form>

                <div class="login-rodape">
                    <p class="institucional">Ambiente interno de uso institucional.</p>
                    <p class="creditos">Prefeitura Municipal de Mesquita &bull; {{ date('Y') }} &bull; FARPOP 1.1</p>
                </div>
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

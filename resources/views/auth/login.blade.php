<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FARPOP 1.1 — Login</title>
    <link rel="icon" type="image/png" href="https://prefeitura.mesquita.rj.gov.br/wp-content/uploads/2025/11/Brasao-Cidade-de-Mesquita-Colorido-1-286x300.png">
    <style>
        body{margin:0;font-family:Arial,sans-serif;background:linear-gradient(135deg,#064e3b,#0f766e);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
        .card{background:#fff;border-radius:20px;display:grid;grid-template-columns:1.1fr .9fr;box-shadow:0 20px 50px rgba(0,0,0,.18);overflow:hidden;max-width:1100px;width:100%;}
        .side{padding:32px;background:linear-gradient(180deg,rgba(255,255,255,.16),rgba(255,255,255,.06));color:#fff;display:flex;flex-direction:column;justify-content:center;}
        .side h1{font-size:40px;margin:0 0 14px;}
        .side p{font-size:16px;line-height:1.7;color:#d1fae5;}
        .form-side{padding:32px 28px;display:flex;flex-direction:column;justify-content:center;}
        .logo{width:90px;margin-bottom:18px;}
        form{display:flex;flex-direction:column;gap:12px;}
        input{padding:12px 14px;border:1px solid #d1d5db;border-radius:10px;font-size:15px;}
        button{padding:12px 16px;border:none;border-radius:10px;background:#0f766e;color:#fff;font-weight:700;cursor:pointer;}
        .alert{padding:12px;border-radius:10px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;margin-bottom:12px;font-size:14px;}
        @media (max-width: 800px){.card{grid-template-columns:1fr;}.side{display:none;}}
    </style>
</head>
<body>
<div class="card">
    <div class="side">
        <h1>FARPOP 1.1</h1>
        <p>Sistema de gestão farmacêutica integrado com controle de estoque, entradas, saídas, pacientes e transferências.</p>
    </div>
    <div class="form-side">
        <img class="logo" src="https://prefeitura.mesquita.rj.gov.br/wp-content/uploads/2025/11/Brasao-Cidade-de-Mesquita-Colorido-1-286x300.png" alt="brasão">
        <h2 style="margin:0 0 8px;color:#064e3b;">Acesso ao sistema</h2>
        <p style="margin:0 0 16px;color:#6b7280;">Informe suas credenciais para entrar.</p>
        @if($errors->any())
            <div class="alert">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <input type="text" name="usuario" placeholder="Usuário" required value="{{ old('usuario') }}">
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>
    </div>
</div>
</body>
</html>

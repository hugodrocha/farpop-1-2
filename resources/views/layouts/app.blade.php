<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FARPOP 1.1')</title>
    <style>
        body{margin:0;font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a;}
        .topo{background:#064e3b;color:#fff;padding:18px 24px;display:flex;justify-content:space-between;align-items:center;}
        .nav{background:#0f172a;padding:12px 24px;display:flex;flex-wrap:wrap;gap:10px;}
        .nav a{color:#fff;text-decoration:none;padding:8px 12px;border-radius:8px;background:#1e293b;}
        .nav a.active{background:#0f766e;}
        .card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px;box-shadow:0 8px 20px rgba(15,23,42,.05);}
        .wrap{padding:24px;}
        table{width:100%;border-collapse:collapse;background:#fff;}th,td{padding:12px;border-bottom:1px solid #e2e8f0;text-align:left;}a{color:#0f766e;text-decoration:none;} .btn{display:inline-block;padding:10px 14px;border-radius:10px;background:#0f766e;color:#fff;text-decoration:none;margin-right:8px;margin-bottom:8px;} .muted{color:#64748b;font-size:13px;} .alert{padding:10px 12px;border-radius:10px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;margin-bottom:12px;} .form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;} label{display:block;font-size:13px;font-weight:700;margin-bottom:6px;} input,select,textarea{width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;} .actions{margin-top:16px;display:flex;gap:8px;flex-wrap:wrap;}
    </style>
</head>
<body>
<div class="topo">
    <div>
        <h2 style="margin:0;">FARPOP 1.1</h2>
        <div class="muted">Olá, {{ session('nome_completo') ?: (session('usuario') ?: 'Usuário') }}</div>
    </div>
    <a class="btn" href="{{ route('logout') }}">Sair</a>
</div>
<nav class="nav">
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
    <a href="{{ route('clinicas.index') }}" class="{{ request()->routeIs('clinicas.*') ? 'active' : '' }}">Clínicas</a>
    <a href="{{ route('medicamentos.index') }}" class="{{ request()->routeIs('medicamentos.*') ? 'active' : '' }}">Medicamentos</a>
    <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">Usuários</a>
    <a href="{{ route('estoque.index') }}" class="{{ request()->routeIs('estoque.*') ? 'active' : '' }}">Estoque</a>
    <a href="{{ route('entradas.index') }}" class="{{ request()->routeIs('entradas.*') ? 'active' : '' }}">Entradas</a>
    <a href="{{ route('saidas.index') }}" class="{{ request()->routeIs('saidas.*') ? 'active' : '' }}">Saídas</a>
    <a href="{{ route('transferencias.index') }}" class="{{ request()->routeIs('transferencias.*') ? 'active' : '' }}">Transferências</a>
    <a href="{{ route('auditoria.index') }}" class="{{ request()->routeIs('auditoria.*') ? 'active' : '' }}">Auditoria</a>
</nav>
<div class="wrap">
    @yield('content')
</div>
</body>
</html>

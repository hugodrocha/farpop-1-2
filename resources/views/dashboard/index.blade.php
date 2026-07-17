<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FARPOP 1.1 — Dashboard</title>
    <style>
        body{margin:0;font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a;}
        .topo{background:#064e3b;color:#fff;padding:20px 24px;display:flex;justify-content:space-between;align-items:center;}
        .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;padding:24px;}
        .card{background:#fff;border-radius:14px;padding:18px;border:1px solid #e2e8f0;box-shadow:0 8px 20px rgba(15,23,42,.05);} 
        .card h3{margin:0 0 8px;font-size:16px;color:#0f766e;} 
        .valor{font-size:28px;font-weight:700;}
        .muted{font-size:12px;color:#64748b;}
        .btn{display:inline-block;padding:10px 14px;border-radius:10px;background:#0f766e;color:#fff;text-decoration:none;} 
    </style>
</head>
<body>
<div class="topo">
    <div>
        <h2 style="margin:0;">Dashboard FARPOP 1.1</h2>
        <p style="margin:4px 0 0;">
            Olá, {{ session('nome_completo') ?: (session('usuario') ?: 'Usuário') }}
        </p>
    </div>
    <a class="btn" href="{{ route('logout') }}">Sair</a>
</div>
<div class="cards">
    <div class="card">
        <h3>Clínicas</h3>
        <div class="valor">{{ $totais['clinicas'] }}</div>
        <div class="muted">unidades cadastradas</div>
    </div>
    <div class="card">
        <h3>Medicamentos</h3>
        <div class="valor">{{ $totais['medicamentos'] }}</div>
        <div class="muted">itens ativos no cadastro</div>
    </div>
    <div class="card">
        <h3>Estoque total</h3>
        <div class="valor">{{ $totais['estoque'] }}</div>
        <div class="muted">quantidade registrada</div>
    </div>
    <div class="card">
        <h3>Usuários</h3>
        <div class="valor">{{ $totais['usuarios'] }}</div>
        <div class="muted">contas ativas</div>
    </div>
</div>
</body>
</html>

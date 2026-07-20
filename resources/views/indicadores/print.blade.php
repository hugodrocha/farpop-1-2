<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 24px; }
        h1 { font-size: 18px; color: #0f766e; margin-bottom: 4px; }
        .sub { color: #666; margin-bottom: 20px; }
        .kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
        .kpi { border: 1px solid #ccc; padding: 10px; border-radius: 4px; }
        .kpi strong { display: block; font-size: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f0fdfa; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:16px;"><button onclick="window.print()">Imprimir</button></div>
    <h1>{{ $titulo }}</h1>
    <p class="sub">FARPOP 1.1 · gerado em {{ now()->format('d/m/Y H:i') }}</p>

    <div class="kpis">
        <div class="kpi"><span>Entradas (un.)</span><strong>{{ number_format($indicadores['entradas_unidades'], 0, ',', '.') }}</strong></div>
        <div class="kpi"><span>Dispensas (un.)</span><strong>{{ number_format($indicadores['saidas_unidades'], 0, ',', '.') }}</strong></div>
        <div class="kpi"><span>Pacientes</span><strong>{{ $indicadores['pacientes_atendidos'] }}</strong></div>
        <div class="kpi"><span>Transferências</span><strong>{{ $indicadores['transferencias_enviadas'] }}</strong></div>
        <div class="kpi"><span>Solicitações</span><strong>{{ $indicadores['solicitacoes_criadas'] }}</strong></div>
        <div class="kpi"><span>Estoque atual</span><strong>{{ number_format($indicadores['estoque_atual'], 0, ',', '.') }}</strong></div>
    </div>

    <h2 style="font-size:14px;">Últimos 6 meses</h2>
    <table>
        <thead>
            <tr><th>Período</th><th>Entradas</th><th>Dispensas</th><th>Pacientes</th><th>Transf.</th><th>Solicitações</th></tr>
        </thead>
        <tbody>
            @foreach($historico as $h)
            <tr>
                <td>{{ $h->periodo_label }}</td>
                <td>{{ number_format($h->entradas_unidades, 0, ',', '.') }}</td>
                <td>{{ number_format($h->saidas_unidades, 0, ',', '.') }}</td>
                <td>{{ $h->pacientes_atendidos }}</td>
                <td>{{ $h->transferencias_enviadas }}</td>
                <td>{{ $h->solicitacoes_criadas }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 24px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #0f766e; }
        .sub { color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f0fdfa; }
        h2 { font-size: 14px; margin: 24px 0 8px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:16px;"><button onclick="window.print()">Imprimir</button></div>
    <h1>{{ $titulo }}</h1>
    <p class="sub">FARPOP 1.1 — Farmácia Popular · gerado em {{ now()->format('d/m/Y H:i') }}</p>

    <h2>Resumo por unidade</h2>
    <table>
        <thead>
            <tr>
                <th>Ano</th>
                <th>Unidade</th>
                <th>Total dispensado</th>
                <th>Medicamentos distintos</th>
                <th>Pacientes atendidos</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resumo as $r)
            <tr>
                <td>{{ $r->ano }}</td>
                <td>{{ $r->unidade }}</td>
                <td>{{ number_format($r->total, 0, ',', '.') }}</td>
                <td>{{ $r->medicamentos_distintos }}</td>
                <td>{{ $r->pacientes_atendidos }}</td>
            </tr>
            @empty
            <tr><td colspan="5">Sem dispensas no período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Detalhamento por medicamento</h2>
    <table>
        <thead>
            <tr>
                <th>Ano</th>
                <th>Unidade</th>
                <th>Código</th>
                <th>Medicamento</th>
                <th>Total</th>
                <th>Registros</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detalhe as $r)
            <tr>
                <td>{{ $r->ano }}</td>
                <td>{{ $r->unidade }}</td>
                <td>{{ $r->codigo }}</td>
                <td>{{ $r->medicamento }} @if($r->dosagem)<small>{{ $r->dosagem }}</small>@endif</td>
                <td>{{ number_format($r->total, 0, ',', '.') }}</td>
                <td>{{ $r->qtd_registros }}</td>
            </tr>
            @empty
            <tr><td colspan="6">Sem dispensas no período.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

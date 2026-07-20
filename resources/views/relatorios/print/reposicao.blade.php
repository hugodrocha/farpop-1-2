<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; }
        h1 { font-size: 16px; color: #0f766e; margin-bottom: 4px; }
        .sub { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px 6px; text-align: left; }
        th { background: #f0fdfa; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:12px;"><button onclick="window.print()">Imprimir</button></div>
    <h1>{{ $titulo }}</h1>
    <p class="sub">FARPOP 1.1 · consumo últimos {{ $diasHistorico }} dias · meta {{ $diasCobertura }} dias · {{ now()->format('d/m/Y H:i') }}</p>
    <p class="sub">Ruptura: {{ $contagens['ruptura'] }} · Crítico: {{ $contagens['critico'] }} · Atenção: {{ $contagens['atencao'] }}</p>
    <table>
        <thead>
            <tr>
                <th>Unidade</th>
                <th>Código</th>
                <th>Medicamento</th>
                <th>Estoque</th>
                <th>Cons./dia</th>
                <th>Dias</th>
                <th>Sugerido</th>
                <th>Situação</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dados as $r)
            <tr>
                <td>{{ $r->unidade }}</td>
                <td>{{ $r->codigo }}</td>
                <td>{{ $r->medicamento }} @if($r->dosagem){{ $r->dosagem }}@endif</td>
                <td>{{ $r->quantidade }}</td>
                <td>{{ number_format($r->consumo_medio_dia, 2, ',', '.') }}</td>
                <td>{{ $r->dias_restantes ?? '—' }}</td>
                <td>{{ $r->qtd_sugerida > 0 ? $r->qtd_sugerida : '—' }}</td>
                <td>{{ $r->situacao_label }}</td>
            </tr>
            @empty
            <tr><td colspan="8">Sem itens para reposição.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

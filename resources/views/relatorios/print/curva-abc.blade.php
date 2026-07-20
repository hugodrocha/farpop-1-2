<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; color: #111; }
        h1 { font-size: 16px; color: #0f766e; margin-bottom: 4px; }
        .sub { color: #666; margin-bottom: 16px; }
        .resumo { display: flex; gap: 16px; margin-bottom: 16px; }
        .resumo span { padding: 6px 12px; border-radius: 4px; font-weight: bold; }
        .classe-a { background: #dcfce7; color: #166534; }
        .classe-b { background: #fef9c3; color: #854d0e; }
        .classe-c { background: #fee2e2; color: #991b1b; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px 6px; text-align: left; }
        th { background: #f0fdfa; }
        .badge-a { background: #166534; color: #fff; padding: 2px 6px; border-radius: 3px; }
        .badge-b { background: #854d0e; color: #fff; padding: 2px 6px; border-radius: 3px; }
        .badge-c { background: #991b1b; color: #fff; padding: 2px 6px; border-radius: 3px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:12px;"><button onclick="window.print()">Imprimir</button></div>
    <h1>{{ $titulo }}</h1>
    <p class="sub">FARPOP 1.1 · {{ $periodo }} · gerado em {{ now()->format('d/m/Y H:i') }}</p>
    <p class="sub">Classificação Pareto: <strong>A</strong> até 80% acumulado · <strong>B</strong> até 95% · <strong>C</strong> restante</p>

    <div class="resumo">
        <span class="classe-a">Classe A: {{ $resumo['A'] }} item(ns)</span>
        <span class="classe-b">Classe B: {{ $resumo['B'] }} item(ns)</span>
        <span class="classe-c">Classe C: {{ $resumo['C'] }} item(ns)</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Unidade</th>
                <th>Código</th>
                <th>Medicamento</th>
                <th>Total</th>
                <th>%</th>
                <th>% acum.</th>
                <th>Classe</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dados as $r)
            <tr>
                <td>{{ $r->ranking }}</td>
                <td>{{ $r->unidade }}</td>
                <td>{{ $r->codigo }}</td>
                <td>{{ $r->medicamento }} @if($r->dosagem)<small>{{ $r->dosagem }}</small>@endif</td>
                <td>{{ number_format($r->total, 0, ',', '.') }}</td>
                <td>{{ number_format($r->percentual, 2, ',', '.') }}%</td>
                <td>{{ number_format($r->percentual_acumulado, 2, ',', '.') }}%</td>
                <td><span class="badge-{{ strtolower($r->classe) }}">{{ $r->classe }}</span></td>
            </tr>
            @empty
            <tr><td colspan="8">Sem dispensas no período selecionado.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

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
        .contada { width: 70px; background: #fffef0; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:12px;"><button onclick="window.print()">Imprimir</button></div>
    <h1>{{ $titulo }}</h1>
    <p class="sub">FARPOP 1.1 · {{ now()->format('d/m/Y H:i') }} · preencha a coluna "Qtd contada" durante o inventário físico</p>
    <table>
        <thead>
            <tr>
                <th>Unidade</th>
                <th>Código</th>
                <th>Medicamento</th>
                <th>Lote</th>
                <th>Validade</th>
                <th>Qtd sistema</th>
                <th class="contada">Qtd contada</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dados as $r)
            <tr>
                <td>{{ $r->unidade }}</td>
                <td>{{ $r->codigo }}</td>
                <td>{{ $r->medicamento }} @if($r->dosagem){{ $r->dosagem }}@endif</td>
                <td>{{ $r->lote ?: '—' }}</td>
                <td>{{ $r->validade_fmt ?: '—' }}</td>
                <td>{{ $r->quantidade }}</td>
                <td class="contada"></td>
            </tr>
            @empty
            <tr><td colspan="7">Nenhum lote com saldo.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

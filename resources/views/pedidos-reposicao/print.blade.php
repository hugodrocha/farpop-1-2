<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 24px; }
        h1 { font-size: 18px; color: #0f766e; margin-bottom: 4px; }
        .sub { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f0fdfa; }
        .assinatura { margin-top: 48px; display: flex; gap: 40px; }
        .assinatura div { flex: 1; border-top: 1px solid #333; padding-top: 8px; text-align: center; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:16px;"><button onclick="window.print()">Imprimir</button></div>
    <h1>{{ $titulo }}</h1>
    <p class="sub">FARPOP 1.1 · {{ $totalItens }} item(ns) · {{ number_format($totalUnidades, 0, ',', '.') }} unidades · {{ now()->format('d/m/Y H:i') }}@if($agrupar) · agrupado por medicamento@endif</p>
    <table>
        <thead>
            <tr>
                <th>Unidade</th>
                <th>Código</th>
                <th>Medicamento</th>
                <th>Estoque</th>
                <th>Qtd pedido</th>
                <th>Prioridade</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itens as $it)
            <tr>
                <td>{{ $it->unidade }}</td>
                <td>{{ $it->codigo }}</td>
                <td>{{ $it->medicamento }} @if($it->dosagem){{ $it->dosagem }}@endif</td>
                <td>{{ $it->quantidade }}</td>
                <td><strong>{{ $it->qtd_sugerida }}</strong></td>
                <td>{{ $it->situacao_label }}</td>
            </tr>
            @empty
            <tr><td colspan="6">Nenhum item no pedido.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="assinatura">
        <div>Solicitante</div>
        <div>Farmácia central</div>
        <div>Aprovação</div>
    </div>
</body>
</html>

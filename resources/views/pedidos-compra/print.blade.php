<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedido {{ $pedido->numero }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 24px; }
        h1 { font-size: 18px; color: #0f766e; margin-bottom: 4px; }
        .sub { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f0fdfa; }
        .meta { margin-bottom: 12px; }
        .assinatura { margin-top: 48px; display: flex; gap: 40px; }
        .assinatura div { flex: 1; border-top: 1px solid #333; padding-top: 8px; text-align: center; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:16px;"><button onclick="window.print()">Imprimir</button></div>
    <h1>Pedido de compra {{ $pedido->numero }}</h1>
    <p class="sub">FARPOP {{ config('farpop.versao', '1.2') }} · {{ $pedido->status_label }} · {{ $pedido->unidade_label }}</p>
    <div class="meta">
        <strong>Data:</strong> {{ $pedido->criado_em ? \Carbon\Carbon::parse($pedido->criado_em)->format('d/m/Y H:i') : '—' }} ·
        <strong>Solicitante:</strong> {{ $pedido->usuario_nome ?: '—' }} ·
        <strong>Itens:</strong> {{ $pedido->total_itens }} ·
        <strong>Unidades:</strong> {{ number_format($pedido->total_unidades, 0, ',', '.') }}
        @if($pedido->observacao)<br><strong>Obs.:</strong> {{ $pedido->observacao }}@endif
    </div>
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
            @foreach($pedido->itens as $it)
            <tr>
                <td>{{ $it->unidade_nome ?: '—' }}</td>
                <td>{{ $it->codigo ?: '—' }}</td>
                <td>{{ $it->medicamento }} @if($it->dosagem){{ $it->dosagem }}@endif</td>
                <td>{{ $it->estoque_atual }}</td>
                <td><strong>{{ $it->qtd_pedido }}</strong></td>
                <td>{{ $it->prioridade ?: '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="assinatura">
        <div>Solicitante</div>
        <div>Farmácia central</div>
        <div>Aprovação / recebimento</div>
    </div>
</body>
</html>

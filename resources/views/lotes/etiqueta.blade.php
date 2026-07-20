<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Etiqueta — {{ $lote->medicamento }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 16px; }
        .etiqueta { border: 2px solid #111; padding: 12px; width: 300px; }
        .nome { font-weight: bold; font-size: 14px; margin-bottom: 6px; line-height: 1.2; }
        .codigo { font-size: 11px; color: #444; margin-bottom: 6px; }
        .barcode-area { text-align: center; margin: 8px 0 4px; }
        .barcode-area svg { max-width: 100%; height: auto; }
        .barcode-legenda { font-size: 10px; color: #555; text-align: center; font-family: monospace; }
        @media print { .no-print { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:12px;"><button onclick="window.print()">Imprimir</button></div>
    <div class="etiqueta">
        <div class="nome">{{ $lote->medicamento }}</div>
        <div class="codigo">Cód. REMUME {{ $lote->codigo }} · {{ $lote->clinica }}</div>
        <div>Lote: <strong>{{ $lote->lote ?: '—' }}</strong></div>
        @if($lote->fabricante)<div>Fab.: {{ $lote->fabricante }}</div>@endif
        <div>Validade: <strong>{{ $lote->validade_fmt ?: '—' }}</strong></div>
        <div>Qtd: <strong>{{ $lote->quantidade }}</strong></div>
        <div class="barcode-area">{!! $barcodeSvg !!}</div>
        <div class="barcode-legenda">CODE128 · {{ $barcodeValor }}</div>
    </div>
</body>
</html>

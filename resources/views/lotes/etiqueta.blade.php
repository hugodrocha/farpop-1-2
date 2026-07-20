<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Etiqueta — {{ $lote->medicamento }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 16px; }
        .etiqueta { border: 2px solid #111; padding: 12px; width: 280px; }
        .nome { font-weight: bold; font-size: 14px; margin-bottom: 6px; }
        .codigo { font-size: 11px; color: #444; }
        .barcode { font-family: 'Libre Barcode 128', monospace; font-size: 42px; letter-spacing: 2px; margin: 8px 0; }
        @media print { .no-print { display: none; } }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap" rel="stylesheet">
</head>
<body>
    <div class="no-print" style="margin-bottom:12px;"><button onclick="window.print()">Imprimir</button></div>
    <div class="etiqueta">
        <div class="nome">{{ $lote->medicamento }}</div>
        <div class="codigo">Cód. {{ $lote->codigo }} · {{ $lote->clinica }}</div>
        <div>Lote: <strong>{{ $lote->lote ?: '—' }}</strong></div>
        @if($lote->fabricante)<div>Fab.: {{ $lote->fabricante }}</div>@endif
        <div>Validade: <strong>{{ $lote->validade_fmt ?: '—' }}</strong></div>
        <div>Qtd: <strong>{{ $lote->quantidade }}</strong></div>
        <div class="barcode">{{ $lote->codigo }}-{{ $lote->lote ?: $lote->id }}</div>
    </div>
</body>
</html>

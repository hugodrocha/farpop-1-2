<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }} — FARPOP</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 24px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .meta { color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:16px;">
        <button onclick="window.print()">Imprimir / Salvar PDF</button>
    </div>
    <h1>{{ $titulo }}</h1>
    <div class="meta">FARPOP — emitido em {{ now()->format('d/m/Y H:i') }}</div>
    <table>
        <thead>
            <tr>
                <th>Unidade</th>
                <th>Código</th>
                <th>Medicamento</th>
                <th>Dosagem</th>
                <th>Un.</th>
                <th>Quantidade</th>
                <th>Mínimo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dados as $r)
                <tr>
                    <td>{{ $r->unidade }}</td>
                    <td>{{ $r->codigo }}</td>
                    <td>{{ $r->medicamento }}</td>
                    <td>{{ $r->dosagem }}</td>
                    <td>{{ $r->un_medida }}</td>
                    <td>{{ $r->quantidade }}</td>
                    <td>{{ $r->estoque_minimo }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

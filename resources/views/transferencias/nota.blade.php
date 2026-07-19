@extends('layouts.app')

@section('title', 'Nota de Transferência — ' . ($codigoNota ?: $t->id))

@push('styles')
<style>
    @media print {
        .sidebar-sistema, .menu-mobile-topo, .no-print { display: none !important; }
        .conteudo-sistema, .main-content { padding: 0 !important; margin: 0 !important; }
        .layout-sistema { display: block !important; }
    }
    .nota-header { border-bottom: 2px solid #0f766e; padding-bottom: 12px; margin-bottom: 16px; }
    .nota-titulo { font-size: 1.25rem; font-weight: 700; color: #0f766e; }
    .barcode-area { text-align: center; margin: 16px 0; }
</style>
@endpush

@section('content')
<div class="container-fluid px-0" style="max-width: 800px;">
    <div class="no-print mb-3 d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i> Imprimir</button>
        <a href="{{ route('transferencias.transito') }}" class="btn btn-outline-secondary btn-sm">Voltar</a>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <div class="nota-header d-flex justify-content-between align-items-start">
                <div>
                    <div class="nota-titulo">FARPOP 1.1 — Nota de Transferência</div>
                    <div class="text-muted">Farmácia Popular — Prefeitura de Mesquita</div>
                </div>
                <div class="text-end">
                    <div><strong>Nota:</strong> {{ $codigoNota ?: $t->id }}</div>
                    <div><strong>Data:</strong> {{ $t->enviado_em ? \Carbon\Carbon::parse($t->enviado_em)->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</div>
                    <div><span class="badge bg-warning text-dark">{{ strtoupper($t->status ?? 'pendente') }}</span></div>
                </div>
            </div>

            @if($barcodeSvg)
                <div class="barcode-area">
                    {!! $barcodeSvg !!}
                    <div class="small text-muted mt-1">{{ $codigoNota }}</div>
                </div>
            @endif

            <div class="row mb-3">
                <div class="col-6"><strong>Origem:</strong><br>{{ $t->clinica_origem }}</div>
                <div class="col-6"><strong>Destino:</strong><br>{{ $t->clinica_destino }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-6"><strong>Enviado por:</strong> {{ $t->enviado_por ?? '—' }}</div>
                <div class="col-6"><strong>Observação:</strong> {{ $t->observacao ?: '—' }}</div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Medicamento</th>
                            <th>Dosagem</th>
                            <th>Unidade</th>
                            <th class="text-end">Quantidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itens as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->nome }}</td>
                                <td>{{ $item->dosagem }}</td>
                                <td>{{ $item->unidade }}</td>
                                <td class="text-end fw-bold">{{ number_format($item->quantidade_enviada ?? $item->quantidade ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total de itens:</td>
                            <td class="text-end fw-bold">{{ $itens->count() }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total de unidades:</td>
                            <td class="text-end fw-bold">{{ number_format($itens->sum(fn($i) => $i->quantidade_enviada ?? $i->quantidade ?? 0), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row mt-5 pt-4">
                <div class="col-6 text-center">
                    <div style="border-top: 1px solid #333; margin: 0 20px; padding-top: 8px;">Assinatura — Origem</div>
                </div>
                <div class="col-6 text-center">
                    <div style="border-top: 1px solid #333; margin: 0 20px; padding-top: 8px;">Assinatura — Destino (recebimento)</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

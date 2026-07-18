@extends('layouts.app')

@section('title', 'Comprovante de Dispensação')

@push('styles')
<style>
    @media print {
        .sidebar-sistema, .menu-mobile-topo, .no-print { display: none !important; }
        .conteudo-sistema, .main-content { padding: 0 !important; margin: 0 !important; }
        .layout-sistema { display: block !important; }
    }
    .comprovante-cabecalho { border-bottom: 2px solid #064e3b; padding-bottom: 12px; margin-bottom: 16px; }
    .comprovante-titulo { font-size: 1.2rem; font-weight: 700; color: #064e3b; }
</style>
@endpush

@section('content')
<div class="container-fluid px-0" style="max-width: 720px;">
    <div class="no-print mb-3 d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i> Imprimir</button>
        <a href="{{ route('saidas.index') }}" class="btn btn-outline-secondary btn-sm">Voltar</a>
        <a href="{{ route('saidas.create') }}" class="btn btn-outline-primary btn-sm">Nova saída</a>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <div class="comprovante-cabecalho">
                <div class="comprovante-titulo">FARPOP 1.1 — Comprovante de Dispensação</div>
                <div class="text-muted">Farmácia Popular — {{ $saida->clinica }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-6">
                    <strong>Data:</strong><br>
                    {{ $saida->data_saida ? \Carbon\Carbon::parse($saida->data_saida)->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}
                </div>
                <div class="col-6">
                    <strong>Atendente:</strong><br>
                    {{ $saida->atendente ?? '—' }}
                </div>
            </div>

            <div class="border rounded p-3 mb-3 bg-light">
                <strong>Paciente</strong><br>
                {{ $saida->paciente }}<br>
                <span class="text-muted">CPF: {{ $saida->cpf ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $saida->cpf) : '—' }}</span>
            </div>

            @if($saida->prescritor || $saida->numero_solicitacao)
                <div class="row mb-3 small">
                    @if($saida->prescritor)
                        <div class="col-6"><strong>Prescritor:</strong> {{ $saida->prescritor }}</div>
                    @endif
                    @if($saida->numero_solicitacao)
                        <div class="col-6"><strong>Solicitação:</strong> {{ $saida->numero_solicitacao }}</div>
                    @endif
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Medicamento</th>
                            <th>Dosagem</th>
                            <th class="text-end">Qtd</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itens as $item)
                            <tr>
                                <td>{{ $item->medicamento }}</td>
                                <td>{{ $item->dosagem }} {{ $item->unidade }}</td>
                                <td class="text-end fw-bold">{{ number_format($item->quantidade, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($saida->observacao)
                <p class="small"><strong>Observação:</strong> {{ $saida->observacao }}</p>
            @endif

            <div class="text-center mt-5 pt-4">
                <div style="border-top: 1px solid #333; display: inline-block; min-width: 280px; padding-top: 8px;">
                    Assinatura do paciente ou responsável
                </div>
            </div>

            <p class="text-muted small text-center mt-4 mb-0">
                Documento gerado em {{ now()->format('d/m/Y H:i') }} — Comprovante nº {{ $saida->id }}
            </p>
        </div>
    </div>
</div>
@endsection

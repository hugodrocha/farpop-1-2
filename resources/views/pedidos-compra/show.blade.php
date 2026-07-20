@extends('layouts.app')

@section('title', 'Pedido ' . $pedido->numero)

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-file-invoice me-2"></i>{{ $pedido->numero }}</h1>
            <p class="farpop-page-subtitle">{{ $pedido->unidade_label }} · {{ $pedido->total_itens }} item(ns) · {{ number_format($pedido->total_unidades, 0, ',', '.') }} unidades</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('pedidos-compra.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Voltar</a>
            <a href="{{ route('pedidos-compra.exportar', $pedido->id) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-csv me-1"></i> CSV</a>
            <a href="{{ route('pedidos-compra.imprimir', $pedido->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print me-1"></i> Imprimir</a>
        </div>
    </div>

    @include('partials.flash')

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span>Resumo</span>
                    <span class="badge bg-{{ $pedido->status_badge }}">{{ $pedido->status_label }}</span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4">Criado em</dt>
                        <dd class="col-sm-8">{{ $pedido->criado_em ? \Carbon\Carbon::parse($pedido->criado_em)->format('d/m/Y H:i') : '—' }}</dd>
                        <dt class="col-sm-4">Solicitante</dt>
                        <dd class="col-sm-8">{{ $pedido->usuario_nome ?: '—' }}</dd>
                        <dt class="col-sm-4">Enviado em</dt>
                        <dd class="col-sm-8">{{ $pedido->enviado_em ? \Carbon\Carbon::parse($pedido->enviado_em)->format('d/m/Y H:i') : '—' }}</dd>
                        <dt class="col-sm-4">Atendido em</dt>
                        <dd class="col-sm-8">{{ $pedido->atendido_em ? \Carbon\Carbon::parse($pedido->atendido_em)->format('d/m/Y H:i') : '—' }}</dd>
                        @if($pedido->observacao)
                        <dt class="col-sm-4">Observação</dt>
                        <dd class="col-sm-8">{{ $pedido->observacao }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header fw-semibold">Ações</div>
                <div class="card-body d-flex flex-column gap-2">
                    @if($pedido->status === 'rascunho')
                    <form method="POST" action="{{ route('pedidos-compra.enviar', $pedido->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane me-1"></i> Marcar como enviado</button>
                    </form>
                    <form method="POST" action="{{ route('pedidos-compra.cancelar', $pedido->id) }}" onsubmit="return confirm('Cancelar este pedido?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">Cancelar pedido</button>
                    </form>
                    @elseif($pedido->status === 'enviado')
                    <form method="POST" action="{{ route('pedidos-compra.atender', $pedido->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-check me-1"></i> Marcar como atendido</button>
                    </form>
                    <form method="POST" action="{{ route('pedidos-compra.cancelar', $pedido->id) }}" onsubmit="return confirm('Cancelar este pedido?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">Cancelar pedido</button>
                    </form>
                    @else
                    <p class="text-muted small mb-0">Pedido {{ strtolower($pedido->status_label) }}. Nenhuma ação disponível.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header fw-semibold">Itens do pedido</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Unidade</th>
                        <th>Código</th>
                        <th>Medicamento</th>
                        <th>Estoque</th>
                        <th>Pedido</th>
                        <th>Prioridade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedido->itens as $it)
                    <tr>
                        <td>{{ $it->unidade_nome ?: '—' }}</td>
                        <td><code>{{ $it->codigo ?: '—' }}</code></td>
                        <td>{{ $it->medicamento }} @if($it->dosagem)<small class="text-muted">{{ $it->dosagem }}</small>@endif</td>
                        <td>{{ $it->estoque_atual }}</td>
                        <td><strong class="text-primary">{{ $it->qtd_pedido }}</strong></td>
                        <td>{{ $it->prioridade ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

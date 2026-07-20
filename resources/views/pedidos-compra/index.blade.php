@extends('layouts.app')

@section('title', 'Pedidos de compra')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-file-invoice me-2"></i>Pedidos de compra</h1>
            <p class="farpop-page-subtitle">Histórico de pedidos registrados a partir da previsão de reposição.</p>
        </div>
        <a href="{{ route('pedidos-reposicao.index') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Novo a partir da reposição</a>
    </div>

    @include('partials.flash')

    @if($ehAdmin && $clinicas->isNotEmpty())
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('pedidos-compra.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5 col-lg-4">
                    <label class="form-label small mb-1">Unidade</label>
                    <select name="clinica_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Todas</option>
                        @foreach($clinicas as $c)
                            <option value="{{ $c->id }}" @selected($clinicaId === (int) $c->id)>{{ $c->nome }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header bg-primary d-flex justify-content-between align-items-center">
            <span><i class="fas fa-history me-2"></i>{{ $pedidos->count() }} pedido(s) registrado(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Data</th>
                        <th>Unidade</th>
                        <th>Itens</th>
                        <th>Unidades</th>
                        <th>Status</th>
                        <th>Solicitante</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $p)
                    <tr>
                        <td><code>{{ $p->numero }}</code></td>
                        <td class="text-nowrap">{{ $p->criado_em ? \Carbon\Carbon::parse($p->criado_em)->format('d/m/Y H:i') : '—' }}</td>
                        <td>{{ $p->unidade_label }}</td>
                        <td>{{ $p->total_itens }}</td>
                        <td>{{ number_format($p->total_unidades, 0, ',', '.') }}</td>
                        <td><span class="badge bg-{{ $p->status_badge }}">{{ $p->status_label }}</span></td>
                        <td>{{ $p->usuario_nome ?: '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('pedidos-compra.show', $p->id) }}" class="btn btn-outline-primary btn-sm">Abrir</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">Nenhum pedido registrado. Gere um a partir da <a href="{{ route('pedidos-reposicao.index') }}">lista de reposição</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

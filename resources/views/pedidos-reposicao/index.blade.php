@extends('layouts.app')

@section('title', 'Pedido de reposição')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-shopping-cart me-2"></i>Pedido de reposição</h1>
            <p class="farpop-page-subtitle">Lista de compras gerada a partir da previsão de consumo (últimos {{ \App\Services\ReposicaoService::diasHistorico() }} dias).</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('alertas.ruptura') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-chart-line me-1"></i> Ver previsão</a>
            <a href="{{ route('pedidos-compra.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-invoice me-1"></i> Pedidos registrados</a>
            @if($itens->isNotEmpty())
            <form method="POST" action="{{ route('pedidos-compra.store') }}" class="d-inline">
                @csrf
                @if($clinicaId)<input type="hidden" name="clinica_id" value="{{ $clinicaId }}">@endif
                @if($agrupar)<input type="hidden" name="agrupar" value="1">@endif
                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Registrar pedido de compra com {{ $itens->count() }} item(ns)?');"><i class="fas fa-save me-1"></i> Registrar pedido</button>
            </form>
            @endif
            <a href="{{ route('pedidos-reposicao.exportar', array_filter(['clinica_id' => $clinicaId ?: null, 'agrupar' => $agrupar ? 1 : null])) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-csv me-1"></i> CSV</a>
            <a href="{{ route('pedidos-reposicao.imprimir', array_filter(['clinica_id' => $clinicaId ?: null, 'agrupar' => $agrupar ? 1 : null])) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print me-1"></i> Imprimir</a>
        </div>
    </div>

    @include('partials.flash')

    @if($ehAdmin && $clinicas->isNotEmpty())
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('pedidos-reposicao.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5 col-lg-4">
                    <label class="form-label small mb-1">Unidade</label>
                    <select name="clinica_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Todas (visão central)</option>
                        @foreach($clinicas as $c)
                            <option value="{{ $c->id }}" @selected($clinicaId === (int) $c->id)>{{ $c->nome }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!$clinicaId)
                <div class="col-md-4 col-lg-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="agrupar" value="1" class="form-check-input" id="agruparMed" @checked($agrupar) onchange="this.form.submit()">
                        <label class="form-check-label small" for="agruparMed">Agrupar por medicamento</label>
                    </div>
                </div>
                @endif
            </form>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header bg-primary d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i>{{ $itens->count() }} item(ns) · {{ number_format($itens->sum('qtd_sugerida'), 0, ',', '.') }} unidades sugeridas</span>
        </div>
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
                    @forelse($itens as $it)
                    <tr>
                        <td>{{ $it->unidade }}</td>
                        <td><code>{{ $it->codigo }}</code></td>
                        <td>{{ $it->medicamento }} @if($it->dosagem)<small class="text-muted">{{ $it->dosagem }}</small>@endif</td>
                        <td>{{ $it->quantidade }}</td>
                        <td><strong class="text-primary">{{ $it->qtd_sugerida }}</strong></td>
                        <td><span class="badge bg-{{ $it->badge_class }}">{{ $it->situacao_label }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">Nenhum item precisa de reposição no momento.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

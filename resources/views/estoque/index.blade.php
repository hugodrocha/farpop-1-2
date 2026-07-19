@extends('layouts.app')

@section('title', 'Estoque')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-boxes me-2"></i>Estoque</h1>
        <p class="farpop-page-subtitle">Consulte saldo por código, código de barras ou nome do medicamento.</p>
    </div>

    @include('partials.flash')

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('estoque.index') }}" class="row g-3 align-items-end">
                <div class="col-md-{{ $ehAdmin ? '5' : '8' }}">
                    <label class="form-label">Buscar medicamento</label>
                    <input type="text" name="q" class="form-control form-control-lg" value="{{ $busca }}" placeholder="Código REMUME, código de barras ou nome" autofocus>
                </div>
                @if($ehAdmin)
                    <div class="col-md-4">
                        <label class="form-label">Clínica</label>
                        <select name="clinica_id" class="form-select">
                            <option value="">Todas as unidades</option>
                            @foreach($clinicas as $clinica)
                                <option value="{{ $clinica->id }}" {{ $clinicaFiltro == $clinica->id ? 'selected' : '' }}>{{ $clinica->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary btn-lg flex-grow-1" type="submit"><i class="fas fa-search me-1"></i> Buscar</button>
                    @if($busca || $clinicaFiltro)
                        <a href="{{ route('estoque.index') }}" class="btn btn-outline-secondary btn-lg">Limpar</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Clínica</th>
                            <th>Código</th>
                            <th>Medicamento</th>
                            <th>Status</th>
                            <th>Quantidade</th>
                            <th>Atualizado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itens as $item)
                            <tr>
                                <td>{{ $item->clinica_nome }}</td>
                                <td><code>{{ $item->codigo ?: '—' }}</code></td>
                                <td class="fw-medium">{{ $item->medicamento_nome }} {{ $item->dosagem }} <span class="text-muted">{{ $item->unidade }}</span></td>
                                <td><span class="badge bg-{{ $item->badge_class }}">{{ $item->badge_label }}</span></td>
                                <td><span class="badge bg-primary fs-6">{{ number_format($item->quantidade, 0, ',', '.') }}</span></td>
                                <td class="text-muted">{{ $item->atualizado_em ? \Carbon\Carbon::parse($item->atualizado_em)->format('d/m/Y H:i') : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhum item encontrado{{ $busca ? ' para esta busca' : '' }}.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

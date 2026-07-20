@extends('layouts.app')

@section('title', 'Lotes em estoque')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-layer-group me-2"></i>Controle por lote</h1>
            <p class="farpop-page-subtitle">Saldo por lote ordenado por validade (PEPS — primeiro que vence, primeiro a sair).</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('ajustes.create') }}" class="btn btn-outline-warning btn-sm"><i class="fas fa-sliders-h me-1"></i> Ajuste</a>
            <a href="{{ route('inventario.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-clipboard-check me-1"></i> Inventário</a>
            <a href="{{ route('movimentacoes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-history me-1"></i> Movimentações</a>
        </div>
    </div>

    @include('partials.flash')

    @if($clinicas->isNotEmpty())
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">Unidade</label>
                    <select name="clinica_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach($clinicas as $c)
                            <option value="{{ $c->id }}" {{ $clinicaId == $c->id ? 'selected' : '' }}>{{ $c->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Filtrar</button></div>
            </form>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Unidade</th>
                            <th>Medicamento</th>
                            <th>Lote</th>
                            <th>Fabricante</th>
                            <th>Validade</th>
                            <th>Saldo</th>
                            <th>Inicial</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lotes as $l)
                            <tr>
                                <td>{{ $l->clinica }}</td>
                                <td><code>{{ $l->codigo }}</code> {{ $l->medicamento }}</td>
                                <td>{{ $l->lote ?: '—' }}</td>
                                <td>{{ $l->fabricante ?: '—' }}</td>
                                <td class="text-nowrap">{{ $l->validade_fmt ?: '—' }}</td>
                                <td><strong>{{ $l->quantidade }}</strong></td>
                                <td class="text-muted">{{ $l->quantidade_inicial }}</td>
                                <td class="text-end">
                                    <a href="{{ route('lotes.etiqueta', $l->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Etiqueta"><i class="fas fa-tag"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-5">Nenhum lote com saldo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

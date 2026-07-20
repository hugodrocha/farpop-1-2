@extends('layouts.app')

@section('title', 'Solicitações de transferência')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-clipboard-list me-2"></i>Solicitações</h1>
            <p class="farpop-page-subtitle">Unidades solicitam medicamentos à central — aprovação, separação e envio.</p>
        </div>
        @if(!$visaoCentral)
        <a href="{{ route('solicitacoes.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nova solicitação</a>
        @endif
    </div>

    @include('partials.flash')

    <div class="card mb-3">
        <div class="card-body py-2">
            <form class="row g-2 align-items-end" method="GET">
                <div class="col-md-4">
                    <label class="form-label mb-0 small">Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        @foreach(['solicitada','aprovada','em_separacao','enviada','recusada','cancelada'] as $st)
                            <option value="{{ $st }}" {{ ($status ?? '') === $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Solicitante</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($solicitacoes as $s)
                        <tr>
                            <td class="fw-medium">{{ $s->codigo }}</td>
                            <td>{{ $s->solicitante }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ', $s->status)) }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($s->criado_em)->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('solicitacoes.show', $s->id) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Nenhuma solicitação encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

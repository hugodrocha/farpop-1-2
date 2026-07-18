@extends('layouts.app')

@section('title', 'Entradas')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-arrow-down me-2"></i>Entradas</h1>
            <p class="farpop-page-subtitle">Últimas entradas de medicamentos registradas.</p>
        </div>
        <a href="{{ route('entradas.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nova entrada</a>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Clínica</th>
                            <th>Medicamento</th>
                            <th>Qtd.</th>
                            <th>Usuário</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entradas as $entrada)
                            <tr>
                                <td>{{ $entrada->data_entrada ? \Carbon\Carbon::parse($entrada->data_entrada)->format('d/m/Y H:i') : '—' }}</td>
                                <td>{{ $entrada->clinica_nome }}</td>
                                <td class="fw-medium">{{ $entrada->medicamento_nome }} {{ $entrada->dosagem }}</td>
                                <td><span class="badge bg-success">{{ $entrada->quantidade }}</span></td>
                                <td>{{ $entrada->usuario_nome ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('entradas.edit', $entrada->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma entrada registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

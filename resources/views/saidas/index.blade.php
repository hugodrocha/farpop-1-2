@extends('layouts.app')

@section('title', 'Dispensa de Medicamentos')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-pills me-2"></i>Dispensa de Medicamentos</h1>
            <p class="farpop-page-subtitle">Histórico de dispensações para pacientes.</p>
        </div>
        <a href="{{ route('saidas.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nova dispensação</a>
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
                            <th>Paciente</th>
                            <th>Medicamento</th>
                            <th>Qtd.</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($saidas as $saida)
                            <tr>
                                <td>{{ $saida->data_saida ? \Carbon\Carbon::parse($saida->data_saida)->format('d/m/Y H:i') : '—' }}</td>
                                <td>{{ $saida->clinica_nome }}</td>
                                <td class="fw-medium">{{ $saida->paciente_nome }}</td>
                                <td>{{ $saida->medicamento_nome }} {{ $saida->dosagem }}</td>
                                <td><span class="badge bg-warning text-dark">{{ $saida->quantidade }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('saidas.comprovante', $saida->id) }}" class="btn btn-sm btn-outline-secondary" title="Comprovante"><i class="fas fa-receipt"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma dispensação registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

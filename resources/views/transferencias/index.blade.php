@extends('layouts.app')

@section('title', 'Transferências')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-truck me-2"></i>Transferências</h1>
            <p class="farpop-page-subtitle">Movimentação de medicamentos entre unidades.</p>
        </div>
        <a href="{{ route('transferencias.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nova transferência</a>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nota</th>
                            <th>Data</th>
                            <th>Origem</th>
                            <th>Destino</th>
                            <th>Status</th>
                            <th>Usuário</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transferencias as $transferencia)
                            <tr>
                                <td><code class="small">{{ $transferencia->codigo_nota ?: '—' }}</code></td>
                                <td>{{ $transferencia->enviado_em ? \Carbon\Carbon::parse($transferencia->enviado_em)->format('d/m/Y H:i') : '—' }}</td>
                                <td>{{ $transferencia->clinica_origem }}</td>
                                <td>{{ $transferencia->clinica_destino }}</td>
                                <td>
                                    @if($transferencia->status === 'pendente')
                                        <span class="badge bg-warning text-dark">Pendente</span>
                                    @elseif($transferencia->status === 'recebida')
                                        <span class="badge bg-success">Recebida OK</span>
                                    @elseif($transferencia->status === 'recebida_parcial')
                                        <span class="badge bg-info text-dark">Parcial</span>
                                    @elseif($transferencia->status === 'nao_recebida')
                                        <span class="badge bg-danger">Não recebida</span>
                                    @elseif($transferencia->status === 'cancelada')
                                        <span class="badge bg-secondary">Cancelada</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $transferencia->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $transferencia->usuario_nome ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('transferencias.nota', $transferencia->id) }}" class="btn btn-sm btn-outline-primary" title="Nota"><i class="fas fa-print"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma transferência registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

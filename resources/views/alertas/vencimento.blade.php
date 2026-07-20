@extends('layouts.app')

@section('title', 'Alertas de vencimento')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-calendar-times me-2"></i>Validade de medicamentos</h1>
            <p class="farpop-page-subtitle">Lotes com validade registrada em entradas, para medicamentos com estoque positivo. Alerta de vencimento: {{ $contagens['dias'] }} dias.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('relatorios.exportar', ['tipo' => 'vencidos', 'clinica_id' => $clinicaId ?: null]) }}" class="btn btn-outline-danger btn-sm"><i class="fas fa-file-csv me-1"></i> CSV vencidos</a>
            <a href="{{ route('relatorios.exportar', ['tipo' => 'vencendo', 'clinica_id' => $clinicaId ?: null]) }}" class="btn btn-outline-warning btn-sm"><i class="fas fa-file-csv me-1"></i> CSV vencendo</a>
            <a href="{{ route('relatorios.imprimir', ['tipo' => 'vencendo', 'clinica_id' => $clinicaId ?: null]) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print me-1"></i> Imprimir</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="text-muted small">Vencidos</div>
                    <div class="fs-3 fw-bold text-danger">{{ $contagens['vencidos'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="text-muted small">Vencendo ({{ $contagens['dias'] }} dias)</div>
                    <div class="fs-3 fw-bold text-warning">{{ $contagens['vencendo'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="btn-group" role="group">
                <a href="{{ route('alertas.vencimento', array_filter(['tipo' => 'todos', 'clinica_id' => $clinicaId ?: null])) }}" class="btn btn-sm {{ $tipo === 'todos' ? 'btn-primary' : 'btn-outline-primary' }}">Todos</a>
                <a href="{{ route('alertas.vencimento', array_filter(['tipo' => 'vencido', 'clinica_id' => $clinicaId ?: null])) }}" class="btn btn-sm {{ $tipo === 'vencido' ? 'btn-danger' : 'btn-outline-danger' }}">Vencidos</a>
                <a href="{{ route('alertas.vencimento', array_filter(['tipo' => 'vencendo', 'clinica_id' => $clinicaId ?: null])) }}" class="btn btn-sm {{ $tipo === 'vencendo' ? 'btn-warning' : 'btn-outline-warning' }}">Vencendo</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Unidade</th>
                            <th>Medicamento</th>
                            <th>Lote</th>
                            <th>Validade</th>
                            <th>Qtd entrada</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itens as $item)
                            <tr>
                                <td>{{ $item->clinica }}</td>
                                <td><span class="text-muted small">{{ $item->codigo }}</span> {{ $item->medicamento }}</td>
                                <td>{{ $item->lote ?: '—' }}</td>
                                <td class="text-nowrap">{{ $item->validade_fmt }}</td>
                                <td>{{ $item->quantidade_entrada }}</td>
                                <td>
                                    @if($item->status === 'vencido')
                                        <span class="badge bg-danger">Vencido</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Vencendo ({{ $item->dias_restantes }}d)</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">Nenhum lote com validade no filtro selecionado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

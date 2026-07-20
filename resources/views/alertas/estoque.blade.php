@extends('layouts.app')

@section('title', 'Alertas de estoque')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-exclamation-triangle me-2"></i>Estoque baixo / crítico</h1>
            <p class="farpop-page-subtitle">Medicamentos com estoque mínimo cadastrado abaixo dos limites configurados.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('alertas.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-bell me-1"></i> Central de alertas</a>
            <a href="{{ route('relatorios.exportar', ['tipo' => 'estoque_geral', 'clinica_id' => $clinicaId ?: null]) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-csv me-1"></i> Exportar estoque</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-danger"><div class="card-body"><span class="text-muted small">Críticos</span><div class="fs-3 fw-bold text-danger">{{ $contagens['critico'] }}</div></div></div>
        </div>
        <div class="col-md-6">
            <div class="card border-warning"><div class="card-body"><span class="text-muted small">Atenção</span><div class="fs-3 fw-bold text-warning">{{ $contagens['atencao'] }}</div></div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="btn-group">
                <a href="{{ route('alertas.estoque', array_filter(['nivel' => 'todos', 'clinica_id' => $clinicaId ?: null])) }}" class="btn btn-sm {{ $nivel === 'todos' ? 'btn-primary' : 'btn-outline-primary' }}">Todos</a>
                <a href="{{ route('alertas.estoque', array_filter(['nivel' => 'critico', 'clinica_id' => $clinicaId ?: null])) }}" class="btn btn-sm {{ $nivel === 'critico' ? 'btn-danger' : 'btn-outline-danger' }}">Críticos</a>
                <a href="{{ route('alertas.estoque', array_filter(['nivel' => 'atencao', 'clinica_id' => $clinicaId ?: null])) }}" class="btn btn-sm {{ $nivel === 'atencao' ? 'btn-warning' : 'btn-outline-warning' }}">Atenção</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Unidade</th><th>Medicamento</th><th>Qtd</th><th>Mínimo</th><th>% mín.</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($itens as $it)
                    <tr>
                        <td>{{ $it->unidade }}</td>
                        <td>{{ $it->medicamento }} @if($it->dosagem)<small class="text-muted">{{ $it->dosagem }}</small>@endif</td>
                        <td>{{ $it->quantidade }}</td>
                        <td>{{ $it->estoque_minimo }}</td>
                        <td>{{ $it->percentual_minimo !== null ? $it->percentual_minimo . '%' : '—' }}</td>
                        <td><span class="badge bg-{{ $it->badge_class }}">{{ $it->badge_label }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Nenhum alerta de estoque no filtro selecionado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

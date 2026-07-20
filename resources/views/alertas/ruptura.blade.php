@extends('layouts.app')

@section('title', 'Ruptura e reposição')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-chart-line me-2"></i>Ruptura e reposição</h1>
            <p class="farpop-page-subtitle">Previsão com base no consumo dos últimos {{ $diasHistorico }} dias. Meta de cobertura: {{ $diasCobertura }} dias.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('alertas.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-bell me-1"></i> Central de alertas</a>
            <a href="{{ route('pedidos-reposicao.index', array_filter(['clinica_id' => $clinicaId ?: null])) }}" class="btn btn-primary btn-sm"><i class="fas fa-shopping-cart me-1"></i> Gerar pedido</a>
            <a href="{{ route('relatorios.exportar', ['tipo' => 'reposicao', 'clinica_id' => $clinicaId ?: null]) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-csv me-1"></i> Exportar</a>
            <a href="{{ route('relatorios.imprimir', array_filter(['tipo' => 'reposicao', 'clinica_id' => $clinicaId ?: null, 'filtro' => $filtro !== 'todos' ? $filtro : null])) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print me-1"></i> Imprimir</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-dark"><div class="card-body"><span class="text-muted small">Ruptura (zerado)</span><div class="fs-3 fw-bold">{{ $contagens['ruptura'] }}</div></div></div></div>
        <div class="col-md-4"><div class="card border-danger"><div class="card-body"><span class="text-muted small">Crítico</span><div class="fs-3 fw-bold text-danger">{{ $contagens['critico'] }}</div></div></div></div>
        <div class="col-md-4"><div class="card border-warning"><div class="card-body"><span class="text-muted small">Atenção</span><div class="fs-3 fw-bold text-warning">{{ $contagens['atencao'] }}</div></div></div></div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="btn-group flex-wrap">
                @foreach(['todos' => 'Todos', 'ruptura' => 'Ruptura', 'critico' => 'Críticos', 'atencao' => 'Atenção'] as $key => $label)
                <a href="{{ route('alertas.ruptura', array_filter(['filtro' => $key === 'todos' ? null : $key, 'clinica_id' => $clinicaId ?: null])) }}" class="btn btn-sm {{ $filtro === $key ? 'btn-primary' : 'btn-outline-primary' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Unidade</th>
                        <th>Medicamento</th>
                        <th>Estoque</th>
                        <th>Mínimo</th>
                        <th>Cons./dia</th>
                        <th>Dias rest.</th>
                        <th>Sugerido</th>
                        <th>Situação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itens as $it)
                    <tr>
                        <td>{{ $it->unidade }}</td>
                        <td>{{ $it->medicamento }} @if($it->dosagem)<small class="text-muted">{{ $it->dosagem }}</small>@endif</td>
                        <td>{{ $it->quantidade }}</td>
                        <td>{{ $it->estoque_minimo ?: '—' }}</td>
                        <td>{{ number_format($it->consumo_medio_dia, 2, ',', '.') }}</td>
                        <td>{{ $it->dias_restantes !== null ? $it->dias_restantes : '—' }}</td>
                        <td><strong>{{ $it->qtd_sugerida > 0 ? $it->qtd_sugerida : '—' }}</strong></td>
                        <td><span class="badge bg-{{ $it->badge_class }}">{{ $it->situacao_label }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Nenhum item no filtro selecionado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

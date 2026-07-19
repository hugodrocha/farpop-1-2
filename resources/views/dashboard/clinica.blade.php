@extends('layouts.app')

@section('title', $totais['clinica_nome'] . ' — Dashboard')

@section('content')
<div class="container-fluid px-0">
    <div class="farpop-hero-gradient p-4 p-md-5 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <a href="{{ route('dashboard') }}" class="text-white-50 small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Voltar ao painel</a>
                <h1 class="h3 mb-2 text-white fw-bold mt-2">{{ $totais['clinica_nome'] }}</h1>
                <p class="mb-0 opacity-75">Dashboard da unidade — estoque, dispensações e movimentações.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('estoque.index', ['clinica_id' => $totais['clinica_id']]) }}" class="btn btn-light btn-sm"><i class="fas fa-boxes me-1"></i> Ver estoque</a>
                <a href="{{ route('saidas.create') }}" class="btn btn-outline-light btn-sm"><i class="fas fa-pills me-1"></i> Nova dispensação</a>
            </div>
        </div>
    </div>

    <div class="farpop-kpi-grid">
        <div class="farpop-kpi-card kpi-1">
            <div class="kpi-label"><i class="fas fa-boxes me-1"></i> Estoque</div>
            <div class="kpi-valor">{{ number_format($totais['estoque'], 0, ',', '.') }}</div>
            <div class="kpi-hint">{{ number_format($totais['itens'], 0, ',', '.') }} item(ns)</div>
        </div>
        <div class="farpop-kpi-card kpi-2">
            <div class="kpi-label"><i class="fas fa-pills me-1"></i> Dispensado hoje</div>
            <div class="kpi-valor">{{ number_format($totais['qtd_saidas_hoje'], 0, ',', '.') }}</div>
            <div class="kpi-hint">{{ number_format($totais['pac_atendidos_hoje'], 0, ',', '.') }} paciente(s)</div>
        </div>
        <div class="farpop-kpi-card kpi-5">
            <div class="kpi-label"><i class="fas fa-exclamation-triangle me-1"></i> Críticos</div>
            <div class="kpi-valor">{{ number_format($totais['criticos'], 0, ',', '.') }}</div>
            <div class="kpi-hint">abaixo do mínimo</div>
        </div>
        <div class="farpop-kpi-card kpi-4">
            <div class="kpi-label"><i class="fas fa-arrow-down me-1"></i> Entradas hoje</div>
            <div class="kpi-valor">{{ number_format($totais['entradas_hoje'], 0, ',', '.') }}</div>
            <div class="kpi-hint">registros de entrada</div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">{!! $graficoEstoque !!}</div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">{!! $graficoDispensados !!}</div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">{!! $graficoMovimentacao !!}</div>
            </div>
        </div>
    </div>

    <h2 class="farpop-section-title">Últimas dispensações desta unidade</h2>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Paciente</th>
                            <th>Medicamento</th>
                            <th>Qtd</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimasSaidas as $saida)
                            <tr>
                                <td>{{ $saida->data_saida ? \Carbon\Carbon::parse($saida->data_saida)->format('d/m/Y H:i') : '—' }}</td>
                                <td>{{ $saida->paciente }}</td>
                                <td>{{ $saida->medicamento }} <span class="text-muted">{{ $saida->dosagem }}</span></td>
                                <td><span class="badge bg-primary">{{ number_format($saida->quantidade, 0, ',', '.') }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Nenhuma dispensação recente.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

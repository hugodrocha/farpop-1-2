@extends('layouts.app')

@section('title', 'Central de alertas')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-bell me-2"></i>Central de alertas</h1>
        <p class="farpop-page-subtitle">Visão consolidada de estoque, validade e pendências operacionais.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('alertas.estoque', ['nivel' => 'critico']) }}" class="text-decoration-none">
                <div class="card border-danger h-100">
                    <div class="card-body">
                        <div class="text-muted small">Estoque crítico</div>
                        <div class="fs-3 fw-bold text-danger">{{ $resumo['estoque_critico'] }}</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('alertas.estoque', ['nivel' => 'atencao']) }}" class="text-decoration-none">
                <div class="card border-warning h-100">
                    <div class="card-body">
                        <div class="text-muted small">Estoque em atenção</div>
                        <div class="fs-3 fw-bold text-warning">{{ $resumo['estoque_atencao'] }}</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('alertas.vencimento', ['tipo' => 'vencido']) }}" class="text-decoration-none">
                <div class="card border-danger h-100">
                    <div class="card-body">
                        <div class="text-muted small">Lotes vencidos</div>
                        <div class="fs-3 fw-bold text-danger">{{ $resumo['vencidos'] }}</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('alertas.vencimento', ['tipo' => 'vencendo']) }}" class="text-decoration-none">
                <div class="card border-warning h-100">
                    <div class="card-body">
                        <div class="text-muted small">Vencendo ({{ $resumo['dias_vencimento'] }}d)</div>
                        <div class="fs-3 fw-bold text-warning">{{ $resumo['vencendo'] }}</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('alertas.pendencias') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Transf. pendentes</div>
                        <div class="fs-3 fw-bold">{{ $resumo['transf_pendentes'] }}</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('alertas.pendencias') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Solicitações</div>
                        <div class="fs-3 fw-bold">{{ $resumo['solicitacoes_pendentes'] }}</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('reservas.index') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small">Reservas ativas</div>
                        <div class="fs-3 fw-bold">{{ $resumo['reservas_ativas'] }}</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    @if($pendencias->isNotEmpty())
    <div class="card">
        <div class="card-header bg-primary d-flex justify-content-between align-items-center">
            <span><i class="fas fa-clock me-2"></i>Pendências recentes</span>
            <a href="{{ route('alertas.pendencias') }}" class="btn btn-sm btn-light">Ver todas</a>
        </div>
        <div class="list-group list-group-flush">
            @foreach($pendencias as $p)
            <a href="{{ $p->url }}" class="list-group-item list-group-item-action">
                <div class="d-flex justify-content-between">
                    <strong>{{ $p->titulo }}</strong>
                    <small class="text-muted">{{ $p->codigo }}</small>
                </div>
                <small class="text-muted">{{ $p->detalhe }} · {{ $p->data ? \Carbon\Carbon::parse($p->data)->format('d/m/Y H:i') : '' }}</small>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

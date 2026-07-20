@extends('layouts.app')

@section('title', 'Pendências operacionais')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-clock me-2"></i>Pendências</h1>
            <p class="farpop-page-subtitle">Transferências aguardando recebimento e solicitações em andamento.</p>
        </div>
        <a href="{{ route('alertas.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-bell me-1"></i> Central de alertas</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card"><div class="card-body"><span class="text-muted small">Transferências</span><div class="fs-4 fw-bold">{{ $resumo['transf_pendentes'] }}</div></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><span class="text-muted small">Solicitações</span><div class="fs-4 fw-bold">{{ $resumo['solicitacoes_pendentes'] }}</div></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><span class="text-muted small">Reservas ativas</span><div class="fs-4 fw-bold">{{ $resumo['reservas_ativas'] }}</div></div></div></div>
    </div>

    <div class="card">
        <div class="list-group list-group-flush">
            @forelse($pendencias as $p)
            <a href="{{ $p->url }}" class="list-group-item list-group-item-action">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>{{ $p->titulo }}</strong>
                        <div class="text-muted small">{{ $p->detalhe }}</div>
                    </div>
                    <div class="text-end">
                        <code class="small">{{ $p->codigo }}</code>
                        <div class="small text-muted">{{ $p->data ? \Carbon\Carbon::parse($p->data)->format('d/m/Y H:i') : '' }}</div>
                    </div>
                </div>
            </a>
            @empty
            <div class="list-group-item text-muted text-center py-4">Nenhuma pendência no momento.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

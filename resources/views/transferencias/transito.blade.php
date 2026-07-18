@extends('layouts.app')

@section('title', 'Transferências em trânsito')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-route me-2"></i>Em trânsito</h1>
            <p class="farpop-page-subtitle">Transferências pendentes de recebimento.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if(in_array(session('perfil'), ['superuser', 'admin', 'gestor', 'farmaceutico']))
                <a href="{{ route('transferencias.receber') }}" class="btn btn-primary"><i class="fas fa-barcode me-1"></i> Receber nota</a>
            @endif
            <a href="{{ route('transferencias.create') }}" class="btn btn-outline-primary"><i class="fas fa-plus me-1"></i> Enviar lote</a>
            <a href="{{ route('transferencias.index') }}" class="btn btn-outline-secondary"><i class="fas fa-history me-1"></i> Histórico</a>
        </div>
    </div>

    @include('partials.flash')

    <div class="farpop-kpi-grid mb-4" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
        <div class="farpop-kpi-card kpi-3">
            <div class="kpi-label">Pendentes</div>
            <div class="kpi-valor">{{ number_format($transferencias->count(), 0, ',', '.') }}</div>
            <div class="kpi-hint">aguardando confirmação</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Enviado em</th>
                            <th>Origem</th>
                            <th>Destino</th>
                            <th>Itens</th>
                            <th>Enviado por</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transferencias as $t)
                            <tr>
                                <td><code>{{ $t->codigo_nota ?: $t->id }}</code></td>
                                <td class="text-nowrap">{{ $t->enviado_em ? \Carbon\Carbon::parse($t->enviado_em)->format('d/m/Y H:i') : '—' }}</td>
                                <td>{{ $t->clinica_origem }}</td>
                                <td>{{ $t->clinica_destino }}</td>
                                <td><span class="badge bg-secondary">{{ $t->itens_count ?? 0 }}</span></td>
                                <td>{{ $t->usuario_nome ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('transferencias.nota', $t->id) }}" class="btn btn-sm btn-outline-primary" title="Ver nota">
                                        <i class="fas fa-file-alt"></i>
                                    </a>
                                    @if(in_array(session('perfil'), ['superuser', 'admin', 'gestor', 'farmaceutico']))
                                        <a href="{{ route('transferencias.receber', ['codigo' => $t->codigo_nota ?: $t->id]) }}" class="btn btn-sm btn-primary" title="Receber">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-truck fa-2x mb-2 d-block opacity-50"></i>
                                    Nenhuma transferência pendente no momento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

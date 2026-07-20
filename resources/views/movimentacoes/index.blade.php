@extends('layouts.app')

@section('title', 'Movimentações de estoque')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex flex-wrap justify-content-between gap-3">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-history me-2"></i>Histórico de movimentações</h1>
            <p class="farpop-page-subtitle">Registro auditável por lote (últimas 300).</p>
        </div>
        <a href="{{ route('lotes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-layer-group me-1"></i> Lotes</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Unidade</th>
                            <th>Medicamento</th>
                            <th>Lote</th>
                            <th>Qtd</th>
                            <th>Saldo após</th>
                            <th>Usuário</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimentacoes as $m)
                            <tr>
                                <td class="text-nowrap">{{ $m->criado_em ? \Carbon\Carbon::parse($m->criado_em)->format('d/m/Y H:i') : '—' }}</td>
                                <td><span class="badge bg-secondary">{{ $tipos[$m->tipo] ?? $m->tipo }}</span></td>
                                <td>{{ $m->clinica }}</td>
                                <td>{{ $m->medicamento }}</td>
                                <td>{{ $m->lote ?: '—' }}</td>
                                <td class="{{ $m->quantidade < 0 ? 'text-danger' : 'text-success' }}">{{ $m->quantidade > 0 ? '+' : '' }}{{ $m->quantidade }}</td>
                                <td>{{ $m->saldo_lote_apos ?? '—' }}</td>
                                <td class="small">{{ $m->usuario ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-5">Nenhuma movimentação registrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

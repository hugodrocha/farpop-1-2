@extends('layouts.app')

@section('title', 'Estoque')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-boxes me-2"></i>Estoque</h1>
        <p class="farpop-page-subtitle">Consulta de saldo por unidade e medicamento.</p>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Clínica</th>
                            <th>Medicamento</th>
                            <th>Status</th>
                            <th>Quantidade</th>
                            <th>Atualizado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itens as $item)
                            <tr>
                                <td>{{ $item->clinica_nome }}</td>
                                <td class="fw-medium">{{ $item->medicamento_nome }} {{ $item->dosagem }} <span class="text-muted">{{ $item->unidade }}</span></td>
                                <td><span class="badge bg-{{ $item->badge_class }}">{{ $item->badge_label }}</span></td>
                                <td><span class="badge bg-primary fs-6">{{ number_format($item->quantidade, 0, ',', '.') }}</span></td>
                                <td class="text-muted">{{ $item->atualizado_em ? \Carbon\Carbon::parse($item->atualizado_em)->format('d/m/Y H:i') : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Nenhum item em estoque.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Inventário')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-clipboard-check me-2"></i>Inventário por lote</h1>
        <p class="farpop-page-subtitle">Informe a quantidade contada fisicamente. O sistema gera ajuste automático quando houver diferença.</p>
    </div>

    @include('partials.flash')

    <form method="POST" action="{{ route('inventario.store') }}">
        @csrf
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Medicamento</th>
                                <th>Lote</th>
                                <th>Validade</th>
                                <th>Sistema</th>
                                <th style="width:140px">Contagem física</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lotes as $l)
                                <tr>
                                    <td>{{ $l->medicamento }} <span class="text-muted small">({{ $l->clinica }})</span></td>
                                    <td>{{ $l->lote ?: '—' }}</td>
                                    <td>{{ $l->validade_fmt ?: '—' }}</td>
                                    <td><strong>{{ $l->quantidade }}</strong></td>
                                    <td>
                                        <input type="number" name="contagem[{{ $l->id }}]" class="form-control form-control-sm" min="0" placeholder="{{ $l->quantidade }}">
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-5">Nenhum lote cadastrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($lotes->isNotEmpty())
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('lotes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Salvar inventário</button>
            </div>
            @endif
        </div>
    </form>
</div>
@endsection

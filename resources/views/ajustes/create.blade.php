@extends('layouts.app')

@section('title', 'Ajuste de estoque')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-sliders-h me-2"></i>Ajuste de lote</h1>
        <p class="farpop-page-subtitle">Perda, vencimento, devolução ou ajuste manual em um lote específico.</p>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('ajustes.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Lote</label>
                        <select name="lote_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            @foreach($lotes as $l)
                                <option value="{{ $l->id }}">{{ $l->clinica }} — {{ $l->medicamento }} | Lote {{ $l->lote ?: '—' }} | Val. {{ $l->validade_fmt ?: '—' }} | Saldo {{ $l->quantidade }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="ajuste_estoque">Ajuste (+/-)</option>
                            <option value="perda">Perda</option>
                            <option value="vencimento">Vencimento</option>
                            <option value="devolucao">Devolução (+)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Quantidade</label>
                        <input type="number" name="quantidade" class="form-control" required placeholder="Use negativo p/ baixa em ajuste">
                        <div class="form-text">Perda/vencimento: informe quantidade positiva.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observação</label>
                        <input type="text" name="observacao" class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Registrar</button>
                    <a href="{{ route('lotes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

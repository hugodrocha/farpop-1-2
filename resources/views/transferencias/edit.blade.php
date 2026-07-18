@extends('layouts.app')

@section('title', 'Editar transferência')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-edit me-2"></i>Editar transferência</h1>
        <p class="farpop-page-subtitle">Atualizar dados da transferência #{{ $transferencia->id }}.</p>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('transferencias.update', $transferencia->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Clínica de origem</label><select name="clinica_origem_id" class="form-select" required>@foreach($clinicas as $clinica)<option value="{{ $clinica->id }}" {{ (old('clinica_origem_id', $transferencia->clinica_origem_id) == $clinica->id) ? 'selected' : '' }}>{{ $clinica->nome }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label">Clínica de destino</label><select name="clinica_destino_id" class="form-select" required>@foreach($clinicas as $clinica)<option value="{{ $clinica->id }}" {{ (old('clinica_destino_id', $transferencia->clinica_destino_id) == $clinica->id) ? 'selected' : '' }}>{{ $clinica->nome }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label">Medicamento</label><select name="medicamento_id" class="form-select" required>@foreach($medicamentos as $medicamento)<option value="{{ $medicamento->id }}" {{ (old('medicamento_id', $item->medicamento_id ?? null) == $medicamento->id) ? 'selected' : '' }}>{{ $medicamento->nome }} {{ $medicamento->dosagem }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="form-label">Quantidade</label><input type="number" name="quantidade" class="form-control" value="{{ old('quantidade', $item->quantidade_enviada ?? 1) }}" min="1" required></div>
                    <div class="col-md-12"><label class="form-label">Observação</label><input type="text" name="observacao" class="form-control" value="{{ old('observacao', $transferencia->observacao) }}"></div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i> Salvar</button>
                    <a class="btn btn-outline-secondary" href="{{ route('transferencias.index') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

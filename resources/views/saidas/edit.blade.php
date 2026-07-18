@extends('layouts.app')

@section('title', 'Editar saída')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-edit me-2"></i>Editar saída</h1>
        <p class="farpop-page-subtitle">Atualizar registro de dispensação.</p>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('saidas.update', $saida->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">CPF</label><input type="text" name="paciente_cpf" class="form-control" value="{{ old('paciente_cpf', $paciente->cpf ?? '') }}" maxlength="11" required></div>
                    <div class="col-md-5"><label class="form-label">Nome do paciente</label><input type="text" name="paciente_nome" class="form-control" value="{{ old('paciente_nome', $paciente->nome ?? '') }}" required></div>
                    <div class="col-md-4"><label class="form-label">Prescritor</label><input type="text" name="prescritor" class="form-control" value="{{ old('prescritor', $saida->prescritor) }}"></div>
                    <div class="col-md-4"><label class="form-label">Clínica</label><select name="clinica_id" class="form-select" required>@foreach($clinicas as $clinica)<option value="{{ $clinica->id }}" {{ (old('clinica_id', $saida->clinica_id) == $clinica->id) ? 'selected' : '' }}>{{ $clinica->nome }}</option>@endforeach</select></div>
                    <div class="col-md-5"><label class="form-label">Medicamento</label><select name="medicamento_id" class="form-select" required>@foreach($medicamentos as $medicamento)<option value="{{ $medicamento->id }}" {{ (old('medicamento_id', $saida->medicamento_id) == $medicamento->id) ? 'selected' : '' }}>{{ $medicamento->nome }} {{ $medicamento->dosagem }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="form-label">Quantidade</label><input type="number" name="quantidade" class="form-control" value="{{ old('quantidade', $saida->quantidade) }}" min="1" required></div>
                    <div class="col-md-4"><label class="form-label">Nº solicitação</label><input type="text" name="numero_solicitacao" class="form-control" value="{{ old('numero_solicitacao', $saida->numero_solicitacao) }}"></div>
                    <div class="col-md-8"><label class="form-label">Observação</label><input type="text" name="observacao" class="form-control" value="{{ old('observacao', $saida->observacao) }}"></div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i> Salvar</button>
                    <a class="btn btn-outline-secondary" href="{{ route('saidas.index') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

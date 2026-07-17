@extends('layouts.app')

@section('title', 'Editar entrada')

@section('content')
<div class="card">
    <h2>Editar entrada</h2>
    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('entradas.update', $entrada->id) }}">
        @csrf
        @method('PUT')
        <div class="form-grid">
            <div><label>Clínica</label><select name="clinica_id" required>@foreach($clinicas as $clinica)<option value="{{ $clinica->id }}" {{ $entrada->clinica_id == $clinica->id ? 'selected' : '' }}>{{ $clinica->nome }}</option>@endforeach</select></div>
            <div><label>Medicamento</label><select name="medicamento_id" required>@foreach($medicamentos as $medicamento)<option value="{{ $medicamento->id }}" {{ $entrada->medicamento_id == $medicamento->id ? 'selected' : '' }}>{{ $medicamento->nome }} {{ $medicamento->dosagem }}</option>@endforeach</select></div>
            <div><label>Quantidade</label><input type="number" name="quantidade" value="{{ $entrada->quantidade }}" min="1" required></div>
            <div><label>Lote</label><input type="text" name="lote" value="{{ $entrada->lote }}"></div>
            <div><label>Validade</label><input type="text" name="validade" value="{{ $entrada->validade }}"></div>
            <div><label>Nota fiscal</label><input type="text" name="nota_fiscal" value="{{ $entrada->nota_fiscal }}"></div>
            <div><label>Origem</label><input type="text" name="origem" value="{{ $entrada->origem }}"></div>
            <div><label>Observação</label><input type="text" name="observacao" value="{{ $entrada->observacao }}"></div>
        </div>
        <div class="actions">
            <button class="btn" type="submit">Salvar</button>
            <a class="btn" href="{{ route('entradas.index') }}">Cancelar</a>
        </div>
    </form>
</div>
@endsection

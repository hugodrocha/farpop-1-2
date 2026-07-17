@extends('layouts.app')

@section('title', 'Nova entrada')

@section('content')
<div class="card">
    <h2>Registrar entrada</h2>
    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('entradas.store') }}">
        @csrf
        <div class="form-grid">
            <div><label>Clínica</label><select name="clinica_id" required>@foreach($clinicas as $clinica)<option value="{{ $clinica->id }}">{{ $clinica->nome }}</option>@endforeach</select></div>
            <div><label>Medicamento</label><select name="medicamento_id" required>@foreach($medicamentos as $medicamento)<option value="{{ $medicamento->id }}">{{ $medicamento->nome }} {{ $medicamento->dosagem }}</option>@endforeach</select></div>
            <div><label>Quantidade</label><input type="number" name="quantidade" value="1" min="1" required></div>
            <div><label>Lote</label><input type="text" name="lote"></div>
            <div><label>Validade</label><input type="text" name="validade"></div>
            <div><label>Nota fiscal</label><input type="text" name="nota_fiscal"></div>
            <div><label>Origem</label><input type="text" name="origem"></div>
            <div><label>Observação</label><input type="text" name="observacao"></div>
        </div>
        <div class="actions">
            <button class="btn" type="submit">Salvar</button>
            <a class="btn" href="{{ route('entradas.index') }}">Cancelar</a>
        </div>
    </form>
</div>
@endsection

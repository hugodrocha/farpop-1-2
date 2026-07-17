@extends('layouts.app')

@section('title', 'Editar medicamento')

@section('content')
<div class="card">
    <h2>Editar medicamento</h2>
    <form method="POST" action="{{ route('medicamentos.update', $medicamento->id) }}">
        @csrf
        @method('PUT')
        <div class="form-grid">
            <div><label>Nome</label><input type="text" name="nome" value="{{ $medicamento->nome }}" required></div>
            <div><label>Princípio ativo</label><input type="text" name="principio_ativo" value="{{ $medicamento->principio_ativo }}"></div>
            <div><label>Forma farmacêutica</label><input type="text" name="forma_farmaceutica" value="{{ $medicamento->forma_farmaceutica }}"></div>
            <div><label>Dosagem</label><input type="text" name="dosagem" value="{{ $medicamento->dosagem }}"></div>
            <div><label>Unidade</label><input type="text" name="unidade" value="{{ $medicamento->unidade }}"></div>
            <div><label>Código</label><input type="text" name="codigo" value="{{ $medicamento->codigo }}"></div>
            <div><label>Código de barras</label><input type="text" name="codigo_barras" value="{{ $medicamento->codigo_barras }}"></div>
            <div><label>Estoque mínimo</label><input type="number" name="estoque_minimo" value="{{ $medicamento->estoque_minimo }}"></div>
            <div><label>Controlado</label><select name="controlado"><option value="0" {{ $medicamento->controlado ? '' : 'selected' }}>Não</option><option value="1" {{ $medicamento->controlado ? 'selected' : '' }}>Sim</option></select></div>
            <div><label>Status</label><select name="ativo"><option value="1" {{ $medicamento->ativo ? 'selected' : '' }}>Ativo</option><option value="0" {{ $medicamento->ativo ? '' : 'selected' }}>Inativo</option></select></div>
        </div>
        <div class="actions">
            <button class="btn" type="submit">Salvar</button>
            <a class="btn" href="{{ route('medicamentos.index') }}">Cancelar</a>
        </div>
    </form>
</div>
@endsection

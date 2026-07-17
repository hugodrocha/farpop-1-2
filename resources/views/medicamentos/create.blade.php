@extends('layouts.app')

@section('title', 'Novo medicamento')

@section('content')
<div class="card">
    <h2>Novo medicamento</h2>
    <form method="POST" action="{{ route('medicamentos.store') }}">
        @csrf
        <div class="form-grid">
            <div><label>Nome</label><input type="text" name="nome" required></div>
            <div><label>Princípio ativo</label><input type="text" name="principio_ativo"></div>
            <div><label>Forma farmacêutica</label><input type="text" name="forma_farmaceutica"></div>
            <div><label>Dosagem</label><input type="text" name="dosagem"></div>
            <div><label>Unidade</label><input type="text" name="unidade" value="UNIDADE"></div>
            <div><label>Código</label><input type="text" name="codigo"></div>
            <div><label>Código de barras</label><input type="text" name="codigo_barras"></div>
            <div><label>Estoque mínimo</label><input type="number" name="estoque_minimo" value="0"></div>
            <div><label>Controlado</label><select name="controlado"><option value="0">Não</option><option value="1">Sim</option></select></div>
            <div><label>Status</label><select name="ativo"><option value="1">Ativo</option><option value="0">Inativo</option></select></div>
        </div>
        <div class="actions">
            <button class="btn" type="submit">Salvar</button>
            <a class="btn" href="{{ route('medicamentos.index') }}">Cancelar</a>
        </div>
    </form>
</div>
@endsection

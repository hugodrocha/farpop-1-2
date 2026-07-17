@extends('layouts.app')

@section('title', 'Nova clínica')

@section('content')
<div class="card">
    <h2>Nova clínica</h2>
    <form method="POST" action="{{ route('clinicas.store') }}">
        @csrf
        <div class="form-grid">
            <div><label>Nome</label><input type="text" name="nome" required></div>
            <div><label>Responsável</label><input type="text" name="responsavel"></div>
            <div><label>Endereço</label><input type="text" name="endereco"></div>
            <div><label>Bairro</label><input type="text" name="bairro"></div>
            <div><label>Telefone</label><input type="text" name="telefone"></div>
            <div><label>Central</label><select name="eh_central"><option value="0">Não</option><option value="1">Sim</option></select></div>
            <div><label>Status</label><select name="ativo"><option value="1">Ativa</option><option value="0">Inativa</option></select></div>
        </div>
        <div class="actions">
            <button class="btn" type="submit">Salvar</button>
            <a class="btn" href="{{ route('clinicas.index') }}">Cancelar</a>
        </div>
    </form>
</div>
@endsection

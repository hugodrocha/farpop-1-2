@extends('layouts.app')

@section('title', 'Editar clínica')

@section('content')
<div class="card">
    <h2>Editar clínica</h2>
    <form method="POST" action="{{ route('clinicas.update', $clinica->id) }}">
        @csrf
        @method('PUT')
        <div class="form-grid">
            <div><label>Nome</label><input type="text" name="nome" value="{{ $clinica->nome }}" required></div>
            <div><label>Responsável</label><input type="text" name="responsavel" value="{{ $clinica->responsavel }}"></div>
            <div><label>Endereço</label><input type="text" name="endereco" value="{{ $clinica->endereco }}"></div>
            <div><label>Bairro</label><input type="text" name="bairro" value="{{ $clinica->bairro }}"></div>
            <div><label>Telefone</label><input type="text" name="telefone" value="{{ $clinica->telefone }}"></div>
            <div><label>Central</label><select name="eh_central"><option value="0" {{ $clinica->eh_central ? '' : 'selected' }}>Não</option><option value="1" {{ $clinica->eh_central ? 'selected' : '' }}>Sim</option></select></div>
            <div><label>Status</label><select name="ativo"><option value="1" {{ $clinica->ativo ? 'selected' : '' }}>Ativa</option><option value="0" {{ $clinica->ativo ? '' : 'selected' }}>Inativa</option></select></div>
        </div>
        <div class="actions">
            <button class="btn" type="submit">Salvar</button>
            <a class="btn" href="{{ route('clinicas.index') }}">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Novo usuário')

@section('content')
<div class="card">
    <h2>Novo usuário</h2>
    <form method="POST" action="{{ route('usuarios.store') }}">
        @csrf
        <div class="form-grid">
            <div><label>Nome completo</label><input type="text" name="nome_completo" required></div>
            <div><label>CPF</label><input type="text" name="cpf"></div>
            <div><label>Cargo</label><input type="text" name="cargo"></div>
            <div><label>Email</label><input type="email" name="email"></div>
            <div><label>Usuário</label><input type="text" name="usuario" required></div>
            <div><label>Senha</label><input type="password" name="senha" required></div>
            <div><label>Perfil</label><select name="perfil"><option value="tecnico">Técnico</option><option value="farmaceutico">Farmacêutico</option><option value="admin">Administrador</option><option value="gestor">Gestor</option><option value="superuser">Super Usuário</option><option value="consulta">Consulta</option></select></div>
            <div><label>Clínica</label><select name="clinica_id"><option value="">Sem clínica</option>@foreach($clinicas as $clinica)<option value="{{ $clinica->id }}">{{ $clinica->nome }}</option>@endforeach</select></div>
            <div><label>Bloqueado</label><select name="bloqueado"><option value="0">Não</option><option value="1">Sim</option></select></div>
        </div>
        <div class="actions">
            <button class="btn" type="submit">Salvar</button>
            <a class="btn" href="{{ route('usuarios.index') }}">Cancelar</a>
        </div>
    </form>
</div>
@endsection

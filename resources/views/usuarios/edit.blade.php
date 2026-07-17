@extends('layouts.app')

@section('title', 'Editar usuário')

@section('content')
<div class="card">
    <h2>Editar usuário</h2>
    <form method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
        @csrf
        @method('PUT')
        <div class="form-grid">
            <div><label>Nome completo</label><input type="text" name="nome_completo" value="{{ $usuario->nome_completo }}" required></div>
            <div><label>CPF</label><input type="text" name="cpf" value="{{ $usuario->cpf }}"></div>
            <div><label>Cargo</label><input type="text" name="cargo" value="{{ $usuario->cargo }}"></div>
            <div><label>Email</label><input type="email" name="email" value="{{ $usuario->email }}"></div>
            <div><label>Usuário</label><input type="text" name="usuario" value="{{ $usuario->usuario }}" required></div>
            <div><label>Nova senha</label><input type="password" name="senha"></div>
            <div><label>Perfil</label><select name="perfil"><option value="tecnico" {{ $usuario->perfil == 'tecnico' ? 'selected' : '' }}>Técnico</option><option value="farmaceutico" {{ $usuario->perfil == 'farmaceutico' ? 'selected' : '' }}>Farmacêutico</option><option value="admin" {{ $usuario->perfil == 'admin' ? 'selected' : '' }}>Administrador</option><option value="gestor" {{ $usuario->perfil == 'gestor' ? 'selected' : '' }}>Gestor</option><option value="superuser" {{ $usuario->perfil == 'superuser' ? 'selected' : '' }}>Super Usuário</option><option value="consulta" {{ $usuario->perfil == 'consulta' ? 'selected' : '' }}>Consulta</option></select></div>
            <div><label>Clínica</label><select name="clinica_id"><option value="">Sem clínica</option>@foreach($clinicas as $clinica)<option value="{{ $clinica->id }}" {{ $usuario->clinica_id == $clinica->id ? 'selected' : '' }}>{{ $clinica->nome }}</option>@endforeach</select></div>
            <div><label>Bloqueado</label><select name="bloqueado"><option value="0" {{ $usuario->bloqueado ? '' : 'selected' }}>Não</option><option value="1" {{ $usuario->bloqueado ? 'selected' : '' }}>Sim</option></select></div>
        </div>
        <div class="actions">
            <button class="btn" type="submit">Salvar</button>
            <a class="btn" href="{{ route('usuarios.index') }}">Cancelar</a>
        </div>
    </form>
</div>
@endsection

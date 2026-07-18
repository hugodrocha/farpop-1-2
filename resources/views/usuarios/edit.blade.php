@extends('layouts.app')

@section('title', 'Editar usuário')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-user-edit me-2"></i>Editar usuário</h1>
        <p class="farpop-page-subtitle">{{ $usuario->nome_completo }}</p>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('usuarios.update', $usuario->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nome completo</label><input type="text" name="nome_completo" class="form-control" value="{{ $usuario->nome_completo }}" required></div>
                    <div class="col-md-3"><label class="form-label">CPF</label><input type="text" name="cpf" class="form-control" value="{{ $usuario->cpf }}"></div>
                    <div class="col-md-3"><label class="form-label">Cargo</label><input type="text" name="cargo" class="form-control" value="{{ $usuario->cargo }}"></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ $usuario->email }}"></div>
                    <div class="col-md-3"><label class="form-label">Usuário</label><input type="text" name="usuario" class="form-control" value="{{ $usuario->usuario }}" required></div>
                    <div class="col-md-3"><label class="form-label">Nova senha</label><input type="password" name="senha" class="form-control" placeholder="Deixe em branco para manter"></div>
                    <div class="col-md-4"><label class="form-label">Perfil</label><select name="perfil" class="form-select"><option value="tecnico" {{ $usuario->perfil == 'tecnico' ? 'selected' : '' }}>Técnico</option><option value="farmaceutico" {{ $usuario->perfil == 'farmaceutico' ? 'selected' : '' }}>Farmacêutico</option><option value="admin" {{ $usuario->perfil == 'admin' ? 'selected' : '' }}>Administrador</option><option value="gestor" {{ $usuario->perfil == 'gestor' ? 'selected' : '' }}>Gestor</option><option value="superuser" {{ $usuario->perfil == 'superuser' ? 'selected' : '' }}>Super Usuário</option><option value="consulta" {{ $usuario->perfil == 'consulta' ? 'selected' : '' }}>Consulta</option></select></div>
                    <div class="col-md-4"><label class="form-label">Clínica</label><select name="clinica_id" class="form-select"><option value="">Sem clínica</option>@foreach($clinicas as $clinica)<option value="{{ $clinica->id }}" {{ $usuario->clinica_id == $clinica->id ? 'selected' : '' }}>{{ $clinica->nome }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Bloqueado</label><select name="bloqueado" class="form-select"><option value="0" {{ $usuario->bloqueado ? '' : 'selected' }}>Não</option><option value="1" {{ $usuario->bloqueado ? 'selected' : '' }}>Sim</option></select></div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i> Salvar</button>
                    <a class="btn btn-outline-secondary" href="{{ route('usuarios.index') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

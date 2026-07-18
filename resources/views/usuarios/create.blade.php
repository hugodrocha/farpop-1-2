@extends('layouts.app')

@section('title', 'Novo usuário')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-user-plus me-2"></i>Novo usuário</h1>
        <p class="farpop-page-subtitle">Cadastre uma nova conta de acesso.</p>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('usuarios.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nome completo</label><input type="text" name="nome_completo" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">CPF</label><input type="text" name="cpf" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Cargo</label><input type="text" name="cargo" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Usuário</label><input type="text" name="usuario" class="form-control" required></div>
                    <div class="col-md-3"><label class="form-label">Senha</label><input type="password" name="senha" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Perfil</label><select name="perfil" class="form-select"><option value="tecnico">Técnico</option><option value="farmaceutico">Farmacêutico</option><option value="admin">Administrador</option><option value="gestor">Gestor</option><option value="superuser">Super Usuário</option><option value="consulta">Consulta</option></select></div>
                    <div class="col-md-4"><label class="form-label">Clínica</label><select name="clinica_id" class="form-select"><option value="">Sem clínica</option>@foreach($clinicas as $clinica)<option value="{{ $clinica->id }}">{{ $clinica->nome }}</option>@endforeach</select></div>
                    <div class="col-md-4"><label class="form-label">Bloqueado</label><select name="bloqueado" class="form-select"><option value="0">Não</option><option value="1">Sim</option></select></div>
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

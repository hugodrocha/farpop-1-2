@extends('layouts.app')

@section('title', 'Trocar senha')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-key me-2"></i>Trocar senha</h1>
        <p class="farpop-page-subtitle">Altere a senha da sua conta (6 a 12 caracteres).</p>
    </div>

    @include('partials.flash')

    @if($errors->has('senha_atual'))
        <div class="alert alert-danger mb-4">{{ $errors->first('senha_atual') }}</div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-primary"><i class="fas fa-lock me-2"></i>Nova senha</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('trocar-senha.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Senha atual</label>
                            <input type="password" name="senha_atual" class="form-control" required autocomplete="current-password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nova senha</label>
                            <input type="password" name="nova_senha" class="form-control" required minlength="6" maxlength="12" autocomplete="new-password">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Confirmar nova senha</label>
                            <input type="password" name="nova_senha_confirmation" class="form-control" required minlength="6" maxlength="12" autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-check me-1"></i> Alterar senha</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

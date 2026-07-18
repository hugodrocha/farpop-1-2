@extends('layouts.app')

@section('title', 'Editar clínica')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-hospital me-2"></i>Editar clínica</h1>
        <p class="farpop-page-subtitle">{{ $clinica->nome }}</p>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('clinicas.update', $clinica->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nome</label><input type="text" name="nome" class="form-control" value="{{ $clinica->nome }}" required></div>
                    <div class="col-md-6"><label class="form-label">Responsável</label><input type="text" name="responsavel" class="form-control" value="{{ $clinica->responsavel }}"></div>
                    <div class="col-md-8"><label class="form-label">Endereço</label><input type="text" name="endereco" class="form-control" value="{{ $clinica->endereco }}"></div>
                    <div class="col-md-4"><label class="form-label">Bairro</label><input type="text" name="bairro" class="form-control" value="{{ $clinica->bairro }}"></div>
                    <div class="col-md-4"><label class="form-label">Telefone</label><input type="text" name="telefone" class="form-control" value="{{ $clinica->telefone }}"></div>
                    <div class="col-md-4"><label class="form-label">Central</label><select name="eh_central" class="form-select"><option value="0" {{ $clinica->eh_central ? '' : 'selected' }}>Não</option><option value="1" {{ $clinica->eh_central ? 'selected' : '' }}>Sim</option></select></div>
                    <div class="col-md-4"><label class="form-label">Status</label><select name="ativo" class="form-select"><option value="1" {{ $clinica->ativo ? 'selected' : '' }}>Ativa</option><option value="0" {{ $clinica->ativo ? '' : 'selected' }}>Inativa</option></select></div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i> Salvar</button>
                    <a class="btn btn-outline-secondary" href="{{ route('clinicas.index') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

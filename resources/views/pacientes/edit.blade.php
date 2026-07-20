@extends('layouts.app')

@section('title', 'Editar paciente')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-user-edit me-2"></i>Editar paciente</h1>
        <p class="farpop-page-subtitle">{{ $paciente->nome }}</p>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('pacientes.update', $paciente->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nome completo</label><input type="text" name="nome" class="form-control" value="{{ old('nome', $paciente->nome) }}" required></div>
                    <div class="col-md-3"><label class="form-label">CPF</label><input type="text" name="cpf" class="form-control" value="{{ old('cpf', $paciente->cpf ? \App\Services\SaidaService::formatarCpf($paciente->cpf) : '') }}"></div>
                    <div class="col-md-3"><label class="form-label">CNS (cartão SUS)</label><input type="text" name="cartao_sus" class="form-control" value="{{ old('cartao_sus', $paciente->cartao_sus) }}"></div>
                    <div class="col-md-3"><label class="form-label">Data de nascimento</label><input type="date" name="data_nascimento" class="form-control" value="{{ old('data_nascimento', $paciente->data_nascimento) }}"></div>
                    <div class="col-md-3"><label class="form-label">Telefone</label><input type="text" name="telefone" class="form-control" value="{{ old('telefone', $paciente->telefone) }}"></div>
                    <div class="col-md-6"><label class="form-label">Endereço</label><input type="text" name="endereco" class="form-control" value="{{ old('endereco', $paciente->endereco) }}"></div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i> Salvar</button>
                    <a class="btn btn-outline-secondary" href="{{ route('pacientes.show', $paciente->id) }}">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

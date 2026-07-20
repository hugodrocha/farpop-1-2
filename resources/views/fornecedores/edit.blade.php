@extends('layouts.app')
@section('title', 'Editar fornecedor')
@section('content')
<div class="container-fluid px-0" style="max-width:720px">
    <h1 class="farpop-page-title mb-4">Editar fornecedor</h1>
    @include('partials.flash')
    <form method="POST" action="{{ route('fornecedores.update', $fornecedor->id) }}" class="card"><div class="card-body">
        @csrf @method('PUT')
        <div class="mb-3"><label class="form-label">Nome *</label><input name="nome" class="form-control" required value="{{ old('nome', $fornecedor->nome) }}"></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">CNPJ</label><input name="cnpj" class="form-control" value="{{ old('cnpj', $fornecedor->cnpj) }}"></div>
            <div class="col-md-6"><label class="form-label">Telefone</label><input name="telefone" class="form-control" value="{{ old('telefone', $fornecedor->telefone) }}"></div>
            <div class="col-12"><label class="form-label">E-mail</label><input type="email" name="email" class="form-control" value="{{ old('email', $fornecedor->email) }}"></div>
            <div class="col-12"><label class="form-label">Endereço</label><input name="endereco" class="form-control" value="{{ old('endereco', $fornecedor->endereco) }}"></div>
            <div class="col-12"><div class="form-check"><input type="checkbox" name="ativo" value="1" class="form-check-input" id="ativo" {{ old('ativo', $fornecedor->ativo) ? 'checked' : '' }}><label class="form-check-label" for="ativo">Ativo</label></div></div>
        </div>
        <div class="mt-3 text-end"><a href="{{ route('fornecedores.index') }}" class="btn btn-outline-secondary">Cancelar</a> <button class="btn btn-primary">Salvar</button></div>
    </div></form>
</div>
@endsection

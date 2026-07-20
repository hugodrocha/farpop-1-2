@extends('layouts.app')
@section('title', 'Novo fabricante')
@section('content')
<div class="container-fluid px-0" style="max-width:560px">
    <h1 class="farpop-page-title mb-4">Novo fabricante</h1>
    @include('partials.flash')
    <form method="POST" action="{{ route('fabricantes.store') }}" class="card"><div class="card-body">@csrf
        <div class="mb-3"><label class="form-label">Nome *</label><input name="nome" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">CNPJ</label><input name="cnpj" class="form-control"></div>
        <div class="form-check mb-3"><input type="checkbox" name="ativo" value="1" class="form-check-input" id="ativo" checked><label for="ativo" class="form-check-label">Ativo</label></div>
        <div class="text-end"><a href="{{ route('fabricantes.index') }}" class="btn btn-outline-secondary">Cancelar</a> <button class="btn btn-primary">Salvar</button></div>
    </div></form>
</div>
@endsection

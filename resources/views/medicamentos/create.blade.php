@extends('layouts.app')

@section('title', 'Novo medicamento')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-capsules me-2"></i>Novo medicamento</h1>
        <p class="farpop-page-subtitle">Cadastre um item no catálogo de medicamentos.</p>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('medicamentos.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nome</label><input type="text" name="nome" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Princípio ativo</label><input type="text" name="principio_ativo" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Forma farmacêutica</label><input type="text" name="forma_farmaceutica" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Dosagem</label><input type="text" name="dosagem" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Unidade</label><input type="text" name="unidade" class="form-control" value="UNIDADE"></div>
                    <div class="col-md-4"><label class="form-label">Código</label><input type="text" name="codigo" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Código de barras</label><input type="text" name="codigo_barras" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Estoque mínimo</label><input type="number" name="estoque_minimo" class="form-control" value="0"></div>
                    <div class="col-md-4"><label class="form-label">Controlado</label><select name="controlado" class="form-select"><option value="0">Não</option><option value="1">Sim</option></select></div>
                    <div class="col-md-4"><label class="form-label">Status</label><select name="ativo" class="form-select"><option value="1">Ativo</option><option value="0">Inativo</option></select></div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i> Salvar</button>
                    <a class="btn btn-outline-secondary" href="{{ route('medicamentos.index') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

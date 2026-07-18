@extends('layouts.app')

@section('title', 'Editar medicamento')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-capsules me-2"></i>Editar medicamento</h1>
        <p class="farpop-page-subtitle">{{ $medicamento->nome }}</p>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('medicamentos.update', $medicamento->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nome</label><input type="text" name="nome" class="form-control" value="{{ $medicamento->nome }}" required></div>
                    <div class="col-md-6"><label class="form-label">Princípio ativo</label><input type="text" name="principio_ativo" class="form-control" value="{{ $medicamento->principio_ativo }}"></div>
                    <div class="col-md-4"><label class="form-label">Forma farmacêutica</label><input type="text" name="forma_farmaceutica" class="form-control" value="{{ $medicamento->forma_farmaceutica }}"></div>
                    <div class="col-md-4"><label class="form-label">Dosagem</label><input type="text" name="dosagem" class="form-control" value="{{ $medicamento->dosagem }}"></div>
                    <div class="col-md-4"><label class="form-label">Unidade</label><input type="text" name="unidade" class="form-control" value="{{ $medicamento->unidade }}"></div>
                    <div class="col-md-4"><label class="form-label">Código</label><input type="text" name="codigo" class="form-control" value="{{ $medicamento->codigo }}"></div>
                    <div class="col-md-4"><label class="form-label">Código de barras</label><input type="text" name="codigo_barras" class="form-control" value="{{ $medicamento->codigo_barras }}"></div>
                    <div class="col-md-4"><label class="form-label">Estoque mínimo</label><input type="number" name="estoque_minimo" class="form-control" value="{{ $medicamento->estoque_minimo }}"></div>
                    <div class="col-md-4"><label class="form-label">Controlado</label><select name="controlado" class="form-select"><option value="0" {{ $medicamento->controlado ? '' : 'selected' }}>Não</option><option value="1" {{ $medicamento->controlado ? 'selected' : '' }}>Sim</option></select></div>
                    <div class="col-md-4"><label class="form-label">Status</label><select name="ativo" class="form-select"><option value="1" {{ $medicamento->ativo ? 'selected' : '' }}>Ativo</option><option value="0" {{ $medicamento->ativo ? '' : 'selected' }}>Inativo</option></select></div>
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

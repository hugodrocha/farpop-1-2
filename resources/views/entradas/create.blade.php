@extends('layouts.app')

@section('title', 'Nova entrada')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-arrow-down me-2"></i>Registrar entrada</h1>
        <p class="farpop-page-subtitle">Adicionar medicamentos ao estoque de uma unidade.</p>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('entradas.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Clínica</label><select name="clinica_id" class="form-select" required>@foreach($clinicas as $clinica)<option value="{{ $clinica->id }}">{{ $clinica->nome }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label class="form-label">Medicamento</label><select name="medicamento_id" class="form-select" required>@foreach($medicamentos as $medicamento)<option value="{{ $medicamento->id }}">{{ $medicamento->nome }} {{ $medicamento->dosagem }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="form-label">Quantidade</label><input type="number" name="quantidade" class="form-control" value="1" min="1" required></div>
                    <div class="col-md-3"><label class="form-label">Lote</label><input type="text" name="lote" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Validade</label><input type="text" name="validade" class="form-control" placeholder="MM/AAAA"></div>
                    <div class="col-md-3"><label class="form-label">Nota fiscal</label><input type="text" name="nota_fiscal" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Origem</label><input type="text" name="origem" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Observação</label><input type="text" name="observacao" class="form-control"></div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i> Salvar</button>
                    <a class="btn btn-outline-secondary" href="{{ route('entradas.index') }}">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

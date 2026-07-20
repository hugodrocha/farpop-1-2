@extends('layouts.app')
@section('title', 'Nova reserva')
@section('content')
<div class="container-fluid px-0" style="max-width:720px">
    <h1 class="farpop-page-title mb-4">Nova reserva</h1>
    @include('partials.flash')
    <form method="POST" action="{{ route('reservas.store') }}" class="card"><div class="card-body">@csrf
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">CPF do paciente *</label><input name="paciente_cpf" class="form-control" required value="{{ old('paciente_cpf') }}"></div>
            <div class="col-md-6"><label class="form-label">Nome do paciente *</label><input name="paciente_nome" class="form-control" required value="{{ old('paciente_nome') }}"></div>
            <div class="col-md-6"><label class="form-label">Medicamento *</label>
                <select name="medicamento_id" class="form-select" required>
                    <option value="">Selecione...</option>
                    @foreach($medicamentos as $m)<option value="{{ $m->id }}" {{ old('medicamento_id') == $m->id ? 'selected' : '' }}>{{ $m->nome }} @if($m->dosagem)— {{ $m->dosagem }}@endif</option>@endforeach
                </select>
            </div>
            <div class="col-md-6"><label class="form-label">Quantidade *</label><input type="number" name="quantidade" class="form-control" min="1" required value="{{ old('quantidade', 1) }}"></div>
            <div class="col-md-6"><label class="form-label">Unidade *</label>
                @if($clinicaFixa)<input type="hidden" name="clinica_id" value="{{ session('clinica_id') }}"><input class="form-control" readonly value="{{ session('clinica_nome') }}">
                @else<select name="clinica_id" class="form-select" required>@foreach($clinicas as $c)<option value="{{ $c->id }}">{{ $c->nome }}</option>@endforeach</select>@endif
            </div>
            <div class="col-md-6"><label class="form-label">Validade da reserva</label><input type="date" name="validade_reserva" class="form-control" value="{{ old('validade_reserva') }}"></div>
            <div class="col-12"><label class="form-label">Observação</label><input name="observacao" class="form-control" value="{{ old('observacao') }}"></div>
        </div>
        <div class="mt-3 text-end"><a href="{{ route('reservas.index') }}" class="btn btn-outline-secondary">Cancelar</a> <button class="btn btn-primary">Reservar</button></div>
    </div></form>
</div>
@endsection

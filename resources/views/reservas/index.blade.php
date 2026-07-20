@extends('layouts.app')
@section('title', 'Reservas de medicamentos')
@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between mb-4">
        <div><h1 class="farpop-page-title"><i class="fas fa-bookmark me-2"></i>Reservas</h1><p class="farpop-page-subtitle">Medicamentos reservados para pacientes específicos.</p></div>
        <a href="{{ route('reservas.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nova reserva</a>
    </div>
    @include('partials.flash')
    <div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Paciente</th><th>Medicamento</th><th>Qtd</th><th>Unidade</th><th>Status</th><th>Validade</th><th></th></tr></thead><tbody>
        @forelse($reservas as $r)
        <tr>
            <td>{{ $r->paciente }}</td><td>{{ $r->medicamento }}</td><td>{{ $r->quantidade_atendida }}/{{ $r->quantidade }}</td><td>{{ $r->clinica }}</td>
            <td><span class="badge bg-secondary">{{ ucfirst($r->status) }}</span></td>
            <td>{{ $r->validade_reserva ? \Carbon\Carbon::parse($r->validade_reserva)->format('d/m/Y') : '—' }}</td>
            <td>@if($r->status === 'ativa')<form method="POST" action="{{ route('reservas.cancelar', $r->id) }}">@csrf<button class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancelar reserva?')">Cancelar</button></form>@endif</td>
        </tr>
        @empty<tr><td colspan="7" class="text-center text-muted py-4">Nenhuma reserva.</td></tr>@endforelse
    </tbody></table></div></div>
</div>
@endsection

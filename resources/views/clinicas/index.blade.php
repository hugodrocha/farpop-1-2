@extends('layouts.app')

@section('title', 'Clínicas')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-hospital me-2"></i>Unidades de saúde</h1>
            <p class="farpop-page-subtitle">Unidades da Farmácia Popular cadastradas no sistema.</p>
        </div>
        <a href="{{ route('clinicas.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nova clínica</a>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CNES</th>
                            <th>Responsável</th>
                            <th>Central</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clinicas as $clinica)
                            <tr>
                                <td class="fw-medium">{{ $clinica->nome }}</td>
                                <td>{{ $clinica->cnes ?? '—' }}</td>
                                <td>{{ $clinica->responsavel ?: '—' }}</td>
                                <td>
                                    @if($clinica->eh_central)
                                        <span class="badge bg-primary">Central</span>
                                    @else
                                        <span class="badge bg-secondary">Unidade</span>
                                    @endif
                                </td>
                                <td>
                                    @if($clinica->ativo)
                                        <span class="badge bg-success">Ativa</span>
                                    @else
                                        <span class="badge bg-danger">Inativa</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('clinicas.edit', $clinica->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    <form class="d-inline" method="POST" action="{{ route('clinicas.destroy', $clinica->id) }}" onsubmit="return confirm('Remover esta clínica?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma clínica cadastrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

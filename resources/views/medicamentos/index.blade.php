@extends('layouts.app')

@section('title', 'Medicamentos')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-capsules me-2"></i>Medicamentos</h1>
            <p class="farpop-page-subtitle">Catálogo de medicamentos da Farmácia Popular.</p>
        </div>
        <a href="{{ route('medicamentos.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Novo medicamento</a>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Dosagem</th>
                            <th>Unidade</th>
                            <th>Estoque mín.</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($medicamentos as $medicamento)
                            <tr>
                                <td class="fw-medium">{{ $medicamento->nome }}</td>
                                <td>{{ $medicamento->dosagem ?: '—' }}</td>
                                <td>{{ $medicamento->unidade }}</td>
                                <td>{{ $medicamento->estoque_minimo }}</td>
                                <td>
                                    @if($medicamento->ativo)
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('medicamentos.edit', $medicamento->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    <form class="d-inline" method="POST" action="{{ route('medicamentos.destroy', $medicamento->id) }}" onsubmit="return confirm('Remover este medicamento?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhum medicamento cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

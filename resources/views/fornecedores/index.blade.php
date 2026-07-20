@extends('layouts.app')

@section('title', 'Fornecedores')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-truck-loading me-2"></i>Fornecedores</h1>
        </div>
        <a href="{{ route('fornecedores.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Novo</a>
    </div>
    @include('partials.flash')
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Nome</th><th>CNPJ</th><th>Telefone</th><th>Status</th><th class="text-end">Ações</th></tr></thead>
                <tbody>
                    @forelse($fornecedores as $f)
                    <tr>
                        <td>{{ $f->nome }}</td>
                        <td>{{ $f->cnpj ?: '—' }}</td>
                        <td>{{ $f->telefone ?: '—' }}</td>
                        <td>@if($f->ativo)<span class="badge bg-success">Ativo</span>@else<span class="badge bg-secondary">Inativo</span>@endif</td>
                        <td class="text-end">
                            <a href="{{ route('fornecedores.edit', $f->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <form class="d-inline" method="POST" action="{{ route('fornecedores.destroy', $f->id) }}" onsubmit="return confirm('Remover?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Nenhum fornecedor.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

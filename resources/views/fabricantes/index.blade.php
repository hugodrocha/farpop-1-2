@extends('layouts.app')
@section('title', 'Fabricantes')
@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between mb-4"><h1 class="farpop-page-title"><i class="fas fa-industry me-2"></i>Fabricantes</h1><a href="{{ route('fabricantes.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Novo</a></div>
    @include('partials.flash')
    <div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Nome</th><th>CNPJ</th><th>Status</th><th class="text-end">Ações</th></tr></thead><tbody>
        @forelse($fabricantes as $f)
        <tr><td>{{ $f->nome }}</td><td>{{ $f->cnpj ?: '—' }}</td><td>@if($f->ativo)<span class="badge bg-success">Ativo</span>@else Inativo @endif</td>
        <td class="text-end"><a href="{{ route('fabricantes.edit', $f->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
        <form class="d-inline" method="POST" action="{{ route('fabricantes.destroy', $f->id) }}" onsubmit="return confirm('Remover?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></td></tr>
        @empty<tr><td colspan="4" class="text-center text-muted py-4">Nenhum fabricante.</td></tr>@endforelse
    </tbody></table></div></div>
</div>
@endsection

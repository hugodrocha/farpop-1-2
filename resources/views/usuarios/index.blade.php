@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-users me-2"></i>Usuários</h1>
            <p class="farpop-page-subtitle">Contas de acesso ao sistema FARPOP.</p>
        </div>
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i> Novo usuário</a>
    </div>

    @include('partials.flash')

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Usuário</th>
                            <th>Perfil</th>
                            <th>Clínica</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                            <tr>
                                <td class="fw-medium">{{ $usuario->nome_completo }}</td>
                                <td>{{ $usuario->usuario }}</td>
                                <td><span class="badge bg-primary">{{ str_replace('_', ' ', $usuario->perfil) }}</span></td>
                                <td>{{ $usuario->clinica_nome ?? '—' }}</td>
                                <td>
                                    @if($usuario->bloqueado)
                                        <span class="badge bg-danger">Bloqueado</span>
                                    @else
                                        <span class="badge bg-success">Ativo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    <form class="d-inline" method="POST" action="{{ route('usuarios.destroy', $usuario->id) }}" onsubmit="return confirm('Remover este usuário?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhum usuário cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

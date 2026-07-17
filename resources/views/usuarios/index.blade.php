@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
<div class="card">
    <div class="actions">
        <h2 style="margin:0;">Usuários</h2>
        <a class="btn" href="{{ route('usuarios.create') }}">+ Novo usuário</a>
    </div>
    @if(session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif
    <table>
        <thead><tr><th>Nome</th><th>Usuário</th><th>Perfil</th><th>Clínica</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
            @foreach($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->nome_completo }}</td>
                    <td>{{ $usuario->usuario }}</td>
                    <td>{{ $usuario->perfil }}</td>
                    <td>{{ $usuario->clinica_nome ?? '-' }}</td>
                    <td>{{ $usuario->bloqueado ? 'Bloqueado' : 'Ativo' }}</td>
                    <td>
                        <a href="{{ route('usuarios.edit', $usuario->id) }}">Editar</a> |
                        <form style="display:inline" method="POST" action="{{ route('usuarios.destroy', $usuario->id) }}" onsubmit="return confirm('Remover este usuário?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="border:none;background:none;color:#0f766e;cursor:pointer;padding:0;">Excluir</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

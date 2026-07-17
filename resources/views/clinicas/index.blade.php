@extends('layouts.app')

@section('title', 'Clínicas')

@section('content')
<div class="card">
    <div class="actions">
        <h2 style="margin:0;">Clínicas</h2>
        <a class="btn" href="{{ route('clinicas.create') }}">+ Nova clínica</a>
    </div>
    @if(session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif
    <table>
        <thead><tr><th>Nome</th><th>Responsável</th><th>Central</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
            @foreach($clinicas as $clinica)
                <tr>
                    <td>{{ $clinica->nome }}</td>
                    <td>{{ $clinica->responsavel }}</td>
                    <td>{{ $clinica->eh_central ? 'Sim' : 'Não' }}</td>
                    <td>{{ $clinica->ativo ? 'Ativa' : 'Inativa' }}</td>
                    <td>
                        <a href="{{ route('clinicas.edit', $clinica->id) }}">Editar</a> |
                        <form style="display:inline" method="POST" action="{{ route('clinicas.destroy', $clinica->id) }}" onsubmit="return confirm('Remover esta clínica?')">
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

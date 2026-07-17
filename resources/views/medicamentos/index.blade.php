@extends('layouts.app')

@section('title', 'Medicamentos')

@section('content')
<div class="card">
    <div class="actions">
        <h2 style="margin:0;">Medicamentos</h2>
        <a class="btn" href="{{ route('medicamentos.create') }}">+ Novo medicamento</a>
    </div>
    @if(session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif
    <table>
        <thead><tr><th>Nome</th><th>Dosagem</th><th>Unidade</th><th>Estoque mínimo</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
            @foreach($medicamentos as $medicamento)
                <tr>
                    <td>{{ $medicamento->nome }}</td>
                    <td>{{ $medicamento->dosagem }}</td>
                    <td>{{ $medicamento->unidade }}</td>
                    <td>{{ $medicamento->estoque_minimo }}</td>
                    <td>{{ $medicamento->ativo ? 'Ativo' : 'Inativo' }}</td>
                    <td>
                        <a href="{{ route('medicamentos.edit', $medicamento->id) }}">Editar</a> |
                        <form style="display:inline" method="POST" action="{{ route('medicamentos.destroy', $medicamento->id) }}" onsubmit="return confirm('Remover este medicamento?')">
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

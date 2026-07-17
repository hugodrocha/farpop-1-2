@extends('layouts.app')

@section('title', 'Auditoria')

@section('content')
<div class="card">
    <h2>Auditoria</h2>
    <p class="muted">Últimas ações registradas no sistema.</p>
    <table>
        <thead>
            <tr><th>Data</th><th>Usuário</th><th>Ação</th><th>Alvo</th><th>Detalhes</th></tr>
        </thead>
        <tbody>
            @foreach($registros as $registro)
                <tr>
                    <td>{{ $registro->criado_em }}</td>
                    <td>{{ $registro->nome_responsavel ?: $registro->usuario_responsavel }}</td>
                    <td>{{ $registro->acao }}</td>
                    <td>{{ $registro->alvo }}</td>
                    <td>{{ $registro->detalhes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

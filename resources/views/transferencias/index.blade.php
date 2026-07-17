@extends('layouts.app')

@section('title', 'Transferências')

@section('content')
<div class="card">
    <h2>Transferências</h2>
    <p class="muted">Fluxo de movimentação entre unidades.</p>
    <table>
        <thead>
            <tr><th>Data</th><th>Origem</th><th>Destino</th><th>Status</th><th>Usuário</th></tr>
        </thead>
        <tbody>
            @foreach($transferencias as $transferencia)
                <tr>
                    <td>{{ $transferencia->enviado_em }}</td>
                    <td>{{ $transferencia->clinica_origem }}</td>
                    <td>{{ $transferencia->clinica_destino }}</td>
                    <td>{{ $transferencia->status }}</td>
                    <td>{{ $transferencia->usuario_nome }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

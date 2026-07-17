@extends('layouts.app')

@section('title', 'Entradas')

@section('content')
<div class="card">
    <h2>Entradas de medicamentos</h2>
    <p class="muted">Últimas entradas registradas no sistema.</p>
    <table>
        <thead>
            <tr><th>Data</th><th>Clínica</th><th>Medicamento</th><th>Quantidade</th><th>Usuário</th></tr>
        </thead>
        <tbody>
            @foreach($entradas as $entrada)
                <tr>
                    <td>{{ $entrada->data_entrada }}</td>
                    <td>{{ $entrada->clinica_nome }}</td>
                    <td>{{ $entrada->medicamento_nome }} {{ $entrada->dosagem }}</td>
                    <td>{{ $entrada->quantidade }}</td>
                    <td>{{ $entrada->usuario_nome }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Saídas')

@section('content')
<div class="card">
    <h2>Saídas de medicamentos</h2>
    <p class="muted">Últimas dispensações registradas no sistema.</p>
    <table>
        <thead>
            <tr><th>Data</th><th>Clínica</th><th>Paciente</th><th>Medicamento</th><th>Quantidade</th></tr>
        </thead>
        <tbody>
            @foreach($saidas as $saida)
                <tr>
                    <td>{{ $saida->data_saida }}</td>
                    <td>{{ $saida->clinica_nome }}</td>
                    <td>{{ $saida->paciente_nome }}</td>
                    <td>{{ $saida->medicamento_nome }} {{ $saida->dosagem }}</td>
                    <td>{{ $saida->quantidade }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

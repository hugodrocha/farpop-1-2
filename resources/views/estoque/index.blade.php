@extends('layouts.app')

@section('title', 'Estoque')

@section('content')
<div class="card">
    <h2>Estoque</h2>
    <p class="muted">Consulta de saldo por unidade e medicamento.</p>
    <table>
        <thead>
            <tr><th>Clínica</th><th>Medicamento</th><th>Quantidade</th><th>Atualizado em</th></tr>
        </thead>
        <tbody>
            @foreach($itens as $item)
                <tr>
                    <td>{{ $item->clinica_nome }}</td>
                    <td>{{ $item->medicamento_nome }} {{ $item->dosagem }} {{ $item->unidade }}</td>
                    <td>{{ $item->quantidade }}</td>
                    <td>{{ $item->atualizado_em }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

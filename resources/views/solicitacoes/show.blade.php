@extends('layouts.app')

@section('title', 'Solicitação ' . $sol->codigo)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-clipboard-list me-2"></i>{{ $sol->codigo }}</h1>
            <p class="farpop-page-subtitle">{{ $sol->solicitante }} → {{ $sol->origem }}</p>
        </div>
        <a href="{{ route('solicitacoes.index') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>

    @include('partials.flash')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-primary">Itens</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Medicamento</th>
                                <th>Solicitado</th>
                                @if($visaoCentral && $sol->status === 'solicitada')
                                <th>Aprovar</th>
                                @else
                                <th>Aprovado</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($itens as $it)
                            <tr>
                                <td>{{ $it->medicamento }} @if($it->dosagem)<small class="text-muted">{{ $it->dosagem }}</small>@endif</td>
                                <td>{{ $it->quantidade_solicitada }}</td>
                                <td>
                                    @if($visaoCentral && $sol->status === 'solicitada')
                                        <input form="formAprovar" type="number" name="quantidade_aprovada[{{ $it->id }}]" class="form-control form-control-sm" min="0" max="{{ $it->quantidade_solicitada }}" value="{{ $it->quantidade_solicitada }}">
                                    @else
                                        {{ $it->quantidade_aprovada ?? '—' }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <p><strong>Status:</strong> {{ ucfirst(str_replace('_',' ', $sol->status)) }}</p>
                    <p><strong>Solicitante:</strong> {{ $sol->usuario_nome ?? '—' }}</p>
                    <p><strong>Criada em:</strong> {{ \Carbon\Carbon::parse($sol->criado_em)->format('d/m/Y H:i') }}</p>
                    @if($sol->observacao)<p><strong>Obs.:</strong> {{ $sol->observacao }}</p>@endif
                    @if($sol->motivo_recusa)<p class="text-danger"><strong>Recusa:</strong> {{ $sol->motivo_recusa }}</p>@endif
                    @if($sol->transferencia_id)
                        <a href="{{ route('transferencias.nota', $sol->transferencia_id) }}" class="btn btn-sm btn-outline-primary">Ver guia #{{ $sol->transferencia_id }}</a>
                    @endif
                </div>
            </div>

            @if($visaoCentral)
                @if($sol->status === 'solicitada')
                <form id="formAprovar" method="POST" action="{{ route('solicitacoes.aprovar', $sol->id) }}" class="card mb-3">
                    @csrf
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-success">Aprovar solicitação</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('solicitacoes.recusar', $sol->id) }}" class="card">
                    @csrf
                    <div class="card-body">
                        <label class="form-label">Motivo da recusa</label>
                        <textarea name="motivo" class="form-control mb-2" required maxlength="500"></textarea>
                        <button type="submit" class="btn btn-outline-danger w-100">Recusar</button>
                    </div>
                </form>
                @elseif($sol->status === 'aprovada')
                <form method="POST" action="{{ route('solicitacoes.separar', $sol->id) }}" class="d-grid mb-2">
                    @csrf
                    <button type="submit" class="btn btn-warning">Iniciar separação</button>
                </form>
                <form method="POST" action="{{ route('solicitacoes.enviar', $sol->id) }}" class="d-grid">
                    @csrf
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Gerar transferência e enviar?')">Enviar transferência</button>
                </form>
                @elseif($sol->status === 'em_separacao')
                <form method="POST" action="{{ route('solicitacoes.enviar', $sol->id) }}" class="d-grid">
                    @csrf
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Finalizar separação e enviar?')">Enviar transferência</button>
                </form>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

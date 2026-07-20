@extends('layouts.app')

@section('title', 'Paciente — ' . $paciente->nome)

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-user-injured me-2"></i>{{ $paciente->nome }}</h1>
            <p class="farpop-page-subtitle">Histórico completo de dispensas do paciente.</p>
        </div>
        <a href="{{ route('pacientes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Voltar</a>
        <a href="{{ route('pacientes.edit', $paciente->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i> Editar</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header fw-semibold">Dados cadastrais</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4">CPF</dt>
                        <dd class="col-sm-8">{{ $paciente->cpf ? \App\Services\SaidaService::formatarCpf($paciente->cpf) : '—' }}</dd>
                        <dt class="col-sm-4">CNS</dt>
                        <dd class="col-sm-8">{{ $paciente->cartao_sus ?: '—' }}</dd>
                        <dt class="col-sm-4">Nascimento</dt>
                        <dd class="col-sm-8">{{ $paciente->data_nascimento ? \Carbon\Carbon::parse($paciente->data_nascimento)->format('d/m/Y') : '—' }}</dd>
                        <dt class="col-sm-4">Telefone</dt>
                        <dd class="col-sm-8">{{ $paciente->telefone ?: '—' }}</dd>
                        <dt class="col-sm-4">Endereço</dt>
                        <dd class="col-sm-8">{{ $paciente->endereco ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header fw-semibold">Dispensas realizadas</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Unidade</th>
                            <th>Medicamento</th>
                            <th>Qtd</th>
                            <th>Receita / solicitação</th>
                            <th>Prescritor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historico as $h)
                            <tr>
                                <td class="text-nowrap">{{ $h->data_saida ? \Carbon\Carbon::parse($h->data_saida)->format('d/m/Y H:i') : '—' }}</td>
                                <td>{{ $h->clinica }}</td>
                                <td>{{ $h->medicamento }}</td>
                                <td>{{ $h->quantidade }}</td>
                                <td>{{ $h->numero_solicitacao ?: '—' }}</td>
                                <td>{{ $h->prescritor ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Nenhuma dispensa registrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

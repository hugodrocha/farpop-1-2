@extends('layouts.app')

@section('title', 'Pacientes')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-user-injured me-2"></i>Pacientes</h1>
            <p class="farpop-page-subtitle">Cadastro consultado e atualizado automaticamente nas dispensas. Pesquise por nome, CPF ou CNS.</p>
        </div>
        <a href="{{ route('saidas.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-pills me-1"></i> Nova dispensa</a>
    </div>

    @include('partials.flash')

    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('pacientes.index') }}" class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label mb-1">Buscar</label>
                    <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="Nome, CPF ou CNS (cartão SUS)">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-search me-1"></i> Buscar</button>
                    @if($q)<a href="{{ route('pacientes.index') }}" class="btn btn-outline-secondary">Limpar</a>@endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>CNS</th>
                            <th>Nascimento</th>
                            <th>Telefone</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pacientes as $p)
                            <tr>
                                <td>{{ $p->nome }}</td>
                                <td class="text-nowrap">{{ $p->cpf ? \App\Services\SaidaService::formatarCpf($p->cpf) : '—' }}</td>
                                <td>{{ $p->cartao_sus ?: '—' }}</td>
                                <td class="text-nowrap">{{ $p->data_nascimento ? \Carbon\Carbon::parse($p->data_nascimento)->format('d/m/Y') : '—' }}</td>
                                <td>{{ $p->telefone ?: '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('pacientes.show', $p->id) }}" class="btn btn-sm btn-outline-primary">Histórico</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    Nenhum paciente encontrado. Pacientes são cadastrados na primeira dispensa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Aqui Tem — FARPOP 1.1')

@section('content')
<div class="container-fluid px-0">
    <div class="farpop-hero-gradient p-4 p-md-5 mb-4">
        <div class="text-center mx-auto" style="max-width: 720px;">
            <h1 class="h3 mb-2 text-white fw-bold"><i class="fas fa-search-location me-2"></i>Aqui Tem</h1>
            <p class="mb-4 opacity-75">Consulte em quais unidades de saúde o medicamento está disponível.</p>
            <form method="GET" action="{{ route('aqui-tem.index') }}" class="d-flex gap-2 flex-wrap justify-content-center">
                <input type="search" name="q" class="form-control form-control-lg flex-grow-1" style="max-width: 480px;"
                       value="{{ $termo }}" placeholder="Nome, princípio ativo, código ou código de barras..." autofocus>
                <button type="submit" class="btn btn-light btn-lg px-4"><i class="fas fa-search me-1"></i> Buscar</button>
            </form>
        </div>
    </div>

    @include('partials.flash')

    @if($termo !== '')
        <div class="d-flex flex-wrap gap-3 mb-4">
            <div class="farpop-kpi-card kpi-1" style="min-width: 180px; flex: 1;">
                <div class="kpi-label">Resultados</div>
                <div class="kpi-valor">{{ number_format($resultados->count(), 0, ',', '.') }}</div>
                <div class="kpi-hint">linha(s) encontrada(s)</div>
            </div>
            <div class="farpop-kpi-card kpi-2" style="min-width: 180px; flex: 1;">
                <div class="kpi-label">Unidades</div>
                <div class="kpi-valor">{{ number_format($clinicasUnicas, 0, ',', '.') }}</div>
                <div class="kpi-hint">clínica(s) com estoque</div>
            </div>
            <div class="farpop-kpi-card kpi-3" style="min-width: 180px; flex: 1;">
                <div class="kpi-label">Total disponível</div>
                <div class="kpi-valor">{{ number_format($totalUnidades, 0, ',', '.') }}</div>
                <div class="kpi-hint">unidades somadas</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary">
                <i class="fas fa-list me-2"></i>Resultados para &ldquo;{{ $termo }}&rdquo;
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Medicamento</th>
                                <th>Clínica</th>
                                <th>Endereço</th>
                                <th>Telefone</th>
                                <th>Quantidade</th>
                                <th>Atualizado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resultados as $r)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $r->medicamento_nome }} {{ $r->dosagem }}</div>
                                        <div class="text-muted small">{{ $r->principio_ativo }} · {{ $r->unidade }}</div>
                                    </td>
                                    <td>
                                        {{ $r->clinica_nome }}
                                        @if($r->eh_central)
                                            <span class="badge bg-warning text-dark ms-1">Central</span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        {{ $r->clinica_endereco ?: '—' }}
                                        @if($r->clinica_bairro)
                                            <br><span class="text-muted">{{ $r->clinica_bairro }}</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ $r->clinica_telefone ?: '—' }}</td>
                                    <td><span class="badge bg-success fs-6">{{ number_format($r->quantidade, 0, ',', '.') }}</span></td>
                                    <td class="text-muted text-nowrap small">
                                        {{ $r->atualizado_em ? \Carbon\Carbon::parse($r->atualizado_em)->format('d/m/Y H:i') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                        Nenhum medicamento encontrado com estoque disponível.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-pills fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">Digite o nome ou código do medicamento para iniciar a busca.</p>
            </div>
        </div>
    @endif
</div>
@endsection

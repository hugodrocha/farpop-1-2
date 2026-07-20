@extends('layouts.app')

@section('title', 'Indicadores gerenciais')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-chart-pie me-2"></i>Indicadores gerenciais</h1>
            <p class="farpop-page-subtitle">Visão mensal da operação farmacêutica — {{ $indicadores['periodo_label'] }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('indicadores.exportar', array_filter(['clinica_id' => $clinicaId ?: null])) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-csv me-1"></i> CSV 12 meses</a>
            <a href="{{ route('indicadores.imprimir', array_filter(['mes' => $mes, 'ano' => $ano, 'clinica_id' => $clinicaId ?: null])) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fas fa-print me-1"></i> Imprimir</a>
        </div>
    </div>

    @if($ehAdmin && $clinicas->isNotEmpty())
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Mês</label>
                    <select name="mes" class="form-select form-select-sm">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($mes === $m)>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Ano</label>
                    <input type="number" name="ano" class="form-control form-control-sm" value="{{ $ano }}" min="2000" max="2100">
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1">Unidade</label>
                    <select name="clinica_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach($clinicas as $c)
                            <option value="{{ $c->id }}" @selected($clinicaId === (int) $c->id)>{{ $c->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted small">Entradas</span><div class="fs-4 fw-bold">{{ number_format($indicadores['entradas_unidades'], 0, ',', '.') }}</div><span class="small text-muted">{{ $indicadores['entradas_qtd'] }} registro(s)</span></div></div></div>
        <div class="col-md-4 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted small">Dispensas</span><div class="fs-4 fw-bold">{{ number_format($indicadores['saidas_unidades'], 0, ',', '.') }}</div><span class="small text-muted">{{ $indicadores['pacientes_atendidos'] }} paciente(s)</span></div></div></div>
        <div class="col-md-4 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted small">Transferências</span><div class="fs-4 fw-bold">{{ $indicadores['transferencias_enviadas'] }}</div><span class="small text-muted">{{ $indicadores['transferencias_recebidas'] }} recebida(s)</span></div></div></div>
        <div class="col-md-4 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted small">Solicitações</span><div class="fs-4 fw-bold">{{ $indicadores['solicitacoes_criadas'] }}</div><span class="small text-muted">{{ $indicadores['solicitacoes_aprovadas'] }} aprovada(s)</span></div></div></div>
        <div class="col-md-4 col-lg-3"><div class="card h-100 border-warning"><div class="card-body"><span class="text-muted small">Estoque atual</span><div class="fs-4 fw-bold">{{ number_format($indicadores['estoque_atual'], 0, ',', '.') }}</div></div></div></div>
        <div class="col-md-4 col-lg-3"><div class="card h-100 border-danger"><div class="card-body"><span class="text-muted small">Alertas críticos</span><div class="fs-4 fw-bold text-danger">{{ $indicadores['alertas_criticos'] }}</div><span class="small text-muted">{{ $indicadores['ruptura'] }} ruptura(s)</span></div></div></div>
        <div class="col-md-4 col-lg-3"><div class="card h-100"><div class="card-body"><span class="text-muted small">Lotes vencidos</span><div class="fs-4 fw-bold">{{ $indicadores['alertas_vencidos'] }}</div></div></div></div>
    </div>

    <div class="card">
        <div class="card-header bg-primary"><i class="fas fa-history me-2"></i>Comparativo — últimos 6 meses</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Período</th>
                        <th>Entradas</th>
                        <th>Dispensas</th>
                        <th>Pacientes</th>
                        <th>Transf.</th>
                        <th>Solicitações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historico as $h)
                    <tr>
                        <td>{{ $h->periodo_label }}</td>
                        <td>{{ number_format($h->entradas_unidades, 0, ',', '.') }}</td>
                        <td>{{ number_format($h->saidas_unidades, 0, ',', '.') }}</td>
                        <td>{{ $h->pacientes_atendidos }}</td>
                        <td>{{ $h->transferencias_enviadas }}</td>
                        <td>{{ $h->solicitacoes_criadas }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

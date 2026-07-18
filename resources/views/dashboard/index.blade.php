@extends('layouts.app')

@section('title', 'Dashboard — FARPOP 1.1')

@section('content')
<div class="container-fluid px-0">
    <div class="farpop-hero-gradient p-4 p-md-5 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h1 class="h3 mb-2 text-white fw-bold">Olá, {{ session('nome_completo') ?: session('usuario') ?: 'Usuário' }}!</h1>
                @if($visaoGlobal)
                    <p class="mb-0 opacity-75">Painel de controle da Farmácia Popular — visão global do sistema.</p>
                @else
                    <p class="mb-0 opacity-75">Painel da unidade <strong>{{ $totais['clinica_nome'] ?? session('clinica_nome') }}</strong>.</p>
                @endif
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('entradas.create') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-down me-1"></i> Nova entrada</a>
                <a href="{{ route('saidas.create') }}" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-up me-1"></i> Nova saída</a>
                @if($visaoGlobal)
                    <a href="{{ route('transferencias.create') }}" class="btn btn-outline-light btn-sm"><i class="fas fa-truck me-1"></i> Enviar lote</a>
                @endif
            </div>
        </div>
    </div>

    @include('partials.flash')

    @if($visaoGlobal)
        <div class="farpop-kpi-grid">
            <div class="farpop-kpi-card kpi-1">
                <div class="kpi-label"><i class="fas fa-boxes me-1"></i> Estoque total</div>
                <div class="kpi-valor">{{ number_format($totais['estoque'], 0, ',', '.') }}</div>
                <div class="kpi-hint">unidades em todas as clínicas</div>
            </div>
            <div class="farpop-kpi-card kpi-5">
                <div class="kpi-label"><i class="fas fa-exclamation-triangle me-1"></i> Itens críticos</div>
                <div class="kpi-valor">{{ number_format($totais['criticos'], 0, ',', '.') }}</div>
                <div class="kpi-hint">≤ {{ $totais['pct_vermelho'] }}% do mínimo</div>
            </div>
            <div class="farpop-kpi-card kpi-4">
                <div class="kpi-label"><i class="fas fa-exclamation-circle me-1"></i> Itens em atenção</div>
                <div class="kpi-valor">{{ number_format($totais['amarelos'], 0, ',', '.') }}</div>
                <div class="kpi-hint">≤ {{ $totais['pct_amarelo'] }}% do mínimo</div>
            </div>
            <div class="farpop-kpi-card kpi-3">
                <div class="kpi-label"><i class="fas fa-truck me-1"></i> Transf. pendentes</div>
                <div class="kpi-valor">{{ number_format($totais['transf_pendentes'], 0, ',', '.') }}</div>
                <div class="kpi-hint">aguardando recebimento</div>
            </div>
            <div class="farpop-kpi-card kpi-2">
                <div class="kpi-label"><i class="fas fa-exchange-alt me-1"></i> Movimentações hoje</div>
                <div class="kpi-valor">{{ number_format($totais['entradas_hoje'], 0, ',', '.') }} / {{ number_format($totais['saidas_hoje'], 0, ',', '.') }}</div>
                <div class="kpi-hint">entradas / saídas registradas</div>
            </div>
        </div>

        @if($cardsClinicas->isNotEmpty())
            <h2 class="farpop-section-title">Unidades de saúde</h2>
            <div class="row g-3 mb-4">
                @foreach($cardsClinicas as $card)
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="card-clinica {{ $card->eh_central ? 'eh-central' : '' }} {{ $card->criticos > 0 ? 'tem-critico' : '' }}">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="h6 mb-0 fw-bold">{{ $card->nome }}</h3>
                                @if($card->eh_central)
                                    <span class="badge bg-warning text-dark">Central</span>
                                @endif
                            </div>
                            <div class="row g-2 small">
                                <div class="col-6">
                                    <span class="text-muted d-block">Estoque</span>
                                    <strong>{{ number_format($card->estoque, 0, ',', '.') }}</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block">Saídas hoje</span>
                                    <strong>{{ number_format($card->saidas_hoje, 0, ',', '.') }}</strong>
                                </div>
                                <div class="col-12">
                                    @if($card->criticos > 0)
                                        <span class="badge bg-danger">{{ $card->criticos }} crítico(s)</span>
                                    @else
                                        <span class="badge bg-success">Sem alertas críticos</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($graficoSaidas)
            <div class="row g-4 mb-4">
                <div class="col-lg-5">
                    <div class="card h-100">
                        <div class="card-header bg-primary">
                            <i class="fas fa-chart-pie me-2"></i>Dispensações hoje por unidade
                        </div>
                        <div class="card-body d-flex justify-content-center align-items-center">
                            {!! $graficoSaidas !!}
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card h-100">
                        <div class="card-header bg-primary">
                            <i class="fas fa-info-circle me-2"></i>Resumo do dia
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="text-muted small">Quantidade dispensada</div>
                                        <div class="fs-4 fw-bold text-primary">{{ number_format($totais['qtd_saidas_hoje'], 0, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="text-muted small">Pacientes atendidos</div>
                                        <div class="fs-4 fw-bold text-primary">{{ number_format($totais['pac_atendidos_hoje'], 0, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="text-muted small">Clínicas ativas</div>
                                        <div class="fs-4 fw-bold">{{ number_format($totais['clinicas'], 0, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="text-muted small">Bloqueios hoje</div>
                                        <div class="fs-4 fw-bold {{ $totais['bloqueios_hoje'] > 0 ? 'text-danger' : '' }}">{{ number_format($totais['bloqueios_hoje'], 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="farpop-kpi-grid">
            <div class="farpop-kpi-card kpi-1">
                <div class="kpi-label"><i class="fas fa-boxes me-1"></i> Estoque da unidade</div>
                <div class="kpi-valor">{{ number_format($totais['estoque'], 0, ',', '.') }}</div>
                <div class="kpi-hint">{{ number_format($totais['itens'], 0, ',', '.') }} item(ns) cadastrado(s)</div>
            </div>
            <div class="farpop-kpi-card kpi-5">
                <div class="kpi-label"><i class="fas fa-exclamation-triangle me-1"></i> Itens críticos</div>
                <div class="kpi-valor">{{ number_format($totais['criticos'], 0, ',', '.') }}</div>
                <div class="kpi-hint">abaixo do mínimo crítico</div>
            </div>
            <div class="farpop-kpi-card kpi-4">
                <div class="kpi-label"><i class="fas fa-exclamation-circle me-1"></i> Itens em atenção</div>
                <div class="kpi-valor">{{ number_format($totais['amarelos'], 0, ',', '.') }}</div>
                <div class="kpi-hint">próximos do mínimo</div>
            </div>
            <div class="farpop-kpi-card kpi-3">
                <div class="kpi-label"><i class="fas fa-truck me-1"></i> Transf. pendentes</div>
                <div class="kpi-valor">{{ number_format($totais['transf_pendentes'], 0, ',', '.') }}</div>
                <div class="kpi-hint">aguardando recebimento</div>
            </div>
            <div class="farpop-kpi-card kpi-2">
                <div class="kpi-label"><i class="fas fa-exchange-alt me-1"></i> Movimentações hoje</div>
                <div class="kpi-valor">{{ number_format($totais['entradas_hoje'], 0, ',', '.') }} / {{ number_format($totais['saidas_hoje'], 0, ',', '.') }}</div>
                <div class="kpi-hint">entradas / saídas</div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <a href="{{ route('estoque.index') }}" class="text-decoration-none">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-boxes text-primary mb-2"></i>
                            <h3 class="h6 mb-1">Consultar estoque</h3>
                            <p class="text-muted small mb-0">Saldo por medicamento.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('transferencias.transito') }}" class="text-decoration-none">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-truck text-primary mb-2"></i>
                            <h3 class="h6 mb-1">Em trânsito</h3>
                            <p class="text-muted small mb-0">Transferências pendentes.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('transferencias.receber') }}" class="text-decoration-none">
                    <div class="card h-100">
                        <div class="card-body">
                            <i class="fas fa-barcode text-primary mb-2"></i>
                            <h3 class="h6 mb-1">Receber transferência</h3>
                            <p class="text-muted small mb-0">Confirmar nota de envio.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    @endif

    <h2 class="farpop-section-title">Últimas dispensações</h2>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            @if($visaoGlobal)
                                <th>Clínica</th>
                            @endif
                            <th>Paciente</th>
                            <th>Medicamento</th>
                            <th>Qtd</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimasSaidas as $saida)
                            <tr>
                                <td class="text-nowrap">{{ $saida->data_saida ? \Carbon\Carbon::parse($saida->data_saida)->format('d/m/Y H:i') : '—' }}</td>
                                @if($visaoGlobal)
                                    <td>{{ $saida->clinica }}</td>
                                @endif
                                <td>{{ $saida->paciente }}</td>
                                <td>{{ $saida->medicamento }} <span class="text-muted">{{ $saida->dosagem }}</span></td>
                                <td><span class="badge bg-primary">{{ number_format($saida->quantidade, 0, ',', '.') }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $visaoGlobal ? 5 : 4 }}" class="text-center text-muted py-4">Nenhuma dispensação recente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

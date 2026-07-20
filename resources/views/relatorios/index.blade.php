@extends('layouts.app')

@section('title', 'Relatórios')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-chart-bar me-2"></i>Relatórios</h1>
        <p class="farpop-page-subtitle">Exportação em CSV (Excel) e impressão/PDF. Use os filtros opcionais de unidade e período.</p>
    </div>

    @include('partials.flash')

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" id="filtrosRelatorio" class="row g-2 align-items-end">
                @if($ehAdmin)
                <div class="col-md-4">
                    <label class="form-label mb-1">Unidade (opcional)</label>
                    <select name="clinica_id" class="form-select form-select-sm" id="filtroClinica">
                        <option value="">Todas</option>
                        @foreach($clinicas as $c)
                            <option value="{{ $c->id }}">{{ $c->nome }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label mb-1">De (opcional)</label>
                    <input type="date" name="de" class="form-control form-control-sm" id="filtroDe">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Até (opcional)</label>
                    <input type="date" name="ate" class="form-control form-control-sm" id="filtroAte">
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        @foreach($relatorios as $rel)
            @php
                $badge = match($rel['status']) {
                    'ativo' => 'success',
                    'parcial' => 'warning',
                    default => 'secondary',
                };
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <h2 class="h6 mb-0">{{ $rel['label'] }}</h2>
                            <span class="badge bg-{{ $badge }}">{{ ucfirst($rel['status']) }}</span>
                        </div>
                        <div class="mt-auto d-flex flex-wrap gap-2">
                            @if(in_array($rel['status'], ['ativo', 'parcial'], true))
                                <a href="#" class="btn btn-sm btn-outline-success btn-export" data-tipo="{{ $rel['slug'] }}" data-formato="csv"><i class="fas fa-file-csv me-1"></i> Excel</a>
                                @if(in_array($rel['slug'], ['estoque_geral', 'estoque_unidade', 'vencendo', 'vencidos'], true))
                                    <a href="#" class="btn btn-sm btn-outline-secondary btn-export" data-tipo="{{ $rel['slug'] }}" data-formato="pdf"><i class="fas fa-print me-1"></i> PDF</a>
                                @endif
                            @else
                                <span class="small text-muted">Previsto na Fase 3+</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const baseExport = @json(url('/relatorios/exportar'));
    const basePrint = @json(url('/relatorios/imprimir'));

    function buildQuery() {
        const params = new URLSearchParams();
        const cid = document.getElementById('filtroClinica');
        const de = document.getElementById('filtroDe');
        const ate = document.getElementById('filtroAte');
        if (cid && cid.value) params.set('clinica_id', cid.value);
        if (de && de.value) params.set('de', de.value);
        if (ate && ate.value) params.set('ate', ate.value);
        return params;
    }

    document.querySelectorAll('.btn-export').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const tipo = this.dataset.tipo;
            const formato = this.dataset.formato;
            const params = buildQuery();
            const qs = params.toString();
            if (formato === 'pdf') {
                window.open(basePrint + '/' + tipo + (qs ? '?' + qs : ''), '_blank');
            } else {
                window.location.href = baseExport + '/' + tipo + (qs ? '?' + qs : '');
            }
        });
    });
})();
</script>
@endpush

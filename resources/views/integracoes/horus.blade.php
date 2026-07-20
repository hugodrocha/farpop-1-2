@extends('layouts.app')
@section('title', 'Exportação Hórus / SUS')
@section('content')
<div class="container-fluid px-0" style="max-width:720px">
    <h1 class="farpop-page-title mb-2"><i class="fas fa-plug me-2"></i>Hórus / SUS</h1>
    <p class="farpop-page-subtitle mb-4">Exportação CSV de movimentações para integração manual com BNAFAR/Hórus.</p>
    @include('partials.flash')
    <div class="card"><div class="card-body">
        <form method="GET" action="{{ route('integracoes.horus.exportar') }}">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">De</label><input type="date" name="de" class="form-control" required value="{{ $de }}"></div>
                <div class="col-md-6"><label class="form-label">Até</label><input type="date" name="ate" class="form-control" required value="{{ $ate }}"></div>
                <div class="col-12"><label class="form-label">Unidade (opcional)</label>
                    <select name="clinica_id" class="form-select"><option value="">Todas / central</option>
                        @foreach($clinicas as $c)<option value="{{ $c->id }}">{{ $c->nome }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="mt-3"><button class="btn btn-primary"><i class="fas fa-download me-1"></i> Baixar CSV</button></div>
        </form>
        <hr>
        <p class="small text-muted mb-0">Colunas: CNES, Data, Codigo_CATMAT, Quantidade, Tipo_movimento, Sistema_origem. Ajuste o arquivo conforme exigência do município antes do envio ao Hórus.</p>
    </div></div>
</div>
@endsection

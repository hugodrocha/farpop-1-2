@extends('layouts.app')

@section('title', 'Importar medicamentos')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-file-csv me-2"></i>Importar medicamentos</h1>
            <p class="farpop-page-subtitle">Importe ou atualize o cadastro a partir de arquivo CSV (REMUME ou similar).</p>
        </div>
        <a href="{{ route('medicamentos.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Voltar</a>
    </div>

    @include('partials.flash')

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary"><i class="fas fa-upload me-2"></i>Enviar arquivo</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('medicamentos.importar.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label">Arquivo CSV</label>
                            <input type="file" name="arquivo" class="form-control" accept=".csv,.txt" required>
                            <div class="form-text">Formatos aceitos: CSV ou TXT (máx. 10 MB). Delimitador detectado automaticamente (, ; ou tab).</div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-file-import me-1"></i> Importar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><i class="fas fa-info-circle me-2"></i>Colunas reconhecidas</div>
                <div class="card-body">
                    <p class="small text-muted mb-3">O cabeçalho do CSV pode usar qualquer um dos nomes abaixo (case insensitive):</p>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Campo</th>
                                <th>Nomes aceitos</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <tr><td><strong>nome</strong> *</td><td>nome, medicamento, produto</td></tr>
                            <tr><td>princípio ativo</td><td>principio_ativo, principio, substancia</td></tr>
                            <tr><td>forma farmacêutica</td><td>forma_farmaceutica, forma</td></tr>
                            <tr><td>dosagem</td><td>dosagem, concentracao</td></tr>
                            <tr><td>unidade</td><td>unidade, apresentacao (padrão: UNIDADE)</td></tr>
                            <tr><td>código</td><td>codigo, cod</td></tr>
                            <tr><td>código de barras</td><td>codigo_barras, ean, barcode</td></tr>
                        </tbody>
                    </table>
                    <p class="small text-muted mb-0">* Obrigatório. Medicamentos existentes (mesmo nome ou código) serão atualizados.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Enviar lote — Transferência')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-truck me-2"></i>Enviar lote</h1>
            <p class="farpop-page-subtitle">Monte o lote pela leitura de código de barras e envie da Central para a unidade de destino.</p>
        </div>
        <a href="{{ route('transferencias.transito') }}" class="btn btn-outline-primary"><i class="fas fa-route me-1"></i> Em trânsito</a>
    </div>

    @include('partials.flash')

    <form method="POST" action="{{ route('transferencias.lote') }}" id="formLote">
        @csrf
        <input type="hidden" name="itens_json" id="itensJson" value="{{ old('itens_json', '[]') }}">

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary"><i class="fas fa-cog me-2"></i>Destino</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Clínica de destino</label>
                            <select name="clinica_destino_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                @foreach($clinicas as $clinica)
                                    <option value="{{ $clinica->id }}" {{ old('clinica_destino_id') == $clinica->id ? 'selected' : '' }}>{{ $clinica->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Observação</label>
                            <textarea name="observacao" class="form-control" rows="3" maxlength="500">{{ old('observacao') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary"><i class="fas fa-barcode me-2"></i>Leitura</div>
                    <div class="card-body">
                        <label class="form-label">Código REMUME ou código de barras</label>
                        <div class="input-group mb-2">
                            <input type="text" id="inputCodigo" class="form-control form-control-lg" placeholder="Escaneie ou digite o código..." autocomplete="off">
                            <button type="button" class="btn btn-primary" id="btnBuscar"><i class="fas fa-plus"></i></button>
                        </div>
                        <div id="msgLeitura" class="small text-muted">Posicione o cursor no campo e escaneie o medicamento.</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-list me-2"></i>Itens do lote</span>
                        <span class="badge bg-light text-dark" id="contadorItens">0 item(ns)</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="tabelaItens">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Medicamento</th>
                                        <th style="width: 120px;">Estoque Central</th>
                                        <th style="width: 110px;">Quantidade</th>
                                        <th style="width: 60px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="corpoItens">
                                    <tr id="linhaVazia">
                                        <td colspan="5" class="text-center text-muted py-4">Nenhum item adicionado. Escaneie um medicamento.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2 justify-content-end">
                        <a href="{{ route('transferencias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary" id="btnEnviar" disabled>
                            <i class="fas fa-paper-plane me-1"></i> Finalizar e gerar nota
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const itens = [];
    let itensMap = {};

    try {
        const old = JSON.parse(document.getElementById('itensJson').value || '[]');
        if (Array.isArray(old)) old.forEach(i => adicionarItem(i, false));
    } catch (e) {}

    const inputCodigo = document.getElementById('inputCodigo');
    const msgLeitura = document.getElementById('msgLeitura');
    const corpoItens = document.getElementById('corpoItens');
    const linhaVazia = document.getElementById('linhaVazia');
    const contadorItens = document.getElementById('contadorItens');
    const btnEnviar = document.getElementById('btnEnviar');
    const itensJson = document.getElementById('itensJson');
    const urlBuscar = @json(route('medicamentos.buscar')) + '?clinica_id=' + @json($centralId) + '&q=';

    function sincronizarHidden() {
        itensJson.value = JSON.stringify(itens.map(i => ({
            medicamento_id: i.id,
            quantidade: i.quantidade
        })));
        contadorItens.textContent = itens.length + ' item(ns)';
        btnEnviar.disabled = itens.length === 0;
    }

    function renderizar() {
        corpoItens.querySelectorAll('tr:not(#linhaVazia)').forEach(tr => tr.remove());
        if (itens.length === 0) {
            linhaVazia.style.display = '';
            sincronizarHidden();
            return;
        }
        linhaVazia.style.display = 'none';

        itens.forEach((item, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><code>${esc(item.codigo || '—')}</code></td>
                <td>
                    <div class="fw-medium">${esc(item.nome)} ${esc(item.dosagem || '')}</div>
                    <div class="text-muted small">${esc(item.unidade || '')}</div>
                </td>
                <td><span class="badge bg-secondary">${item.estoque_central}</span></td>
                <td>
                    <input type="number" class="form-control form-control-sm qtd-item" min="1" max="${item.estoque_central}" value="${item.quantidade}" data-idx="${idx}">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remover" data-idx="${idx}"><i class="fas fa-trash"></i></button>
                </td>`;
            corpoItens.appendChild(tr);
        });

        corpoItens.querySelectorAll('.qtd-item').forEach(inp => {
            inp.addEventListener('change', function () {
                const idx = parseInt(this.dataset.idx, 10);
                let q = parseInt(this.value, 10) || 1;
                if (q > itens[idx].estoque_central) q = itens[idx].estoque_central;
                if (q < 1) q = 1;
                itens[idx].quantidade = q;
                this.value = q;
                sincronizarHidden();
            });
        });

        corpoItens.querySelectorAll('.btn-remover').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.dataset.idx, 10);
                delete itensMap[itens[idx].id];
                itens.splice(idx, 1);
                renderizar();
            });
        });

        sincronizarHidden();
    }

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function adicionarItem(med, incrementar) {
        const id = med.id || med.medicamento_id;
        if (itensMap[id]) {
            const idx = itens.findIndex(i => i.id === id);
            if (incrementar !== false) {
                const max = itens[idx].estoque_central;
                if (itens[idx].quantidade < max) itens[idx].quantidade++;
            }
            renderizar();
            return;
        }

        const item = {
            id: id,
            nome: med.nome,
            dosagem: med.dosagem,
            unidade: med.unidade,
            codigo: med.codigo,
            estoque_central: med.estoque_central ?? med.estoque ?? 0,
            quantidade: med.quantidade || 1
        };

        if (item.estoque_central <= 0) {
            msgLeitura.className = 'small text-danger';
            msgLeitura.textContent = 'Sem estoque na Central para este medicamento.';
            return;
        }

        itensMap[id] = true;
        itens.push(item);
        renderizar();
        msgLeitura.className = 'small text-success';
        msgLeitura.textContent = 'Medicamento adicionado: ' + item.nome;
    }

    async function buscarCodigo() {
        const codigo = inputCodigo.value.trim();
        if (!codigo) return;

        msgLeitura.className = 'small text-muted';
        msgLeitura.textContent = 'Buscando...';

        try {
            const res = await fetch(urlBuscar + encodeURIComponent(codigo), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.ok) {
                msgLeitura.className = 'small text-danger';
                msgLeitura.textContent = data.erro || 'Medicamento não encontrado.';
                return;
            }
            adicionarItem(data.medicamento, true);
            inputCodigo.value = '';
            inputCodigo.focus();
        } catch (e) {
            msgLeitura.className = 'small text-danger';
            msgLeitura.textContent = 'Erro ao buscar medicamento.';
        }
    }

    document.getElementById('btnBuscar').addEventListener('click', buscarCodigo);
    inputCodigo.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarCodigo();
        }
    });

    renderizar();
    inputCodigo.focus();
})();
</script>
@endpush

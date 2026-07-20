@extends('layouts.app')

@section('title', 'Transferência entre unidades')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-exchange-alt me-2"></i>Entre unidades</h1>
            <p class="farpop-page-subtitle">Envio direto entre unidades de saúde ou devolução de medicamentos à matriz.</p>
        </div>
        <a href="{{ route('transferencias.transito') }}" class="btn btn-outline-primary"><i class="fas fa-route me-1"></i> Em trânsito</a>
    </div>

    @include('partials.flash')

    <form method="POST" action="{{ route('transferencias.entre-unidades.store') }}" id="formLote">
        @csrf
        <input type="hidden" name="itens_json" id="itensJson" value="{{ old('itens_json', '[]') }}">

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header bg-primary"><i class="fas fa-cog me-2"></i>Origem e destino</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <select name="tipo_transferencia" id="tipoTransferencia" class="form-select" required>
                                <option value="entre_unidades" {{ old('tipo_transferencia', 'entre_unidades') === 'entre_unidades' ? 'selected' : '' }}>Entre unidades</option>
                                <option value="unidade_matriz" {{ old('tipo_transferencia') === 'unidade_matriz' ? 'selected' : '' }}>Devolução à matriz</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unidade de origem</label>
                            @if($origemFixa)
                                <input type="hidden" name="clinica_origem_id" id="clinicaOrigemId" value="{{ $clinicaUsuario }}">
                                <input type="text" class="form-control" readonly value="{{ session('clinica_nome') }}">
                            @else
                                <select name="clinica_origem_id" id="clinicaOrigemId" class="form-select" required>
                                    @foreach($clinicasOrigem as $c)
                                        <option value="{{ $c->id }}" {{ old('clinica_origem_id') == $c->id ? 'selected' : '' }}>{{ $c->nome }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unidade de destino</label>
                            <select name="clinica_destino_id" id="clinicaDestinoId" class="form-select" required>
                                <option value="">Selecione...</option>
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
                            <input type="text" id="inputCodigo" class="form-control form-control-lg" placeholder="Escaneie ou digite..." autocomplete="off">
                            <button type="button" class="btn btn-primary" id="btnBuscar"><i class="fas fa-plus"></i></button>
                        </div>
                        <div id="msgLeitura" class="small text-muted">Estoque consultado na unidade de origem.</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-list me-2"></i>Itens</span>
                        <span class="badge bg-light text-dark" id="contadorItens">0 item(ns)</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="tabelaItens">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Medicamento</th>
                                        <th style="width:120px">Estoque origem</th>
                                        <th style="width:110px">Qtd</th>
                                        <th style="width:60px"></th>
                                    </tr>
                                </thead>
                                <tbody id="corpoItens">
                                    <tr id="linhaVazia"><td colspan="5" class="text-center text-muted py-4">Nenhum item. Escaneie um medicamento.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer d-flex gap-2 justify-content-end">
                        <a href="{{ route('transferencias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary" id="btnEnviar" disabled><i class="fas fa-paper-plane me-1"></i> Enviar</button>
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
    const centralId = @json($centralId);
    const clinicas = @json($clinicas->values());
    const itens = [];
    let itensMap = {};

    const tipoSel = document.getElementById('tipoTransferencia');
    const origemSel = document.getElementById('clinicaOrigemId');
    const destinoSel = document.getElementById('clinicaDestinoId');
    const inputCodigo = document.getElementById('inputCodigo');
    const msgLeitura = document.getElementById('msgLeitura');
    const corpoItens = document.getElementById('corpoItens');
    const linhaVazia = document.getElementById('linhaVazia');
    const contadorItens = document.getElementById('contadorItens');
    const btnEnviar = document.getElementById('btnEnviar');
    const itensJson = document.getElementById('itensJson');
    const urlBuscarBase = @json(route('medicamentos.buscar'));

    function origemId() {
        return parseInt(origemSel.value || origemSel.getAttribute('value') || '0', 10);
    }

    function atualizarDestinos() {
        const origem = origemId();
        const tipo = tipoSel.value;
        destinoSel.innerHTML = '<option value="">Selecione...</option>';
        clinicas.forEach(c => {
            if (parseInt(c.id, 10) === origem) return;
            if (tipo === 'unidade_matriz') {
                if (centralId && parseInt(c.id, 10) === centralId) {
                    destinoSel.innerHTML += `<option value="${c.id}">${c.nome}</option>`;
                }
            } else if (tipo === 'entre_unidades') {
                if (!centralId || parseInt(c.id, 10) !== centralId) {
                    destinoSel.innerHTML += `<option value="${c.id}">${c.nome}</option>`;
                }
            }
        });
    }

    function urlBuscar() {
        return urlBuscarBase + '?clinica_id=' + origemId() + '&q=';
    }

    function sincronizarHidden() {
        itensJson.value = JSON.stringify(itens.map(i => ({ medicamento_id: i.id, quantidade: i.quantidade })));
        contadorItens.textContent = itens.length + ' item(ns)';
        btnEnviar.disabled = itens.length === 0;
    }

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
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
            tr.innerHTML = `<td><code>${esc(item.codigo || '—')}</code></td>
                <td><div class="fw-medium">${esc(item.nome)} ${esc(item.dosagem || '')}</div></td>
                <td><span class="badge bg-secondary">${item.estoque_origem}</span></td>
                <td><input type="number" class="form-control form-control-sm qtd-item" min="1" max="${item.estoque_origem}" value="${item.quantidade}" data-idx="${idx}"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger btn-remover" data-idx="${idx}"><i class="fas fa-trash"></i></button></td>`;
            corpoItens.appendChild(tr);
        });
        corpoItens.querySelectorAll('.qtd-item').forEach(inp => {
            inp.addEventListener('change', function () {
                const idx = parseInt(this.dataset.idx, 10);
                let q = parseInt(this.value, 10) || 1;
                q = Math.max(1, Math.min(q, itens[idx].estoque_origem));
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

    function adicionarItem(med) {
        const id = med.id || med.medicamento_id;
        const est = med.estoque ?? med.estoque_central ?? med.estoque_origem ?? 0;
        if (itensMap[id]) {
            const idx = itens.findIndex(i => i.id === id);
            if (itens[idx].quantidade < itens[idx].estoque_origem) itens[idx].quantidade++;
            renderizar();
            return;
        }
        if (est <= 0) {
            msgLeitura.className = 'small text-danger';
            msgLeitura.textContent = 'Sem estoque na unidade de origem.';
            return;
        }
        itensMap[id] = true;
        itens.push({ id, nome: med.nome, dosagem: med.dosagem, codigo: med.codigo, estoque_origem: est, quantidade: 1 });
        renderizar();
        msgLeitura.className = 'small text-success';
        msgLeitura.textContent = 'Adicionado: ' + med.nome;
    }

    async function buscarCodigo() {
        const codigo = inputCodigo.value.trim();
        if (!codigo || origemId() <= 0) return;
        msgLeitura.className = 'small text-muted';
        msgLeitura.textContent = 'Buscando...';
        try {
            const res = await fetch(urlBuscar() + encodeURIComponent(codigo), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            if (!data.ok) {
                msgLeitura.className = 'small text-danger';
                msgLeitura.textContent = data.erro || 'Não encontrado.';
                return;
            }
            adicionarItem(data.medicamento);
            inputCodigo.value = '';
            inputCodigo.focus();
        } catch (e) {
            msgLeitura.className = 'small text-danger';
            msgLeitura.textContent = 'Erro ao buscar.';
        }
    }

    tipoSel.addEventListener('change', atualizarDestinos);
    if (origemSel.tagName === 'SELECT') origemSel.addEventListener('change', () => { itens.length = 0; itensMap = {}; renderizar(); atualizarDestinos(); });
    document.getElementById('btnBuscar').addEventListener('click', buscarCodigo);
    inputCodigo.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); buscarCodigo(); } });

    atualizarDestinos();
    renderizar();
})();
</script>
@endpush

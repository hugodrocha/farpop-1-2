@extends('layouts.app')

@section('title', 'Nova entrada')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-arrow-down me-2"></i>Registrar entrada</h1>
        <p class="farpop-page-subtitle">Bipe os medicamentos, monte a lista e salve para alimentar o estoque.</p>
    </div>

    @include('partials.flash')

    <form method="POST" action="{{ route('entradas.store') }}" id="formEntrada">
        @csrf

        <div class="card mb-4">
            <div class="card-header bg-primary"><i class="fas fa-hospital me-2"></i>Dados da entrada</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Clínica</label>
                        @if($clinicaFixa)
                            <input type="hidden" name="clinica_id" id="clinicaId" value="{{ session('clinica_id') }}">
                            <input type="text" class="form-control" value="{{ session('clinica_nome') }}" readonly>
                        @else
                            <select name="clinica_id" id="clinicaId" class="form-select" required>
                                @foreach($clinicas as $clinica)
                                    <option value="{{ $clinica->id }}" {{ old('clinica_id', session('clinica_id')) == $clinica->id ? 'selected' : '' }}>{{ $clinica->nome }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nota fiscal</label>
                        <input type="text" name="nota_fiscal" class="form-control" value="{{ old('nota_fiscal') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo de entrada</label>
                        <select name="tipo_entrada" class="form-select">
                            <option value="entrada_compra">Entrada por compra</option>
                            <option value="entrada_doacao">Entrada por doação</option>
                            <option value="devolucao">Devolução</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Origem / fornecedor (texto)</label>
                        <input type="text" name="origem" class="form-control" value="{{ old('origem') }}" placeholder="Ex.: SUS, doação">
                    </div>
                    @if(!empty($fornecedores) && count($fornecedores))
                    <div class="col-md-3">
                        <label class="form-label">Fornecedor cadastrado</label>
                        <select name="fornecedor_id" class="form-select">
                            <option value="">—</option>
                            @foreach($fornecedores as $f)
                                <option value="{{ $f->id }}" {{ old('fornecedor_id') == $f->id ? 'selected' : '' }}>{{ $f->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-12">
                        <label class="form-label">Observação geral</label>
                        <input type="text" name="observacao" class="form-control" value="{{ old('observacao') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary"><i class="fas fa-barcode me-2"></i>Medicamentos</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label">Bipar código REMUME ou código de barras</label>
                        <input type="text" id="campoBipar" class="form-control form-control-lg" placeholder="Aponte o leitor e pressione Enter" autocomplete="off">
                        <div id="msgBipar" class="small mt-1 text-muted"></div>
                    </div>
                    <div class="col-md-7 position-relative">
                        <label class="form-label">Ou buscar pelo nome</label>
                        <input type="text" id="campoNome" class="form-control form-control-lg" placeholder="Digite o nome do medicamento..." autocomplete="off">
                        <div id="listaAutocomplete" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index:20;max-height:240px;overflow:auto"></div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0" id="tabelaMeds">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Medicamento</th>
                                <th style="width:110px">Quantidade</th>
                                <th style="width:110px">Lote</th>
                                <th style="width:120px">Fabricante</th>
                                <th style="width:110px">Validade</th>
                                <th>Obs.</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody id="corpoMeds"></tbody>
                    </table>
                </div>
                <div id="msgLista" class="text-muted small mt-2">Nenhum medicamento adicionado.</div>
            </div>
            <div class="card-footer d-flex gap-2 justify-content-end">
                <a href="{{ route('entradas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary" id="btnSalvar" disabled>
                    <i class="fas fa-save me-1"></i> Salvar entrada
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const urlBuscar = @json(route('medicamentos.buscar'));
    const urlAutocomplete = @json(route('medicamentos.autocomplete'));
    const corpoMeds = document.getElementById('corpoMeds');
    const campoBipar = document.getElementById('campoBipar');
    const campoNome = document.getElementById('campoNome');
    const listaAutocomplete = document.getElementById('listaAutocomplete');
    const msgLista = document.getElementById('msgLista');
    const msgBipar = document.getElementById('msgBipar');
    const btnSalvar = document.getElementById('btnSalvar');
    const clinicaEl = document.getElementById('clinicaId');
    const itens = [];

    function clinicaParam() {
        const id = clinicaEl ? clinicaEl.value : '';
        return id ? '&clinica_id=' + encodeURIComponent(id) : '';
    }

    function chaveItem(item) {
        return item.id + '|' + (item.lote || '') + '|' + (item.validade || '');
    }

    function atualizarMsgLista() {
        msgLista.textContent = itens.length ? itens.length + ' medicamento(s) na lista.' : 'Nenhum medicamento adicionado.';
        btnSalvar.disabled = itens.length === 0;
    }

    function renderTabela() {
        corpoMeds.innerHTML = '';
        itens.forEach((item, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><code>${esc(item.codigo || '—')}</code></td>
                <td class="fw-medium">${esc(item.label)}</td>
                <td>
                    <input type="hidden" name="med_id[]" value="${item.id}">
                    <input type="number" name="med_qtd[]" class="form-control form-control-sm" min="1" value="${item.qtd}" required data-idx="${idx}">
                </td>
                <td><input type="text" name="med_lote[]" class="form-control form-control-sm" value="${esc(item.lote || '')}" data-idx="${idx}"></td>
                <td><input type="text" name="med_fabricante[]" class="form-control form-control-sm" value="${esc(item.fabricante || '')}" data-idx="${idx}"></td>
                <td><input type="text" name="med_validade[]" class="form-control form-control-sm" value="${esc(item.validade || '')}" placeholder="MM/AAAA" data-idx="${idx}"></td>
                <td><input type="text" name="med_obs[]" class="form-control form-control-sm" value="${esc(item.obs || '')}" data-idx="${idx}"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger btn-remover" data-idx="${idx}"><i class="fas fa-trash"></i></button></td>`;
            corpoMeds.appendChild(tr);
        });

        corpoMeds.querySelectorAll('input[data-idx]').forEach(inp => {
            inp.addEventListener('change', function () {
                const idx = parseInt(this.dataset.idx, 10);
                const field = this.name.replace('med_', '').replace('[]', '');
                if (field === 'qtd') itens[idx].qtd = Math.max(1, parseInt(this.value, 10) || 1);
                else itens[idx][field] = this.value;
            });
            inp.addEventListener('input', function () {
                const idx = parseInt(this.dataset.idx, 10);
                const field = this.name.replace('med_', '').replace('[]', '');
                if (field !== 'qtd') itens[idx][field] = this.value;
            });
        });

        corpoMeds.querySelectorAll('.btn-remover').forEach(btn => {
            btn.addEventListener('click', function () {
                itens.splice(parseInt(this.dataset.idx, 10), 1);
                renderTabela();
                atualizarMsgLista();
            });
        });

        atualizarMsgLista();
    }

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function adicionarMedicamento(med, incrementar) {
        const item = {
            id: med.id,
            label: med.label || (med.nome + ' ' + (med.dosagem || '')),
            codigo: med.codigo,
            qtd: 1,
            lote: '',
            fabricante: '',
            validade: '',
            obs: ''
        };

        const existente = itens.find(i => i.id === item.id && !i.lote && !i.validade);
        if (existente && incrementar !== false) {
            existente.qtd += 1;
            msgBipar.className = 'small mt-1 text-success';
            msgBipar.textContent = 'Quantidade incrementada: ' + item.label;
            renderTabela();
            return;
        }

        itens.push(item);
        renderTabela();
        msgBipar.className = 'small mt-1 text-success';
        msgBipar.textContent = 'Adicionado: ' + item.label;
        campoBipar.value = '';
        campoBipar.focus();
    }

    async function buscarMedicamento(termo) {
        const res = await fetch(urlBuscar + '?q=' + encodeURIComponent(termo) + clinicaParam(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        return res.json();
    }

    campoBipar.addEventListener('keydown', async (e) => {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const termo = campoBipar.value.trim();
        if (!termo) return;
        msgBipar.className = 'small mt-1 text-muted';
        msgBipar.textContent = 'Buscando...';
        try {
            const data = await buscarMedicamento(termo);
            if (!data.ok) {
                msgBipar.className = 'small mt-1 text-danger';
                msgBipar.textContent = data.erro || 'Medicamento não encontrado.';
                return;
            }
            adicionarMedicamento(data.medicamento, true);
        } catch (err) {
            msgBipar.className = 'small mt-1 text-danger';
            msgBipar.textContent = 'Erro ao buscar medicamento.';
        }
    });

    let timerAuto = null;
    campoNome.addEventListener('input', () => {
        clearTimeout(timerAuto);
        const termo = campoNome.value.trim();
        if (termo.length < 2) {
            listaAutocomplete.classList.add('d-none');
            return;
        }
        timerAuto = setTimeout(async () => {
            const res = await fetch(urlAutocomplete + '?q=' + encodeURIComponent(termo) + clinicaParam(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            listaAutocomplete.innerHTML = '';
            (data.itens || []).forEach((med) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';
                btn.innerHTML = `<strong>${esc(med.label)}</strong><br><small class="text-muted">Código: ${esc(med.codigo || '—')}</small>`;
                btn.addEventListener('click', () => {
                    adicionarMedicamento(med, false);
                    campoNome.value = '';
                    listaAutocomplete.classList.add('d-none');
                });
                listaAutocomplete.appendChild(btn);
            });
            listaAutocomplete.classList.toggle('d-none', !(data.itens || []).length);
        }, 250);
    });

    document.addEventListener('click', (e) => {
        if (!campoNome.contains(e.target) && !listaAutocomplete.contains(e.target)) {
            listaAutocomplete.classList.add('d-none');
        }
    });

    document.getElementById('formEntrada').addEventListener('submit', function (e) {
        if (itens.length === 0) {
            e.preventDefault();
            alert('Adicione ao menos um medicamento.');
        }
    });

    campoBipar.focus();
    atualizarMsgLista();
})();
</script>
@endpush

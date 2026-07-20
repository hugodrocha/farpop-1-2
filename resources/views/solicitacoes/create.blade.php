@extends('layouts.app')

@section('title', 'Nova solicitação')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-clipboard-list me-2"></i>Nova solicitação</h1>
        <p class="farpop-page-subtitle">Informe os medicamentos que a unidade precisa receber da central. Digite o nome, escolha na lista ou bipar o código.</p>
    </div>

    @include('partials.flash')

    <form method="POST" action="{{ route('solicitacoes.store') }}" id="formSol">
        @csrf
        <div class="card mb-4">
            <div class="card-body">
                <label class="form-label">Observação</label>
                <textarea name="observacao" class="form-control" rows="2" maxlength="500">{{ old('observacao') }}</textarea>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary"><i class="fas fa-capsules me-2"></i>Medicamentos</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label"><i class="fas fa-barcode me-1"></i> Código / código de barras</label>
                        <input type="text" id="campoBipar" class="form-control form-control-lg" placeholder="Bipe ou digite o código REMUME e pressione Enter" autocomplete="off">
                        <div id="msgBipar" class="small mt-1 text-muted"></div>
                    </div>
                    <div class="col-md-6 position-relative">
                        <label class="form-label"><i class="fas fa-search me-1"></i> Nome do medicamento</label>
                        <input type="text" id="campoNome" class="form-control form-control-lg" placeholder="Digite para autocompletar..." autocomplete="off">
                        <div id="listaAutocomplete" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index:20;max-height:220px;overflow:auto"></div>
                    </div>
                </div>

                <p id="msgLista" class="small text-muted mb-2">Nenhum medicamento adicionado.</p>

                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Medicamento</th>
                                <th style="width:120px">Qtd</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody id="corpoMeds"></tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="{{ route('solicitacoes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary" id="btnSalvar" disabled>Enviar solicitação</button>
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
    const corpo = document.getElementById('corpoMeds');
    const btnSalvar = document.getElementById('btnSalvar');
    const campoBipar = document.getElementById('campoBipar');
    const campoNome = document.getElementById('campoNome');
    const lista = document.getElementById('listaAutocomplete');
    const msgLista = document.getElementById('msgLista');
    const msgBipar = document.getElementById('msgBipar');
    const itens = [];
    let timerAuto = null;

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s ?? '';
        return d.innerHTML;
    }

    function atualizarMsgLista() {
        msgLista.textContent = itens.length
            ? itens.length + ' medicamento(s) na solicitação.'
            : 'Nenhum medicamento adicionado.';
        btnSalvar.disabled = itens.length === 0;
    }

    function renderTabela() {
        corpo.innerHTML = '';
        itens.forEach((item, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><code>${esc(item.codigo || '—')}</code></td>
                <td class="fw-medium">${esc(item.label)}</td>
                <td>
                    <input type="hidden" name="med_id[]" value="${item.id}">
                    <input type="number" name="med_qtd[]" class="form-control form-control-sm" min="1" value="${item.qtd}" required data-idx="${idx}">
                </td>
                <td><button type="button" class="btn btn-sm btn-outline-danger btn-rem" data-idx="${idx}"><i class="fas fa-trash"></i></button></td>`;
            corpo.appendChild(tr);
        });

        corpo.querySelectorAll('input[data-idx]').forEach(inp => {
            inp.addEventListener('change', function () {
                const idx = parseInt(this.dataset.idx, 10);
                itens[idx].qtd = Math.max(1, parseInt(this.value, 10) || 1);
            });
        });

        corpo.querySelectorAll('.btn-rem').forEach(btn => {
            btn.addEventListener('click', function () {
                itens.splice(parseInt(this.dataset.idx, 10), 1);
                renderTabela();
                atualizarMsgLista();
            });
        });

        atualizarMsgLista();
    }

    function adicionarMedicamento(med, incrementar) {
        const item = {
            id: med.id,
            label: med.label || (med.nome + (med.dosagem ? ' — ' + med.dosagem : '')),
            codigo: med.codigo,
            qtd: 1,
        };

        const existente = itens.find(i => i.id === item.id);
        if (existente && incrementar !== false) {
            existente.qtd += 1;
            msgBipar.className = 'small mt-1 text-success';
            msgBipar.textContent = 'Quantidade incrementada: ' + item.label;
            renderTabela();
            return;
        }
        if (existente) {
            msgBipar.className = 'small mt-1 text-warning';
            msgBipar.textContent = 'Medicamento já está na lista.';
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
        const res = await fetch(urlBuscar + '?q=' + encodeURIComponent(termo), {
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

    campoNome.addEventListener('input', () => {
        clearTimeout(timerAuto);
        const termo = campoNome.value.trim();
        if (termo.length < 2) {
            lista.classList.add('d-none');
            return;
        }
        timerAuto = setTimeout(async () => {
            try {
                const res = await fetch(urlAutocomplete + '?q=' + encodeURIComponent(termo), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                lista.innerHTML = '';
                (data.itens || []).forEach((med) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action';
                    btn.innerHTML = `<strong>${esc(med.label)}</strong><br><small class="text-muted">Código: ${esc(med.codigo || '—')}</small>`;
                    btn.addEventListener('click', () => {
                        adicionarMedicamento(med, false);
                        campoNome.value = '';
                        lista.classList.add('d-none');
                        msgBipar.textContent = '';
                    });
                    lista.appendChild(btn);
                });
                lista.classList.toggle('d-none', !(data.itens || []).length);
            } catch (err) {
                lista.classList.add('d-none');
            }
        }, 250);
    });

    campoNome.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const first = lista.querySelector('.list-group-item');
            if (first) first.click();
        }
    });

    document.addEventListener('click', (e) => {
        if (!campoNome.contains(e.target) && !lista.contains(e.target)) {
            lista.classList.add('d-none');
        }
    });

    document.getElementById('formSol').addEventListener('submit', (e) => {
        if (itens.length === 0) {
            e.preventDefault();
            alert('Adicione ao menos um medicamento à solicitação.');
        }
    });

    renderTabela();
    campoBipar.focus();
})();
</script>
@endpush

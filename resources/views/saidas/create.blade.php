@extends('layouts.app')

@section('title', 'Dispensa para paciente')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-pills me-2"></i>Dispensa para paciente</h1>
        <p class="farpop-page-subtitle">Bipe o código REMUME ou código de barras, ou busque pelo nome do medicamento.</p>
    </div>

    @include('partials.flash')

    @if(!($pecStatus['ok'] ?? false))
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
            <i class="fas fa-exclamation-triangle"></i>
            <span>PEC indisponível: {{ $pecStatus['motivo'] ?? 'integração desabilitada' }}. Preencha os dados manualmente.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('saidas.store') }}" id="formSaida">
        @csrf

        <div class="card mb-4">
            <div class="card-header bg-primary"><i class="fas fa-user me-2"></i>Dados do paciente</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">CPF</label>
                        <div class="input-group">
                            <input type="text" name="paciente_cpf" id="pacienteCpf" class="form-control" value="{{ old('paciente_cpf') }}" maxlength="11" required placeholder="Somente números">
                            <button type="button" class="btn btn-outline-primary" id="btnPec" {{ !($pecStatus['ok'] ?? false) ? 'disabled' : '' }} title="Buscar no PEC">
                                <i class="fas fa-search"></i> PEC
                            </button>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nome do paciente</label>
                        <input type="text" name="paciente_nome" id="pacienteNome" class="form-control" value="{{ old('paciente_nome') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Data de nascimento</label>
                        <input type="text" name="paciente_nascimento" id="pacienteNascimento" class="form-control" value="{{ old('paciente_nascimento') }}" placeholder="DD/MM/AAAA">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="paciente_telefone" id="pacienteTelefone" class="form-control" value="{{ old('paciente_telefone') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cartão SUS</label>
                        <input type="text" name="paciente_sus" id="pacienteSus" class="form-control" value="{{ old('paciente_sus') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Endereço</label>
                        <input type="text" name="paciente_endereco" id="pacienteEndereco" class="form-control" value="{{ old('paciente_endereco') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Prescritor</label>
                        <input type="text" name="prescritor" class="form-control" value="{{ old('prescritor') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nº solicitação</label>
                        <input type="text" name="numero_solicitacao" class="form-control" value="{{ old('numero_solicitacao') }}">
                    </div>
                    <div class="col-md-4">
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
                </div>
                <div id="msgPec" class="small mt-2 text-muted"></div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary"><i class="fas fa-barcode me-2"></i>Medicamentos</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label">Bipar código / código de barras</label>
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
                                <th>Medicamento</th>
                                <th>Código</th>
                                <th style="width:120px">Estoque</th>
                                <th style="width:120px">Quantidade</th>
                                <th>Observação</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody id="corpoMeds"></tbody>
                    </table>
                </div>
                <div id="msgLista" class="text-muted small mt-2">Nenhum medicamento adicionado.</div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-check me-1"></i> Registrar dispensação</button>
            <a href="{{ route('saidas.index') }}" class="btn btn-outline-secondary btn-lg">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const urlBuscar = @json(route('medicamentos.buscar'));
    const urlAutocomplete = @json(route('medicamentos.autocomplete'));
    const urlPec = @json(route('pec.cidadao'));
    const corpoMeds = document.getElementById('corpoMeds');
    const campoBipar = document.getElementById('campoBipar');
    const campoNome = document.getElementById('campoNome');
    const listaAutocomplete = document.getElementById('listaAutocomplete');
    const msgLista = document.getElementById('msgLista');
    const msgBipar = document.getElementById('msgBipar');
    const clinicaEl = document.getElementById('clinicaId');
    const itens = new Map();

    function clinicaParam() {
        const id = clinicaEl ? clinicaEl.value : '';
        return id ? '&clinica_id=' + encodeURIComponent(id) : '';
    }

    function atualizarMsgLista() {
        msgLista.textContent = itens.size ? itens.size + ' medicamento(s) na lista.' : 'Nenhum medicamento adicionado.';
    }

    function renderTabela() {
        corpoMeds.innerHTML = '';
        itens.forEach((item) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="fw-medium">${item.label}</td>
                <td><code>${item.codigo || '—'}</code></td>
                <td><span class="badge bg-secondary">${item.estoque ?? '—'}</span></td>
                <td>
                    <input type="hidden" name="med_id[]" value="${item.id}">
                    <input type="number" name="med_qtd[]" class="form-control form-control-sm" min="1" value="${item.qtd}" required>
                </td>
                <td><input type="text" name="med_obs[]" class="form-control form-control-sm" value="${item.obs || ''}"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger btn-remover"><i class="fas fa-trash"></i></button></td>`;
            tr.querySelector('.btn-remover').addEventListener('click', () => {
                itens.delete(item.id);
                renderTabela();
                atualizarMsgLista();
            });
            tr.querySelector('input[name="med_qtd[]"]').addEventListener('change', (e) => {
                item.qtd = Math.max(1, parseInt(e.target.value || '1', 10));
            });
            tr.querySelector('input[name="med_obs[]"]').addEventListener('input', (e) => {
                item.obs = e.target.value;
            });
            corpoMeds.appendChild(tr);
        });
        atualizarMsgLista();
    }

    function adicionarMedicamento(med) {
        if (itens.has(med.id)) {
            const atual = itens.get(med.id);
            atual.qtd += 1;
            itens.set(med.id, atual);
            msgBipar.className = 'small mt-1 text-success';
            msgBipar.textContent = 'Quantidade incrementada: ' + med.label;
        } else {
            itens.set(med.id, {
                id: med.id,
                label: med.label,
                codigo: med.codigo,
                estoque: med.estoque,
                qtd: 1,
                obs: ''
            });
            msgBipar.className = 'small mt-1 text-success';
            msgBipar.textContent = 'Adicionado: ' + med.label;
        }
        renderTabela();
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
            adicionarMedicamento(data.medicamento);
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
                btn.innerHTML = `<strong>${med.label}</strong><br><small class="text-muted">Código: ${med.codigo || '—'} | Estoque: ${med.estoque ?? '—'}</small>`;
                btn.addEventListener('click', () => {
                    adicionarMedicamento(med);
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

    document.getElementById('btnPec').addEventListener('click', async function () {
        const cpf = document.getElementById('pacienteCpf').value.replace(/\D/g, '');
        const msg = document.getElementById('msgPec');
        if (cpf.length !== 11) {
            msg.className = 'small mt-2 text-danger';
            msg.textContent = 'Informe um CPF válido (11 dígitos).';
            return;
        }
        msg.className = 'small mt-2 text-muted';
        msg.textContent = 'Consultando PEC...';
        try {
            const res = await fetch(urlPec + '?cpf=' + cpf, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.ok) {
                msg.className = 'small mt-2 text-danger';
                msg.textContent = data.erro || 'Cidadão não encontrado.';
                return;
            }
            const c = data.cidadao;
            document.getElementById('pacienteNome').value = c.nome || '';
            document.getElementById('pacienteNascimento').value = c.data_nascimento || '';
            document.getElementById('pacienteTelefone').value = c.telefone || '';
            document.getElementById('pacienteSus').value = c.cartao_sus || '';
            document.getElementById('pacienteEndereco').value = c.endereco || '';
            msg.className = 'small mt-2 text-success';
            msg.textContent = 'Dados carregados do PEC (' + (c.fonte || 'e-SUS') + ').';
        } catch (e) {
            msg.className = 'small mt-2 text-danger';
            msg.textContent = 'Erro ao consultar PEC.';
        }
    });

    campoBipar.focus();
})();
</script>
@endpush

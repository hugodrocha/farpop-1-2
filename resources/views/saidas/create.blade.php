@extends('layouts.app')

@section('title', 'Registrar saída')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-arrow-up me-2"></i>Registrar saída</h1>
        <p class="farpop-page-subtitle">Dispensação de medicamentos para paciente — múltiplos itens por atendimento.</p>
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
                            <input type="hidden" name="clinica_id" value="{{ session('clinica_id') }}">
                            <input type="text" class="form-control" value="{{ session('clinica_nome') }}" readonly>
                        @else
                            <select name="clinica_id" class="form-select" required>
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
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <span><i class="fas fa-pills me-2"></i>Medicamentos</span>
                <button type="button" class="btn btn-sm btn-light" id="btnAddMed"><i class="fas fa-plus me-1"></i> Adicionar linha</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="tabelaMeds">
                        <thead>
                            <tr>
                                <th>Medicamento</th>
                                <th style="width: 120px;">Quantidade</th>
                                <th>Observação</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="corpoMeds"></tbody>
                    </table>
                </div>
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
    const medicamentos = @json($medicamentos);
    const urlPec = @json(route('pec.cidadao'));
    const corpoMeds = document.getElementById('corpoMeds');
    const oldIds = @json(old('med_id', []));
    const oldQtds = @json(old('med_qtd', []));
    const oldObs = @json(old('med_obs', []));

    function optionsHtml(selected) {
        let html = '<option value="">Selecione...</option>';
        medicamentos.forEach(m => {
            const sel = selected == m.id ? ' selected' : '';
            html += `<option value="${m.id}"${sel}>${m.nome} ${m.dosagem || ''} (${m.unidade || ''})</option>`;
        });
        return html;
    }

    function addLinha(medId, qtd, obs) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="med_id[]" class="form-select form-select-sm" required>${optionsHtml(medId || '')}</select>
            </td>
            <td>
                <input type="number" name="med_qtd[]" class="form-control form-control-sm" min="1" value="${qtd || 1}" required>
            </td>
            <td>
                <input type="text" name="med_obs[]" class="form-control form-control-sm" value="${obs || ''}">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remover"><i class="fas fa-trash"></i></button>
            </td>`;
        tr.querySelector('.btn-remover').addEventListener('click', () => {
            if (corpoMeds.children.length > 1) tr.remove();
        });
        corpoMeds.appendChild(tr);
    }

    if (oldIds.length) {
        oldIds.forEach((id, i) => addLinha(id, oldQtds[i], oldObs[i]));
    } else {
        addLinha('', 1, '');
    }

    document.getElementById('btnAddMed').addEventListener('click', () => addLinha('', 1, ''));

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
})();
</script>
@endpush

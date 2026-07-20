@extends('layouts.app')

@section('title', 'Nova solicitação')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-clipboard-list me-2"></i>Nova solicitação</h1>
        <p class="farpop-page-subtitle">Informe os medicamentos que a unidade precisa receber da central.</p>
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
                    <div class="col-md-8 position-relative">
                        <label class="form-label">Buscar medicamento</label>
                        <input type="text" id="campoNome" class="form-control" placeholder="Digite o nome..." autocomplete="off">
                        <div id="listaAutocomplete" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index:20;max-height:200px;overflow:auto"></div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0" id="tabelaMeds">
                        <thead><tr><th>Medicamento</th><th style="width:120px">Qtd</th><th style="width:50px"></th></tr></thead>
                        <tbody id="corpoMeds">
                            <tr id="linhaVazia"><td colspan="3" class="text-muted text-center py-3">Adicione medicamentos à solicitação.</td></tr>
                        </tbody>
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
    const corpo = document.getElementById('corpoMeds');
    const linhaVazia = document.getElementById('linhaVazia');
    const btnSalvar = document.getElementById('btnSalvar');
    const campoNome = document.getElementById('campoNome');
    const lista = document.getElementById('listaAutocomplete');
    let timer;

    function atualizarBtn() {
        btnSalvar.disabled = corpo.querySelectorAll('tr[data-id]').length === 0;
    }

    function adicionar(m) {
        if (corpo.querySelector(`tr[data-id="${m.id}"]`)) return;
        linhaVazia?.remove();
        const tr = document.createElement('tr');
        tr.dataset.id = m.id;
        tr.innerHTML = `<td>${m.nome}${m.dosagem ? ' — ' + m.dosagem : ''}<input type="hidden" name="med_id[]" value="${m.id}"></td>
            <td><input type="number" name="med_qtd[]" class="form-control" min="1" value="1" required></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger btn-rem">&times;</button></td>`;
        tr.querySelector('.btn-rem').onclick = () => { tr.remove(); if (!corpo.querySelector('tr[data-id]')) corpo.appendChild(linhaVazia); atualizarBtn(); };
        corpo.appendChild(tr);
        atualizarBtn();
        campoNome.value = '';
        lista.classList.add('d-none');
    }

    campoNome.addEventListener('input', () => {
        clearTimeout(timer);
        const q = campoNome.value.trim();
        if (q.length < 2) { lista.classList.add('d-none'); return; }
        timer = setTimeout(() => {
            fetch(`{{ route('medicamentos.autocomplete') }}?q=${encodeURIComponent(q)}`)
                .then(r => r.json()).then(arr => {
                    lista.innerHTML = '';
                    arr.forEach(m => {
                        const a = document.createElement('button');
                        a.type = 'button';
                        a.className = 'list-group-item list-group-item-action';
                        a.textContent = m.nome + (m.dosagem ? ' — ' + m.dosagem : '');
                        a.onclick = () => adicionar(m);
                        lista.appendChild(a);
                    });
                    lista.classList.toggle('d-none', arr.length === 0);
                });
        }, 250);
    });
})();
</script>
@endpush

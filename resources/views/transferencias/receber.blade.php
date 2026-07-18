@extends('layouts.app')

@section('title', 'Receber transferência')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-barcode me-2"></i>Receber transferência</h1>
        <p class="farpop-page-subtitle">Escaneie ou informe o código da nota de transferência para confirmar o recebimento.</p>
    </div>

    @include('partials.flash')

    <div class="card mb-4">
        <div class="card-header bg-primary"><i class="fas fa-search me-2"></i>Buscar nota</div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Código da nota</label>
                    <input type="text" id="inputCodigo" class="form-control form-control-lg" value="{{ $codigo ?? '' }}" placeholder="Código de barras ou número da nota" autofocus>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-primary btn-lg w-100" id="btnBuscar">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                </div>
            </div>
            <div id="msgBusca" class="mt-2 small text-muted"></div>
        </div>
    </div>

    <div id="painelRecebimento" class="d-none">
        <form method="POST" action="{{ route('transferencias.receber.confirmar') }}" id="formReceber">
            @csrf
            <input type="hidden" name="transferencia_id" id="transferenciaId">
            <input type="hidden" name="recebidas_json" id="recebidasJson">

            <div class="card mb-4">
                <div class="card-header bg-primary"><i class="fas fa-file-alt me-2"></i>Dados da nota</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Código</span>
                            <strong id="infoCodigo">—</strong>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Origem</span>
                            <strong id="infoOrigem">—</strong>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Destino</span>
                            <strong id="infoDestino">—</strong>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Enviado por</span>
                            <strong id="infoEnviadoPor">—</strong>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Data envio</span>
                            <strong id="infoEnviadoEm">—</strong>
                        </div>
                        <div class="col-md-9">
                            <span class="text-muted small d-block">Observação</span>
                            <span id="infoObservacao">—</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary"><i class="fas fa-pills me-2"></i>Itens — informe quantidade recebida</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Medicamento</th>
                                    <th>Cód. barras</th>
                                    <th style="width: 120px;">Enviado</th>
                                    <th style="width: 130px;">Recebido</th>
                                </tr>
                            </thead>
                            <tbody id="corpoItens"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <label class="form-label">Observação do recebimento</label>
                    <textarea name="observacao_recebimento" class="form-control" rows="2" maxlength="500"></textarea>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-check me-1"></i> Confirmar recebimento
                </button>
                <a href="{{ route('transferencias.transito') }}" class="btn btn-outline-secondary btn-lg">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const urlBuscar = @json(route('transferencias.receber.buscar'));
    const inputCodigo = document.getElementById('inputCodigo');
    const msgBusca = document.getElementById('msgBusca');
    const painel = document.getElementById('painelRecebimento');
    const corpoItens = document.getElementById('corpoItens');
    const recebidasJson = document.getElementById('recebidasJson');
    let itensAtuais = [];

    function formatarData(iso) {
        if (!iso) return '—';
        const d = new Date(iso);
        if (isNaN(d)) return iso;
        return d.toLocaleString('pt-BR');
    }

    async function buscarNota() {
        const codigo = inputCodigo.value.trim();
        if (!codigo) {
            msgBusca.className = 'mt-2 small text-danger';
            msgBusca.textContent = 'Informe o código da nota.';
            return;
        }

        msgBusca.className = 'mt-2 small text-muted';
        msgBusca.textContent = 'Buscando...';
        painel.classList.add('d-none');

        try {
            const res = await fetch(urlBuscar + '?codigo=' + encodeURIComponent(codigo), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (!data.ok) {
                msgBusca.className = 'mt-2 small text-danger';
                msgBusca.textContent = data.erro || 'Nota não encontrada.';
                return;
            }

            const t = data.transferencia;
            document.getElementById('transferenciaId').value = t.id;
            document.getElementById('infoCodigo').textContent = t.codigo_nota || t.id;
            document.getElementById('infoOrigem').textContent = t.clinica_origem;
            document.getElementById('infoDestino').textContent = t.clinica_destino;
            document.getElementById('infoEnviadoPor').textContent = t.enviado_por || '—';
            document.getElementById('infoEnviadoEm').textContent = formatarData(t.enviado_em);
            document.getElementById('infoObservacao').textContent = t.observacao || '—';

            itensAtuais = data.itens || [];
            corpoItens.innerHTML = '';
            itensAtuais.forEach((item, idx) => {
                const qtdEnviada = item.quantidade_enviada ?? item.quantidade ?? 0;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <div class="fw-medium">${item.nome} ${item.dosagem || ''}</div>
                        <div class="text-muted small">${item.unidade || ''}</div>
                    </td>
                    <td class="small text-muted">${item.codigo_barras || '—'}</td>
                    <td><span class="badge bg-primary">${qtdEnviada}</span></td>
                    <td>
                        <input type="number" class="form-control form-control-sm qtd-recebida"
                               min="0" max="${qtdEnviada}" value="${qtdEnviada}"
                               data-item-id="${item.id}" data-idx="${idx}">
                    </td>`;
                corpoItens.appendChild(tr);
            });

            painel.classList.remove('d-none');
            msgBusca.className = 'mt-2 small text-success';
            msgBusca.textContent = 'Nota encontrada. Confira os itens e confirme o recebimento.';
        } catch (e) {
            msgBusca.className = 'mt-2 small text-danger';
            msgBusca.textContent = 'Erro ao buscar nota.';
        }
    }

    document.getElementById('btnBuscar').addEventListener('click', buscarNota);
    inputCodigo.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarNota();
        }
    });

    document.getElementById('formReceber').addEventListener('submit', function () {
        const recebidas = {};
        corpoItens.querySelectorAll('.qtd-recebida').forEach(inp => {
            recebidas[inp.dataset.itemId] = parseInt(inp.value, 10) || 0;
        });
        recebidasJson.value = JSON.stringify(recebidas);
    });

    @if(!empty($codigo))
        buscarNota();
    @endif
})();
</script>
@endpush

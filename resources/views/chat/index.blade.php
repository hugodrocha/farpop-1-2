@extends('layouts.app')

@section('title', 'Chat')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <h1 class="farpop-page-title"><i class="fas fa-comments me-2"></i>Chat</h1>
        <p class="farpop-page-subtitle">Comunicação interna entre usuários do FARPOP.</p>
    </div>

    @include('partials.flash')

    <div class="card" style="height: calc(100vh - 220px); min-height: 420px;">
        <div class="card-header bg-primary d-flex flex-wrap gap-2 align-items-center">
            <span><i class="fas fa-hashtag me-1"></i> Conversa:</span>
            <select id="selectAlvo" class="form-select form-select-sm" style="max-width: 280px;">
                <option value="geral">Canal geral</option>
            </select>
            <span class="badge bg-light text-dark ms-auto" id="statusPoll">Conectado</span>
        </div>
        <div class="card-body d-flex flex-column p-0" style="overflow: hidden;">
            <div id="areaMensagens" class="flex-grow-1 p-3" style="overflow-y: auto; background: rgba(248,250,252,0.8);">
                <div class="text-center text-muted py-4">Carregando mensagens...</div>
            </div>
            <div class="border-top p-3 bg-white">
                <form id="formEnviar" class="d-flex gap-2">
                    @csrf
                    <input type="text" id="inputMensagem" class="form-control" placeholder="Digite sua mensagem..." maxlength="2000" autocomplete="off">
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const urlMensagens = @json(route('chat.mensagens'));
    const urlEnviar = @json(route('chat.enviar'));
    const urlUsuarios = @json(route('chat.usuarios'));
    const meuId = @json((int) session('usuario_id'));
    const area = document.getElementById('areaMensagens');
    const selectAlvo = document.getElementById('selectAlvo');
    const statusPoll = document.getElementById('statusPoll');
    let ultimoCount = 0;

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function formatarHora(iso) {
        if (!iso) return '';
        const d = new Date(iso);
        return isNaN(d) ? iso : d.toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
    }

    function renderizarMensagens(lista) {
        if (!lista.length) {
            area.innerHTML = '<div class="text-center text-muted py-4">Nenhuma mensagem ainda. Seja o primeiro!</div>';
            return;
        }
        area.innerHTML = lista.map(m => {
            const propria = m.remetente_id == meuId;
            return `
                <div class="mb-3 ${propria ? 'text-end' : ''}">
                    <div class="d-inline-block text-start p-2 px-3 rounded-3 ${propria ? 'bg-primary text-white' : 'bg-white border'}" style="max-width: 75%;">
                        <div class="small fw-semibold ${propria ? 'text-white-50' : 'text-muted'}">${esc(m.remetente_nome || 'Usuário')}</div>
                        <div>${esc(m.mensagem)}</div>
                        <div class="small ${propria ? 'text-white-50' : 'text-muted'} mt-1">${formatarHora(m.enviado_em)}</div>
                    </div>
                </div>`;
        }).join('');
        if (lista.length !== ultimoCount) {
            area.scrollTop = area.scrollHeight;
            ultimoCount = lista.length;
        }
    }

    async function carregarMensagens() {
        try {
            const alvo = selectAlvo.value;
            const res = await fetch(urlMensagens + '?alvo=' + encodeURIComponent(alvo), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.ok) {
                renderizarMensagens(data.mensagens || []);
                statusPoll.className = 'badge bg-success ms-auto';
                statusPoll.textContent = 'Atualizado';
            }
        } catch (e) {
            statusPoll.className = 'badge bg-danger ms-auto';
            statusPoll.textContent = 'Erro';
        }
    }

    async function carregarUsuarios() {
        try {
            const res = await fetch(urlUsuarios, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (Array.isArray(data.usuarios)) {
                data.usuarios.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.nome_completo || u.usuario;
                    selectAlvo.appendChild(opt);
                });
            }
        } catch (e) {}
    }

    document.getElementById('formEnviar').addEventListener('submit', async function (e) {
        e.preventDefault();
        const input = document.getElementById('inputMensagem');
        const msg = input.value.trim();
        if (!msg) return;

        try {
            const res = await fetch(urlEnviar, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ alvo: selectAlvo.value, mensagem: msg })
            });
            const data = await res.json();
            if (data.ok) {
                input.value = '';
                carregarMensagens();
            }
        } catch (e) {}
    });

    selectAlvo.addEventListener('change', () => {
        ultimoCount = 0;
        carregarMensagens();
    });

    carregarUsuarios();
    carregarMensagens();
    setInterval(carregarMensagens, 4000);
})();
</script>
@endpush

@extends('layouts.app')

@section('title', 'Chat')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-2">
        <div>
            <h1 class="farpop-page-title"><i class="fas fa-comments me-2"></i>Chat</h1>
            <p class="farpop-page-subtitle">Comunicação interna entre usuários do FARPOP. Online no topo, offline abaixo.</p>
        </div>
        <button type="button" id="btnNovoGrupo" class="btn btn-outline-primary btn-sm"><i class="fas fa-users me-1"></i> Novo grupo</button>
    </div>

    @include('partials.flash')

    <div id="grupoFormPage" class="card mb-3 d-none">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">Nome do grupo</label>
                    <input type="text" id="grupoTituloPage" class="form-control form-control-sm" maxlength="180">
                </div>
                <div class="col-md-6">
                    <label class="form-label mb-1">Participantes</label>
                    <div id="grupoMembrosPage" class="d-flex flex-wrap gap-2"></div>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="button" id="grupoCriarPage" class="btn btn-sm btn-primary flex-grow-1">Criar</button>
                    <button type="button" id="grupoCancelarPage" class="btn btn-sm btn-outline-secondary">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-0">
        <div class="farpop-chat-page">
            <aside class="farpop-chat-page-sidebar">
                <div id="listaGruposPage"></div>
                <div class="farpop-chat-secao-titulo">Online</div>
                <div id="listaOnlinePage" class="farpop-chat-contatos"></div>
                <div class="farpop-chat-secao-titulo farpop-chat-secao-offline">Offline</div>
                <div id="listaOfflinePage" class="farpop-chat-contatos"></div>
            </aside>
            <div class="farpop-chat-page-main">
                <div id="tituloConversaPage" class="farpop-chat-conversa-titulo px-3">Selecione um contato ou grupo</div>
                <div id="areaMensagensPage" class="farpop-chat-mensagens">
                    <div class="farpop-chat-vazio">Escolha alguém para conversar.</div>
                </div>
                <form id="formEnviarPage" class="farpop-chat-form">
                    @csrf
                    <input type="text" id="inputMensagemPage" class="form-control" placeholder="Digite sua mensagem..." maxlength="4000" autocomplete="off" disabled>
                    <button type="submit" class="btn btn-primary px-3" disabled><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const apiUrl = @json(route('chat.api'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const meuId = @json((int) session('usuario_id'));

    let conversaAtual = null;
    let sinceId = 0;
    let contatosCache = [];

    const elOnline = document.getElementById('listaOnlinePage');
    const elOffline = document.getElementById('listaOfflinePage');
    const elGrupos = document.getElementById('listaGruposPage');
    const elMensagens = document.getElementById('areaMensagensPage');
    const elTitulo = document.getElementById('tituloConversaPage');
    const inputMsg = document.getElementById('inputMensagemPage');
    const btnEnviar = document.querySelector('#formEnviarPage button');

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s ?? '';
        return d.innerHTML;
    }

    function apiCall(acao, params, method) {
        method = method || 'GET';
        const url = new URL(apiUrl, window.location.origin);
        url.searchParams.set('acao', acao);
        const opts = { method, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };
        if (method === 'POST') {
            opts.headers['X-CSRF-TOKEN'] = csrf;
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(params || {});
        } else if (params) {
            Object.keys(params).forEach(k => url.searchParams.set(k, params[k]));
        }
        return fetch(url, opts).then(r => r.json());
    }

    function renderContato(c, ativo) {
        const dot = c.online ? 'farpop-chat-dot-online' : 'farpop-chat-dot-offline';
        const badge = c.nao_lidas > 0 ? `<span class="farpop-chat-contato-badge">${c.nao_lidas > 99 ? '99+' : c.nao_lidas}</span>` : '';
        return `<button type="button" class="farpop-chat-contato-item ${ativo ? 'ativo' : ''}" data-tipo="privada" data-id="${c.id}" data-conversa="${c.conversa_id}" data-nome="${esc(c.nome_completo || c.usuario)}">
            <span class="farpop-chat-dot ${dot}"></span>
            <span class="farpop-chat-contato-info">
                <span class="farpop-chat-contato-nome">${esc(c.nome_completo || c.usuario)}</span>
                <span class="farpop-chat-contato-setor">${esc(c.setor || c.perfil || '')}</span>
            </span>${badge}
        </button>`;
    }

    function renderGrupo(g, ativo) {
        const badge = g.nao_lidas > 0 ? `<span class="farpop-chat-contato-badge">${g.nao_lidas > 99 ? '99+' : g.nao_lidas}</span>` : '';
        return `<button type="button" class="farpop-chat-contato-item ${ativo ? 'ativo' : ''}" data-tipo="grupo" data-conversa="${g.conversa_id}" data-nome="${esc(g.titulo)}">
            <span class="farpop-chat-grupo-ico"><i class="fas fa-users"></i></span>
            <span class="farpop-chat-contato-info">
                <span class="farpop-chat-contato-nome">${esc(g.titulo)}</span>
                <span class="farpop-chat-contato-setor">${g.membros} participantes</span>
            </span>${badge}
        </button>`;
    }

    function bindClicks() {
        document.querySelectorAll('.farpop-chat-contato-item').forEach(btn => {
            btn.onclick = () => {
                conversaAtual = {
                    tipo: btn.dataset.tipo,
                    id: btn.dataset.tipo === 'privada' ? parseInt(btn.dataset.id, 10) : null,
                    conversa_id: parseInt(btn.dataset.conversa, 10),
                    nome: btn.dataset.nome
                };
                sinceId = 0;
                elTitulo.textContent = conversaAtual.nome;
                inputMsg.disabled = false;
                btnEnviar.disabled = false;
                inputMsg.focus();
                carregarMensagens(true);
                carregarContatos();
            };
        });
    }

    function carregarContatos() {
        return apiCall('contatos').then(data => {
            if (!data.ok) return;
            contatosCache = data.contatos || [];
            const online = contatosCache.filter(c => c.online);
            const offline = contatosCache.filter(c => !c.online);
            const grupos = data.grupos || [];

            elGrupos.innerHTML = grupos.length
                ? '<div class="farpop-chat-secao-titulo">Grupos</div>' + grupos.map(g => renderGrupo(g, conversaAtual && conversaAtual.conversa_id == g.conversa_id)).join('')
                : '';

            elOnline.innerHTML = online.length
                ? online.map(c => renderContato(c, conversaAtual && conversaAtual.tipo === 'privada' && conversaAtual.id == c.id)).join('')
                : '<div class="farpop-chat-sem-contatos">Ninguém online</div>';

            elOffline.innerHTML = offline.length
                ? offline.map(c => renderContato(c, conversaAtual && conversaAtual.tipo === 'privada' && conversaAtual.id == c.id)).join('')
                : '<div class="farpop-chat-sem-contatos">—</div>';

            bindClicks();
            atualizarGrupoMembros();
        });
    }

    function renderMensagens(lista, reset) {
        if (reset) elMensagens.innerHTML = '';
        if (!lista.length && reset) {
            elMensagens.innerHTML = '<div class="farpop-chat-vazio">Nenhuma mensagem ainda.</div>';
            return;
        }
        elMensagens.querySelector('.farpop-chat-vazio')?.remove();
        lista.forEach(m => {
            const eu = m.remetente_id === meuId || m.eu;
            const div = document.createElement('div');
            div.className = 'farpop-chat-msg ' + (eu ? 'eu' : 'outro');
            div.innerHTML = `<div class="farpop-chat-msg-bolha">
                ${conversaAtual && conversaAtual.tipo === 'grupo' && !eu ? `<div class="farpop-chat-msg-autor">${esc(m.remetente_nome || 'Usuário')}</div>` : ''}
                <div>${esc(m.corpo || m.mensagem)}</div>
                <div class="farpop-chat-msg-hora">${esc(m.criado_em || '')}</div>
            </div>`;
            elMensagens.appendChild(div);
            sinceId = Math.max(sinceId, m.id);
        });
        elMensagens.scrollTop = elMensagens.scrollHeight;
    }

    function carregarMensagens(reset) {
        if (!conversaAtual) return Promise.resolve();
        const params = { conversa_id: conversaAtual.conversa_id, since_id: reset ? 0 : sinceId };
        if (conversaAtual.tipo === 'privada' && conversaAtual.id) params.outro_id = conversaAtual.id;
        return apiCall('mensagens', params).then(data => {
            if (!data.ok) return;
            if (data.conversa_id) conversaAtual.conversa_id = data.conversa_id;
            renderMensagens(data.mensagens || [], !!reset);
            if (reset) apiCall('marcar_lido', { conversa_id: conversaAtual.conversa_id });
        });
    }

    document.getElementById('formEnviarPage').addEventListener('submit', function (e) {
        e.preventDefault();
        const texto = inputMsg.value.trim();
        if (!texto || !conversaAtual) return;
        const payload = { texto, conversa_id: conversaAtual.conversa_id };
        if (conversaAtual.tipo === 'privada' && conversaAtual.id) payload.outro_id = conversaAtual.id;
        apiCall('enviar', payload, 'POST').then(data => {
            if (data.ok && data.mensagem) {
                inputMsg.value = '';
                renderMensagens([{ ...data.mensagem, remetente_id: meuId, eu: true }], false);
                carregarContatos();
            }
        });
    });

    function atualizarGrupoMembros() {
        const box = document.getElementById('grupoMembrosPage');
        if (!box) return;
        box.innerHTML = contatosCache.map(c =>
            `<label class="farpop-chat-grupo-check"><input type="checkbox" value="${c.id}"> ${esc(c.nome_completo || c.usuario)}</label>`
        ).join('');
    }

    document.getElementById('btnNovoGrupo').addEventListener('click', () => {
        document.getElementById('grupoFormPage').classList.toggle('d-none');
        atualizarGrupoMembros();
    });
    document.getElementById('grupoCancelarPage').addEventListener('click', () => {
        document.getElementById('grupoFormPage').classList.add('d-none');
    });
    document.getElementById('grupoCriarPage').addEventListener('click', () => {
        const titulo = document.getElementById('grupoTituloPage').value.trim();
        const membros = [...document.querySelectorAll('#grupoMembrosPage input:checked')].map(i => parseInt(i.value, 10));
        if (!titulo || !membros.length) return;
        apiCall('criar_grupo', { titulo, membros }, 'POST').then(data => {
            if (data.ok) {
                document.getElementById('grupoFormPage').classList.add('d-none');
                conversaAtual = { tipo: 'grupo', id: null, conversa_id: data.conversa_id, nome: titulo };
                sinceId = 0;
                elTitulo.textContent = titulo;
                inputMsg.disabled = false;
                btnEnviar.disabled = false;
                carregarContatos().then(() => carregarMensagens(true));
            }
        });
    });

    carregarContatos();
    setInterval(() => {
        carregarContatos();
        if (conversaAtual) carregarMensagens(false);
        apiCall('ping');
    }, 3500);
})();
</script>
@endpush

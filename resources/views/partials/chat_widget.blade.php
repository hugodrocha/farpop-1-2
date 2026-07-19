@if($farpopChatPodeUsar ?? false)
<div id="farpopChatRoot" class="farpop-chat-root" data-api="{{ route('chat.api') }}" data-me="{{ (int) session('usuario_id') }}">
    <button type="button" id="farpopChatFab" class="farpop-chat-fab" title="Chat FARPOP" aria-label="Abrir chat">
        <i class="fas fa-comments"></i>
        <span id="farpopChatBadge" class="farpop-chat-badge {{ ($farpopChatNaoLidas ?? 0) > 0 ? '' : 'd-none' }}">
            {{ ($farpopChatNaoLidas ?? 0) > 99 ? '99+' : ($farpopChatNaoLidas ?? 0) }}
        </span>
    </button>

    <div id="farpopChatPanel" class="farpop-chat-panel d-none" aria-hidden="true">
        <div class="farpop-chat-panel-header">
            <span><i class="fas fa-comments me-1"></i> Chat FARPOP</span>
            <div class="d-flex gap-1">
                <button type="button" id="farpopChatBtnGrupo" class="btn btn-sm btn-light" title="Novo grupo"><i class="fas fa-users"></i></button>
                <button type="button" id="farpopChatBtnFechar" class="btn btn-sm btn-light" title="Fechar"><i class="fas fa-times"></i></button>
            </div>
        </div>

        <div id="farpopChatGrupoForm" class="farpop-chat-grupo-form d-none">
            <input type="text" id="farpopChatGrupoTitulo" class="form-control form-control-sm mb-2" placeholder="Nome do grupo" maxlength="180">
            <div id="farpopChatGrupoMembros" class="farpop-chat-grupo-membros"></div>
            <div class="d-flex gap-2 mt-2">
                <button type="button" id="farpopChatGrupoCriar" class="btn btn-sm btn-primary flex-grow-1">Criar grupo</button>
                <button type="button" id="farpopChatGrupoCancelar" class="btn btn-sm btn-outline-secondary">Cancelar</button>
            </div>
        </div>

        <div class="farpop-chat-panel-body">
            <aside class="farpop-chat-sidebar">
                <div id="farpopChatGrupos" class="farpop-chat-grupos-lista"></div>
                <div class="farpop-chat-secao-titulo">Online</div>
                <div id="farpopChatOnline" class="farpop-chat-contatos"></div>
                <div class="farpop-chat-secao-titulo farpop-chat-secao-offline">Offline</div>
                <div id="farpopChatOffline" class="farpop-chat-contatos"></div>
            </aside>
            <main class="farpop-chat-main">
                <div id="farpopChatConversaTitulo" class="farpop-chat-conversa-titulo">Selecione um contato</div>
                <div id="farpopChatMensagens" class="farpop-chat-mensagens">
                    <div class="farpop-chat-vazio">Escolha alguém para conversar ou crie um grupo.</div>
                </div>
                <form id="farpopChatFormEnviar" class="farpop-chat-form">
                    <input type="text" id="farpopChatInput" class="form-control form-control-sm" placeholder="Digite sua mensagem..." maxlength="4000" autocomplete="off" disabled>
                    <button type="submit" class="btn btn-sm btn-primary" disabled><i class="fas fa-paper-plane"></i></button>
                </form>
            </main>
        </div>
    </div>
</div>

<script>
(function () {
    const root = document.getElementById('farpopChatRoot');
    if (!root) return;

    const apiUrl = root.dataset.api;
    const meuId = parseInt(root.dataset.me, 10);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const fab = document.getElementById('farpopChatFab');
    const panel = document.getElementById('farpopChatPanel');
    const badge = document.getElementById('farpopChatBadge');
    const elOnline = document.getElementById('farpopChatOnline');
    const elOffline = document.getElementById('farpopChatOffline');
    const elGrupos = document.getElementById('farpopChatGrupos');
    const elMensagens = document.getElementById('farpopChatMensagens');
    const elTitulo = document.getElementById('farpopChatConversaTitulo');
    const formEnviar = document.getElementById('farpopChatFormEnviar');
    const inputMsg = document.getElementById('farpopChatInput');
    const btnEnviar = formEnviar.querySelector('button');
    const grupoForm = document.getElementById('farpopChatGrupoForm');
    const grupoTitulo = document.getElementById('farpopChatGrupoTitulo');
    const grupoMembros = document.getElementById('farpopChatGrupoMembros');

    const POS_KEY = 'farpop_chat_fab_pos';
    let aberto = false;
    let arrastando = false;
    let dragMoved = false;
    let dragOffset = { x: 0, y: 0 };
    let conversaAtual = null;
    let sinceId = 0;
    let contatosCache = [];
    let pollTimer = null;

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s ?? '';
        return d.innerHTML;
    }

    function apiCall(acao, params, method) {
        method = method || 'GET';
        const url = new URL(apiUrl, window.location.origin);
        url.searchParams.set('acao', acao);
        const opts = {
            method,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        };
        if (method === 'POST') {
            opts.headers['X-CSRF-TOKEN'] = csrf;
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(params || {});
        } else if (params) {
            Object.keys(params).forEach(k => url.searchParams.set(k, params[k]));
        }
        return fetch(url, opts).then(r => r.json());
    }

    function restaurarPosFab() {
        try {
            const raw = localStorage.getItem(POS_KEY);
            if (!raw) return;
            const pos = JSON.parse(raw);
            if (typeof pos.left === 'number' && typeof pos.top === 'number') {
                fab.style.left = pos.left + 'px';
                fab.style.top = pos.top + 'px';
                fab.style.right = 'auto';
                fab.style.bottom = 'auto';
            }
        } catch (e) {}
    }

    function salvarPosFab() {
        const rect = fab.getBoundingClientRect();
        localStorage.setItem(POS_KEY, JSON.stringify({ left: rect.left, top: rect.top }));
    }

    fab.addEventListener('mousedown', function (e) {
        if (e.button !== 0) return;
        arrastando = true;
        dragMoved = false;
        const rect = fab.getBoundingClientRect();
        dragOffset = { x: e.clientX - rect.left, y: e.clientY - rect.top };
        fab.style.transition = 'none';
        e.preventDefault();
    });

    document.addEventListener('mousemove', function (e) {
        if (!arrastando) return;
        dragMoved = true;
        const w = fab.offsetWidth;
        const h = fab.offsetHeight;
        let left = e.clientX - dragOffset.x;
        let top = e.clientY - dragOffset.y;
        left = Math.max(8, Math.min(window.innerWidth - w - 8, left));
        top = Math.max(8, Math.min(window.innerHeight - h - 8, top));
        fab.style.left = left + 'px';
        fab.style.top = top + 'px';
        fab.style.right = 'auto';
        fab.style.bottom = 'auto';
    });

    document.addEventListener('mouseup', function () {
        if (!arrastando) return;
        arrastando = false;
        fab.style.transition = '';
        if (dragMoved) salvarPosFab();
    });

    fab.addEventListener('click', function () {
        if (dragMoved) return;
        togglePanel();
    });

    document.getElementById('farpopChatBtnFechar').addEventListener('click', () => togglePanel(false));

    function posicionarPanel() {
        const rect = fab.getBoundingClientRect();
        const pw = 380;
        const ph = 480;
        let left = rect.left - pw + fab.offsetWidth;
        let top = rect.top - ph - 12;
        if (left < 8) left = 8;
        if (top < 8) top = rect.bottom + 12;
        if (left + pw > window.innerWidth - 8) left = window.innerWidth - pw - 8;
        panel.style.left = left + 'px';
        panel.style.top = top + 'px';
    }

    function togglePanel(force) {
        aberto = typeof force === 'boolean' ? force : !aberto;
        panel.classList.toggle('d-none', !aberto);
        panel.setAttribute('aria-hidden', aberto ? 'false' : 'true');
        if (aberto) {
            posicionarPanel();
            carregarContatos();
            iniciarPoll();
            apiCall('ping');
        } else {
            pararPoll();
            grupoForm.classList.add('d-none');
        }
    }

    function atualizarBadge(total) {
        if (total > 0) {
            badge.textContent = total > 99 ? '99+' : total;
            badge.classList.remove('d-none');
            fab.classList.add('farpop-chat-fab-pulse');
        } else {
            badge.classList.add('d-none');
            fab.classList.remove('farpop-chat-fab-pulse');
        }
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
        return `<button type="button" class="farpop-chat-contato-item farpop-chat-grupo-item ${ativo ? 'ativo' : ''}" data-tipo="grupo" data-conversa="${g.conversa_id}" data-nome="${esc(g.titulo)}">
            <span class="farpop-chat-grupo-ico"><i class="fas fa-users"></i></span>
            <span class="farpop-chat-contato-info">
                <span class="farpop-chat-contato-nome">${esc(g.titulo)}</span>
                <span class="farpop-chat-contato-setor">${g.membros} participantes</span>
            </span>${badge}
        </button>`;
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

            atualizarBadge(data.total_nao_lidas || 0);
            bindContatoClicks();
        }).catch(() => {});
    }

    function bindContatoClicks() {
        root.querySelectorAll('.farpop-chat-contato-item').forEach(btn => {
            btn.onclick = () => {
                const tipo = btn.dataset.tipo;
                conversaAtual = {
                    tipo,
                    id: tipo === 'privada' ? parseInt(btn.dataset.id, 10) : null,
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

    function renderMensagens(lista, reset) {
        if (reset) elMensagens.innerHTML = '';
        if (!lista.length && reset) {
            elMensagens.innerHTML = '<div class="farpop-chat-vazio">Nenhuma mensagem ainda.</div>';
            return;
        }
        const vazio = elMensagens.querySelector('.farpop-chat-vazio');
        if (vazio) vazio.remove();

        lista.forEach(m => {
            const eu = m.remetente_id === meuId || m.eu;
            const nome = eu ? 'Você' : (m.remetente_nome || 'Usuário');
            const div = document.createElement('div');
            div.className = 'farpop-chat-msg ' + (eu ? 'eu' : 'outro');
            div.innerHTML = `<div class="farpop-chat-msg-bolha">
                ${conversaAtual && conversaAtual.tipo === 'grupo' && !eu ? `<div class="farpop-chat-msg-autor">${esc(nome)}</div>` : ''}
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
        if (conversaAtual.tipo === 'privada' && conversaAtual.id) {
            params.outro_id = conversaAtual.id;
        }
        return apiCall('mensagens', params).then(data => {
            if (!data.ok) return;
            if (data.conversa_id) conversaAtual.conversa_id = data.conversa_id;
            renderMensagens(data.mensagens || [], !!reset);
            if (reset) {
                apiCall('marcar_lido', { conversa_id: conversaAtual.conversa_id });
            }
        }).catch(() => {});
    }

    formEnviar.addEventListener('submit', function (e) {
        e.preventDefault();
        const texto = inputMsg.value.trim();
        if (!texto || !conversaAtual) return;

        const payload = { texto, conversa_id: conversaAtual.conversa_id };
        if (conversaAtual.tipo === 'privada' && conversaAtual.id) {
            payload.outro_id = conversaAtual.id;
        }

        apiCall('enviar', payload, 'POST').then(data => {
            if (data.ok && data.mensagem) {
                inputMsg.value = '';
                renderMensagens([{ ...data.mensagem, remetente_id: meuId, eu: true }], false);
                carregarContatos();
            }
        });
    });

    document.getElementById('farpopChatBtnGrupo').addEventListener('click', () => {
        grupoForm.classList.toggle('d-none');
        if (!grupoForm.classList.contains('d-none')) {
            grupoMembros.innerHTML = contatosCache.map(c =>
                `<label class="farpop-chat-grupo-check"><input type="checkbox" value="${c.id}"> ${esc(c.nome_completo || c.usuario)}</label>`
            ).join('');
        }
    });

    document.getElementById('farpopChatGrupoCancelar').addEventListener('click', () => grupoForm.classList.add('d-none'));
    document.getElementById('farpopChatGrupoCriar').addEventListener('click', () => {
        const titulo = grupoTitulo.value.trim();
        const membros = [...grupoMembros.querySelectorAll('input:checked')].map(i => parseInt(i.value, 10));
        if (!titulo || !membros.length) return;
        apiCall('criar_grupo', { titulo, membros }, 'POST').then(data => {
            if (data.ok) {
                grupoForm.classList.add('d-none');
                grupoTitulo.value = '';
                conversaAtual = { tipo: 'grupo', id: null, conversa_id: data.conversa_id, nome: titulo };
                sinceId = 0;
                elTitulo.textContent = titulo;
                inputMsg.disabled = false;
                btnEnviar.disabled = false;
                carregarContatos().then(() => carregarMensagens(true));
            }
        });
    });

    function iniciarPoll() {
        pararPoll();
        pollTimer = setInterval(() => {
            if (!aberto) return;
            carregarContatos();
            if (conversaAtual) carregarMensagens(false);
            apiCall('ping');
        }, 3500);
    }

    function pararPoll() {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = null;
    }

    setInterval(() => {
        if (aberto) return;
        apiCall('resumo').then(data => {
            if (data.ok) atualizarBadge(data.total_nao_lidas || 0);
        }).catch(() => {});
    }, 8000);

    restaurarPosFab();
})();
</script>
@endif

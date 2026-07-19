<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --verde-principal: #0f766e;
        --verde-escuro: #064e3b;
        --verde-profundo: #022c22;
        --verde-medio: #14b8a6;
        --verde-acento: #2dd4bf;
        --verde-claro: #5eead4;
        --verde-suave: #ecfdf5;
        --verde-mint: #f0fdfa;
        --ambar-acento: #d97706;
        --vermelho-alerta: #dc2626;
        --fundo-pagina: #f4fbf8;
        --texto-principal: #134e4a;
        --texto-suave: #2d6a5f;
        --texto-mudo: #5b8a7e;
        --card-borda: rgba(15, 118, 110, 0.14);
        --sombra-suave: 0 10px 40px rgba(6, 78, 59, 0.12);
        --sombra-card: 0 4px 24px rgba(6, 78, 59, 0.07);
        --raio-card: 18px;
        --gradiente-principal: linear-gradient(135deg, #064e3b 0%, #0f766e 42%, #14b8a6 78%, #2dd4bf 100%);
        --gradiente-sidebar: linear-gradient(195deg, #011812 0%, #022c22 18%, #064e3b 48%, #0f766e 78%, #0d9488 100%);
        --gradiente-card: linear-gradient(160deg, #ffffff 0%, #fafffe 45%, #ecfdf5 82%, #f0fdfa 100%);
        --gradiente-fundo: linear-gradient(165deg, #ffffff 0%, #f4fbf8 40%, #ecfdf5 75%, #e6fffa 100%);
        --gradiente-hero: linear-gradient(135deg, #022c22 0%, #064e3b 32%, #0f766e 68%, #14b8a6 100%);
        --gradiente-botao: linear-gradient(135deg, #065f46 0%, #0f766e 45%, #14b8a6 100%);
        --gradiente-kpi-1: linear-gradient(135deg, #022c22, #064e3b);
        --gradiente-kpi-2: linear-gradient(135deg, #064e3b, #0f766e);
        --gradiente-kpi-3: linear-gradient(135deg, #0f766e, #14b8a6);
        --gradiente-kpi-4: linear-gradient(135deg, #0d9488, #2dd4bf);
        --gradiente-kpi-5: linear-gradient(135deg, #dc2626, #991b1b);
        --brasao-url: url('https://prefeitura.mesquita.rj.gov.br/wp-content/uploads/2025/11/Brasao-Cidade-de-Mesquita-Colorido-1.png');
        --sidebar-bg-cruz: url('{{ asset('images/sidebar-cruz-bg.png') }}');
        --sidebar-largura-colapsada: 78px;
        --sidebar-largura-expandida: 288px;

        --bs-primary: #0f766e;
        --bs-primary-rgb: 15, 118, 110;
        --bs-success: #0f766e;
        --bs-success-rgb: 15, 118, 110;
        --bs-body-color: #134e4a;
        --bs-body-bg: #f4fbf8;
        --bs-border-color: rgba(15, 118, 110, 0.14);
        --bs-link-color: #0f766e;
        --bs-link-hover-color: #064e3b;
    }

    body {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        font-weight: 400;
        color: var(--texto-principal);
        background: var(--gradiente-fundo);
        margin: 0;
        -webkit-font-smoothing: antialiased;
    }

    h1, h2, h3, h4, h5, h6 { font-weight: 600; color: var(--texto-principal); letter-spacing: -0.02em; }

    .layout-sistema { display: flex; min-height: 100vh; }

    .sidebar-sistema {
        width: var(--sidebar-largura-colapsada);
        min-width: var(--sidebar-largura-colapsada);
        background: var(--gradiente-sidebar);
        color: #fff;
        box-shadow: 4px 0 36px rgba(2, 44, 34, 0.22);
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-x: hidden;
        overflow-y: auto;
        transition: width 0.3s ease, min-width 0.3s ease, left 0.3s ease;
        z-index: 1000;
        isolation: isolate;
    }

    .sidebar-sistema:hover, .sidebar-sistema.sidebar-fixada {
        width: var(--sidebar-largura-expandida);
        min-width: var(--sidebar-largura-expandida);
    }

    .sidebar-deco-cruz {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 0;
        overflow: hidden;
        background:
            linear-gradient(
                to top,
                rgba(2, 44, 34, 0.12) 0%,
                rgba(2, 44, 34, 0.28) 55%,
                rgba(1, 24, 18, 0.5) 100%
            ),
            var(--sidebar-bg-cruz) center bottom / cover no-repeat;
    }

    .sidebar-deco-cruz::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(1, 24, 18, 0.42) 0%, transparent 24%, transparent 100%);
    }

    .sidebar-topo, .sidebar-menu { position: relative; z-index: 1; }

    .sidebar-topo { padding: 16px 12px 14px; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .sidebar-logo-area { display: flex; align-items: center; gap: 12px; }
    .sidebar-logo {
        width: 42px; height: 42px; min-width: 42px; object-fit: contain;
        background: rgba(255,255,255,0.97); border-radius: 12px; padding: 4px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
    .sidebar-texto-topo { opacity: 0; width: 0; overflow: hidden; white-space: nowrap; transition: opacity 0.2s, width 0.2s; }
    .sidebar-sistema:hover .sidebar-texto-topo, .sidebar-sistema.sidebar-fixada .sidebar-texto-topo { opacity: 1; width: auto; }
    .sidebar-pin-btn {
        position: absolute; top: 14px; right: 10px; width: 30px; height: 30px;
        border: 1px solid rgba(255,255,255,0.14); border-radius: 8px;
        background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        opacity: 0; pointer-events: none; transition: all 0.2s; z-index: 2;
    }
    .sidebar-sistema:hover .sidebar-pin-btn, .sidebar-sistema.sidebar-fixada .sidebar-pin-btn { opacity: 1; pointer-events: auto; }
    .sidebar-pin-btn:hover { background: rgba(45,212,191,0.25); color: #fff; }
    .sidebar-sistema.sidebar-fixada .sidebar-pin-btn { background: rgba(45,212,191,0.28); color: #fff; transform: rotate(-45deg); }
    .sidebar-titulo { margin: 0; font-size: 19px; font-weight: 700; color: #fff; }
    .sidebar-subtitulo { margin: 2px 0 0; font-size: 12px; color: rgba(167, 243, 208, 0.85); }
    .sidebar-usuario-box {
        margin-top: 14px; padding: 12px 14px;
        background: linear-gradient(135deg, rgba(45,212,191,0.14) 0%, rgba(255,255,255,0.06) 100%);
        border: 1px solid rgba(255,255,255,0.12); border-radius: 14px;
        opacity: 0; max-height: 0; overflow: hidden; transition: all 0.2s;
    }
    .sidebar-sistema:hover .sidebar-usuario-box, .sidebar-sistema.sidebar-fixada .sidebar-usuario-box {
        opacity: 1; max-height: 140px; margin-top: 14px; padding: 12px 14px;
    }
    .sidebar-usuario-nome { margin: 0; font-weight: 500; font-size: 14px; }
    .sidebar-usuario-info { margin: 2px 0 0; font-size: 12px; color: rgba(255,255,255,0.72); text-transform: capitalize; }

    .sidebar-menu { padding: 10px 8px 20px; }
    .sidebar-item { position: relative; margin-bottom: 4px; }
    .sidebar-link, .sidebar-dropdown-btn, .sidebar-sublink {
        display: flex; align-items: center; gap: 10px; width: 100%; min-width: 0;
        border: none; background: transparent; color: rgba(255,255,255,0.84); text-decoration: none;
        padding: 11px 14px; border-radius: 12px; font-size: 14px; font-weight: 500;
        text-align: left; transition: all 0.2s; box-sizing: border-box; cursor: pointer;
    }
    .sidebar-icone { width: 22px; min-width: 22px; text-align: center; font-size: 16px; flex-shrink: 0; }
    .sidebar-label { opacity: 0; width: 0; overflow: hidden; white-space: nowrap; transition: opacity 0.2s, width 0.2s; flex: 1; }
    .sidebar-sistema:hover .sidebar-label, .sidebar-sistema.sidebar-fixada .sidebar-label { opacity: 1; width: auto; }
    .sidebar-link:hover, .sidebar-dropdown-btn:hover, .sidebar-sublink:hover,
    .sidebar-link.active, .sidebar-sublink.active, .sidebar-dropdown-btn.active {
        background: linear-gradient(135deg, rgba(45,212,191,0.22) 0%, rgba(255,255,255,0.08) 100%);
        color: #fff; box-shadow: inset 3px 0 0 var(--verde-acento);
    }
    .sidebar-seta { margin-left: auto; font-size: 12px; opacity: 0; width: 0; overflow: hidden; transition: all 0.2s; }
    .sidebar-sistema:hover .sidebar-seta, .sidebar-sistema.sidebar-fixada .sidebar-seta { opacity: 1; width: auto; }
    .sidebar-seta.open { transform: rotate(180deg); }
    .sidebar-submenu { display: none; margin: 4px 0 8px 12px; padding: 6px; background: rgba(0,0,0,0.14); border-radius: 12px; }
    .sidebar-submenu.show { display: block; }
    .sidebar-sistema:not(:hover):not(.sidebar-fixada) .sidebar-submenu { display: none !important; }
    .sidebar-sublink { font-size: 13px; font-weight: 400; padding: 9px 12px; }
    .sidebar-tooltip {
        position: absolute; left: calc(100% + 8px); top: 50%; transform: translateY(-50%);
        background: var(--verde-escuro); color: #fff; font-size: 12px; padding: 6px 10px; border-radius: 8px;
        white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.15s; z-index: 9999;
    }
    .sidebar-sistema:not(:hover):not(.sidebar-fixada) .sidebar-item:hover .sidebar-tooltip { opacity: 1; }

    .conteudo-sistema { flex: 1; min-width: 0; position: relative; padding-bottom: 52px; }
    .conteudo-sistema::before {
        content: ''; position: fixed; top: 50%; left: 55%; transform: translate(-50%, -50%);
        width: min(420px, 55vw); height: min(420px, 55vw);
        background: var(--brasao-url) center/contain no-repeat;
        opacity: 0.03; pointer-events: none; z-index: 0;
    }
    .main-content { padding: 28px; position: relative; z-index: 1; }

    .btn-primary, .bg-primary {
        background: var(--gradiente-botao) !important; border: none; font-weight: 600; border-radius: 12px;
        padding: 0.5rem 1.1rem; box-shadow: 0 8px 24px rgba(15, 118, 110, 0.28);
        transition: transform 0.15s, box-shadow 0.15s; color: #fff !important;
    }
    .btn-primary:hover, .btn-primary:focus {
        background: linear-gradient(135deg, #064e3b 0%, #0d9488 50%, #2dd4bf 100%) !important;
        transform: translateY(-1px); box-shadow: 0 12px 28px rgba(15, 118, 110, 0.35); color: #fff !important;
    }
    .btn-outline-primary { color: var(--verde-principal); border-color: rgba(20, 184, 166, 0.45); border-radius: 12px; font-weight: 500; }
    .btn-outline-primary:hover { background: var(--verde-suave); border-color: var(--verde-medio); color: var(--verde-escuro); }
    .btn-outline-secondary { color: var(--texto-suave); border-color: rgba(15, 118, 110, 0.22); border-radius: 12px; }
    .btn-outline-secondary:hover { background: var(--verde-suave); border-color: var(--verde-claro); color: var(--verde-escuro); }
    .text-primary { color: var(--verde-principal) !important; }
    .text-muted { color: var(--texto-mudo) !important; }

    .card {
        background: var(--gradiente-card); border: 1px solid var(--card-borda);
        border-radius: var(--raio-card); box-shadow: var(--sombra-card); overflow: hidden;
    }
    .card .card-header.bg-primary { background: var(--gradiente-hero) !important; color: #fff; border: none; }

    .table thead th {
        font-weight: 600; font-size: 0.78rem; color: var(--texto-suave);
        background: linear-gradient(180deg, #ffffff 0%, var(--verde-mint) 100%);
        border-bottom: 1px solid var(--card-borda);
    }
    .table-striped > tbody > tr:nth-of-type(odd) > * {
        --bs-table-bg-type: rgba(236, 253, 245, 0.55);
        background-color: rgba(236, 253, 245, 0.55);
    }
    .table-hover > tbody > tr:hover > * {
        --bs-table-bg-state: rgba(236, 253, 245, 0.9);
        background-color: rgba(236, 253, 245, 0.9);
    }

    .form-control, .form-select {
        border: 1px solid rgba(15, 118, 110, 0.18); border-radius: 12px;
        background: #fff; transition: border-color 0.15s, box-shadow 0.15s; color: var(--texto-principal);
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--verde-medio); box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.14);
    }
    .form-control::placeholder { color: #7aab9e; }
    .form-label { font-weight: 600; font-size: 0.85rem; color: var(--texto-suave); }

    .alert-success {
        background: var(--verde-suave); border-color: rgba(15, 118, 110, 0.22);
        color: var(--verde-escuro); border-radius: 12px;
    }
    .alert-danger { border-radius: 12px; }
    .alert-info {
        background: var(--verde-mint); border-color: rgba(15, 118, 110, 0.18);
        color: var(--verde-escuro); border-radius: 12px;
    }

    .badge.bg-primary, .badge.text-bg-primary { background: var(--verde-principal) !important; }
    .badge.bg-success, .badge.text-bg-success { background: var(--verde-medio) !important; }

    .farpop-page-title {
        font-size: 1.6rem; font-weight: 700;
        background: var(--gradiente-principal); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text; margin-bottom: 0.25rem;
    }
    .farpop-page-subtitle { color: var(--texto-mudo); font-size: 0.92rem; margin-bottom: 0; }

    .farpop-hero-gradient {
        background: var(--gradiente-hero); border-radius: var(--raio-card); color: #fff;
        box-shadow: var(--sombra-suave); position: relative; overflow: hidden;
    }
    .farpop-hero-gradient::before {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(120deg, transparent 40%, rgba(45,212,191,0.12) 60%, transparent 80%);
        pointer-events: none;
    }
    .farpop-hero-gradient::after {
        content: ''; position: absolute; top: -40%; right: -10%; width: 280px; height: 280px;
        background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
        pointer-events: none;
    }

    .farpop-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .farpop-kpi-card {
        border-radius: var(--raio-card); padding: 1.2rem 1.3rem; color: #fff;
        box-shadow: var(--sombra-suave); transition: transform 0.15s; position: relative; overflow: hidden;
    }
    .farpop-kpi-card::before {
        content: ''; position: absolute; inset: 0;
        background: radial-gradient(circle at 100% 0%, rgba(255,255,255,0.2) 0%, transparent 55%);
        pointer-events: none;
    }
    .farpop-kpi-card:hover { transform: translateY(-3px); }
    .farpop-kpi-card.kpi-1 { background: var(--gradiente-kpi-1); }
    .farpop-kpi-card.kpi-2 { background: var(--gradiente-kpi-2); }
    .farpop-kpi-card.kpi-3 { background: var(--gradiente-kpi-3); }
    .farpop-kpi-card.kpi-4 { background: var(--gradiente-kpi-4); }
    .farpop-kpi-card.kpi-5 { background: var(--gradiente-kpi-5); }
    .farpop-kpi-card .kpi-label { font-size: 0.78rem; font-weight: 600; opacity: 0.92; margin-bottom: 6px; }
    .farpop-kpi-card .kpi-valor { font-size: 2rem; font-weight: 800; letter-spacing: -0.03em; line-height: 1; }
    .farpop-kpi-card .kpi-hint { font-size: 0.72rem; opacity: 0.88; margin-top: 8px; }

    .farpop-section-title {
        font-size: 1rem; font-weight: 700; color: var(--verde-principal);
        margin: 1.5rem 0 1rem; padding-left: 12px;
        border-left: 4px solid transparent;
        border-image: linear-gradient(180deg, #14b8a6, #2dd4bf) 1;
    }

    .card-clinica {
        border-radius: var(--raio-card); padding: 1rem 1.1rem; border: 1px solid var(--card-borda);
        background: var(--gradiente-card); box-shadow: var(--sombra-card);
        border-top: 4px solid var(--verde-medio); transition: transform 0.15s;
    }
    .card-clinica:hover { transform: translateY(-2px); }
    .card-clinica.eh-central { border-top-color: var(--ambar-acento); background: linear-gradient(160deg, #fffbeb 0%, #fff 60%); }
    .card-clinica.tem-critico { border-top-color: var(--vermelho-alerta); background: linear-gradient(160deg, #fef2f2 0%, #fff 60%); }

    .farpop-surface-mint { background: var(--verde-mint); }

    /* Barra inferior de avisos */
    .farpop-barra-avisos {
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 1100;
        min-height: 44px; display: flex; align-items: center; overflow: hidden;
        box-shadow: 0 -4px 24px rgba(6, 78, 59, 0.15);
    }
    .farpop-barra-avisos::before {
        content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);
        width: 120px; height: 100%;
        background: linear-gradient(to top, rgba(255,255,255,0.12), transparent);
        clip-path: polygon(35% 0%, 65% 0%, 72% 100%, 28% 100%);
        pointer-events: none;
    }
    .farpop-barra-marquee { display: flex; white-space: nowrap; animation: farpop-marquee 40s linear infinite; position: relative; z-index: 1; }
    @keyframes farpop-marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }

    /* Chat flutuante */
    .farpop-chat-root { position: fixed; z-index: 1050; inset: 0; pointer-events: none; }
    .farpop-chat-fab {
        position: fixed; bottom: 58px; right: 22px; z-index: 1051;
        width: 56px; height: 56px; border-radius: 50%; border: none;
        background: var(--gradiente-botao); color: #fff; font-size: 22px;
        box-shadow: 0 8px 28px rgba(15, 118, 110, 0.38); cursor: grab;
        transition: transform 0.15s; pointer-events: auto; touch-action: none;
    }
    .farpop-chat-fab:active { cursor: grabbing; }
    .farpop-chat-fab:hover { transform: scale(1.06); }
    .farpop-chat-fab-pulse { animation: farpop-chat-pulse 1.4s ease-in-out infinite; }
    @keyframes farpop-chat-pulse {
        0%, 100% { box-shadow: 0 8px 28px rgba(15, 118, 110, 0.38); }
        50% { box-shadow: 0 8px 32px rgba(220, 38, 38, 0.55), 0 0 0 8px rgba(220, 38, 38, 0.12); }
    }
    .farpop-chat-badge {
        position: absolute; top: -2px; right: -2px; min-width: 20px; height: 20px;
        background: linear-gradient(135deg, #dc2626, #d97706); border-radius: 999px;
        font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center;
        border: 2px solid #fff; pointer-events: none;
    }
    .farpop-chat-panel {
        position: fixed; width: 380px; max-width: calc(100vw - 16px); height: 480px; max-height: calc(100vh - 80px);
        background: #fff; border-radius: 16px; box-shadow: 0 16px 48px rgba(6, 78, 59, 0.22);
        display: flex; flex-direction: column; overflow: hidden; z-index: 1052; pointer-events: auto;
        border: 1px solid rgba(15, 118, 110, 0.12);
    }
    .farpop-chat-panel-header {
        display: flex; align-items: center; justify-content: space-between; gap: 8px;
        padding: 10px 12px; background: var(--gradiente-sidebar); color: #fff; font-weight: 600; font-size: 14px;
    }
    .farpop-chat-panel-body { display: flex; flex: 1; min-height: 0; }
    .farpop-chat-sidebar {
        width: 42%; min-width: 130px; border-right: 1px solid #e5e7eb;
        overflow-y: auto; background: #f8fafc; font-size: 12px;
    }
    .farpop-chat-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    .farpop-chat-secao-titulo {
        padding: 6px 10px 4px; font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.04em; color: #059669; background: #ecfdf5;
    }
    .farpop-chat-secao-offline { color: #b91c1c; background: #fef2f2; }
    .farpop-chat-contatos { padding: 2px 0 6px; }
    .farpop-chat-contato-item {
        display: flex; align-items: center; gap: 6px; width: 100%; border: none; background: transparent;
        padding: 6px 8px; text-align: left; cursor: pointer; position: relative;
    }
    .farpop-chat-contato-item:hover, .farpop-chat-contato-item.ativo { background: rgba(15, 118, 110, 0.08); }
    .farpop-chat-contato-item.ativo { border-left: 3px solid var(--farpop-verde-medio, #0f766e); }
    .farpop-chat-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .farpop-chat-dot-online { background: #22c55e; box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.25); }
    .farpop-chat-dot-offline { background: #ef4444; box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2); }
    .farpop-chat-grupo-ico { width: 18px; text-align: center; color: #0f766e; flex-shrink: 0; }
    .farpop-chat-contato-info { flex: 1; min-width: 0; }
    .farpop-chat-contato-nome { display: block; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .farpop-chat-contato-setor { display: block; font-size: 10px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .farpop-chat-contato-badge {
        min-width: 16px; height: 16px; padding: 0 4px; border-radius: 999px;
        background: #dc2626; color: #fff; font-size: 9px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
    }
    .farpop-chat-sem-contatos { padding: 4px 10px; color: #94a3b8; font-size: 11px; }
    .farpop-chat-conversa-titulo {
        padding: 8px 10px; font-weight: 600; font-size: 13px; border-bottom: 1px solid #e5e7eb;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .farpop-chat-mensagens { flex: 1; overflow-y: auto; padding: 10px; background: linear-gradient(180deg, #f0fdfa 0%, #fff 100%); }
    .farpop-chat-vazio { text-align: center; color: #94a3b8; font-size: 12px; padding: 24px 8px; }
    .farpop-chat-msg { margin-bottom: 8px; display: flex; }
    .farpop-chat-msg.eu { justify-content: flex-end; }
    .farpop-chat-msg-bolha {
        max-width: 88%; padding: 6px 10px; border-radius: 12px; font-size: 12px; line-height: 1.4;
    }
    .farpop-chat-msg.eu .farpop-chat-msg-bolha { background: var(--gradiente-botao); color: #fff; border-bottom-right-radius: 4px; }
    .farpop-chat-msg.outro .farpop-chat-msg-bolha { background: #fff; border: 1px solid #e2e8f0; border-bottom-left-radius: 4px; }
    .farpop-chat-msg-autor { font-size: 10px; font-weight: 700; color: #0f766e; margin-bottom: 2px; }
    .farpop-chat-msg-hora { font-size: 9px; opacity: 0.65; margin-top: 3px; }
    .farpop-chat-form {
        display: flex; gap: 6px; padding: 8px; border-top: 1px solid #e5e7eb; background: #fff;
    }
    .farpop-chat-grupo-form {
        padding: 8px 10px; border-bottom: 1px solid #e5e7eb; background: #f8fafc;
    }
    .farpop-chat-grupo-membros { max-height: 100px; overflow-y: auto; display: flex; flex-direction: column; gap: 4px; }
    .farpop-chat-grupo-check { font-size: 12px; margin: 0; cursor: pointer; }
    .farpop-chat-grupos-lista .farpop-chat-secao-titulo { background: #f0f9ff; color: #0369a1; }

    /* Chat página completa */
    .farpop-chat-page { display: flex; height: calc(100vh - 220px); min-height: 420px; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; }
    .farpop-chat-page-sidebar { width: 280px; border-right: 1px solid #e5e7eb; overflow-y: auto; background: #f8fafc; flex-shrink: 0; }
    .farpop-chat-page-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }

    .menu-mobile-topo { display: none; }
    .menu-overlay { display: none; }
    .menu-mobile-botao {
        background: rgba(255,255,255,0.12); color: #fff; border: 1px solid rgba(255,255,255,0.16);
        border-radius: 10px; padding: 8px 12px; font-size: 18px; cursor: pointer;
    }
    .menu-mobile-titulo { font-size: 15px; font-weight: 700; margin: 0; }

    @media (max-width: 991px) {
        .menu-mobile-topo {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 14px; background: var(--gradiente-sidebar); color: #fff;
            position: sticky; top: 0; z-index: 1200;
        }
        .layout-sistema { display: block; }
        .sidebar-sistema {
            position: fixed; left: calc(-1 * var(--sidebar-largura-expandida)); top: 0;
            width: var(--sidebar-largura-expandida); min-width: var(--sidebar-largura-expandida);
            height: 100vh; z-index: 1300;
        }
        .sidebar-sistema.ativo-mobile { left: 0; }
        .sidebar-sistema, .sidebar-sistema:hover, .sidebar-sistema.sidebar-fixada {
            width: var(--sidebar-largura-expandida); min-width: var(--sidebar-largura-expandida);
        }
        .sidebar-texto-topo, .sidebar-label, .sidebar-seta, .sidebar-usuario-box {
            opacity: 1 !important; width: auto !important; max-height: 200px !important;
        }
        .sidebar-pin-btn { opacity: 1 !important; pointer-events: auto !important; }
        .sidebar-tooltip { display: none !important; }
        .menu-overlay.ativo { display: block; position: fixed; inset: 0; background: rgba(2, 44, 34, 0.45); z-index: 1250; }
        .main-content { padding: 16px; }
        .conteudo-sistema { padding-bottom: 52px; }
    }
</style>

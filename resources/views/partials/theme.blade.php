<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --verde-principal: #064e3b;
        --verde-acento: #0f766e;
        --verde-claro: #14b8a6;
        --verde-brilho: #2dd4bf;
        --verde-suave: #ccfbf1;
        --cyan-acento: #0891b2;
        --roxo-acento: #7c3aed;
        --ambar-acento: #f59e0b;
        --vermelho-alerta: #dc2626;
        --verde-escuro: #0f172a;
        --fundo-pagina: #eef7f4;
        --texto-principal: #334155;
        --texto-suave: #64748b;
        --texto-mudo: #94a3b8;
        --card-borda: rgba(148, 163, 184, 0.22);
        --sombra-suave: 0 8px 32px rgba(6, 78, 59, 0.12);
        --sombra-card: 0 2px 16px rgba(15, 23, 42, 0.06);
        --raio-card: 18px;
        --gradiente-principal: linear-gradient(135deg, #022c22 0%, #064e3b 28%, #0f766e 58%, #14b8a6 82%, #0891b2 100%);
        --gradiente-sidebar: linear-gradient(180deg, #0f172a 0%, #134e4a 38%, #1e293b 72%, #312e81 100%);
        --gradiente-card: linear-gradient(160deg, #ffffff 0%, #f8fffe 45%, #ecfdf5 78%, #f0fdfa 100%);
        --gradiente-fundo: linear-gradient(180deg, #f8fafc 0%, #eef7f4 45%, #ecfeff 100%);
        --gradiente-hero: linear-gradient(135deg, #064e3b 0%, #0f766e 35%, #14b8a6 65%, #0891b2 100%);
        --gradiente-kpi-1: linear-gradient(135deg, #064e3b, #0f766e);
        --gradiente-kpi-2: linear-gradient(135deg, #0891b2, #14b8a6);
        --gradiente-kpi-3: linear-gradient(135deg, #7c3aed, #6366f1);
        --gradiente-kpi-4: linear-gradient(135deg, #f59e0b, #d97706);
        --gradiente-kpi-5: linear-gradient(135deg, #dc2626, #991b1b);
        --brasao-url: url('https://prefeitura.mesquita.rj.gov.br/wp-content/uploads/2025/11/Brasao-Cidade-de-Mesquita-Colorido-1.png');
        --sidebar-largura-colapsada: 78px;
        --sidebar-largura-expandida: 288px;
    }

    body {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        font-weight: 400;
        color: var(--texto-principal);
        background: var(--gradiente-fundo);
        margin: 0;
        -webkit-font-smoothing: antialiased;
    }

    h1, h2, h3, h4, h5, h6 { font-weight: 600; color: #1e293b; letter-spacing: -0.02em; }

    .layout-sistema { display: flex; min-height: 100vh; }

    .sidebar-sistema {
        width: var(--sidebar-largura-colapsada);
        min-width: var(--sidebar-largura-colapsada);
        background: var(--gradiente-sidebar);
        color: #fff;
        box-shadow: 4px 0 32px rgba(15, 23, 42, 0.18);
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-x: hidden;
        overflow-y: auto;
        transition: width 0.3s ease, min-width 0.3s ease, left 0.3s ease;
        z-index: 1000;
    }

    .sidebar-sistema:hover, .sidebar-sistema.sidebar-fixada {
        width: var(--sidebar-largura-expandida);
        min-width: var(--sidebar-largura-expandida);
    }

    .sidebar-topo { padding: 16px 12px 14px; border-bottom: 1px solid rgba(255,255,255,0.08); position: relative; }
    .sidebar-logo-area { display: flex; align-items: center; gap: 12px; }
    .sidebar-logo {
        width: 42px; height: 42px; min-width: 42px; object-fit: contain;
        background: rgba(255,255,255,0.97); border-radius: 12px; padding: 4px;
        box-shadow: 0 6px 16px rgba(0,0,0,0.18);
    }
    .sidebar-texto-topo { opacity: 0; width: 0; overflow: hidden; white-space: nowrap; transition: opacity 0.2s, width 0.2s; }
    .sidebar-sistema:hover .sidebar-texto-topo, .sidebar-sistema.sidebar-fixada .sidebar-texto-topo { opacity: 1; width: auto; }
    .sidebar-pin-btn {
        position: absolute; top: 14px; right: 10px; width: 30px; height: 30px;
        border: 1px solid rgba(255,255,255,0.12); border-radius: 8px;
        background: rgba(255,255,255,0.06); color: #94a3b8; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        opacity: 0; pointer-events: none; transition: all 0.2s; z-index: 2;
    }
    .sidebar-sistema:hover .sidebar-pin-btn, .sidebar-sistema.sidebar-fixada .sidebar-pin-btn { opacity: 1; pointer-events: auto; }
    .sidebar-pin-btn:hover { background: rgba(45,212,191,0.22); color: #fff; }
    .sidebar-sistema.sidebar-fixada .sidebar-pin-btn { background: rgba(45,212,191,0.28); color: #fff; transform: rotate(-45deg); }
    .sidebar-titulo { margin: 0; font-size: 19px; font-weight: 700; color: #fff; }
    .sidebar-subtitulo { margin: 2px 0 0; font-size: 12px; color: #94a3b8; }
    .sidebar-usuario-box {
        margin-top: 14px; padding: 12px 14px;
        background: linear-gradient(135deg, rgba(45,212,191,0.15) 0%, rgba(99,102,241,0.08) 100%);
        border: 1px solid rgba(255,255,255,0.1); border-radius: 14px;
        opacity: 0; max-height: 0; overflow: hidden; transition: all 0.2s;
    }
    .sidebar-sistema:hover .sidebar-usuario-box, .sidebar-sistema.sidebar-fixada .sidebar-usuario-box {
        opacity: 1; max-height: 140px; margin-top: 14px; padding: 12px 14px;
    }
    .sidebar-usuario-nome { margin: 0; font-weight: 500; font-size: 14px; }
    .sidebar-usuario-info { margin: 2px 0 0; font-size: 12px; color: #94a3b8; text-transform: capitalize; }

    .sidebar-menu { padding: 10px 8px 20px; }
    .sidebar-item { position: relative; margin-bottom: 4px; }
    .sidebar-link, .sidebar-dropdown-btn, .sidebar-sublink {
        display: flex; align-items: center; gap: 10px; width: 100%; min-width: 0;
        border: none; background: transparent; color: #cbd5e1; text-decoration: none;
        padding: 11px 14px; border-radius: 12px; font-size: 14px; font-weight: 500;
        text-align: left; transition: all 0.2s; box-sizing: border-box; cursor: pointer;
    }
    .sidebar-icone { width: 22px; min-width: 22px; text-align: center; font-size: 16px; flex-shrink: 0; }
    .sidebar-label { opacity: 0; width: 0; overflow: hidden; white-space: nowrap; transition: opacity 0.2s, width 0.2s; flex: 1; }
    .sidebar-sistema:hover .sidebar-label, .sidebar-sistema.sidebar-fixada .sidebar-label { opacity: 1; width: auto; }
    .sidebar-link:hover, .sidebar-dropdown-btn:hover, .sidebar-sublink:hover,
    .sidebar-link.active, .sidebar-sublink.active, .sidebar-dropdown-btn.active {
        background: linear-gradient(135deg, rgba(45,212,191,0.25) 0%, rgba(99,102,241,0.12) 100%);
        color: #fff; box-shadow: inset 0 0 0 1px rgba(255,255,255,0.06);
    }
    .sidebar-seta { margin-left: auto; font-size: 12px; opacity: 0; width: 0; overflow: hidden; transition: all 0.2s; }
    .sidebar-sistema:hover .sidebar-seta, .sidebar-sistema.sidebar-fixada .sidebar-seta { opacity: 1; width: auto; }
    .sidebar-seta.open { transform: rotate(180deg); }
    .sidebar-submenu { display: none; margin: 4px 0 8px 12px; padding: 6px; background: rgba(255,255,255,0.04); border-radius: 12px; }
    .sidebar-submenu.show { display: block; }
    .sidebar-sistema:not(:hover):not(.sidebar-fixada) .sidebar-submenu { display: none !important; }
    .sidebar-sublink { font-size: 13px; font-weight: 400; padding: 9px 12px; }
    .sidebar-tooltip {
        position: absolute; left: calc(100% + 8px); top: 50%; transform: translateY(-50%);
        background: #111827; color: #fff; font-size: 12px; padding: 6px 10px; border-radius: 8px;
        white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.15s; z-index: 9999;
    }
    .sidebar-sistema:not(:hover):not(.sidebar-fixada) .sidebar-item:hover .sidebar-tooltip { opacity: 1; }

    .conteudo-sistema { flex: 1; min-width: 0; position: relative; padding-bottom: 52px; }
    .conteudo-sistema::before {
        content: ''; position: fixed; top: 50%; left: 55%; transform: translate(-50%, -50%);
        width: min(420px, 55vw); height: min(420px, 55vw);
        background: var(--brasao-url) center/contain no-repeat;
        opacity: 0.045; pointer-events: none; z-index: 0;
        filter: saturate(0.9);
    }
    .main-content { padding: 28px; position: relative; z-index: 1; }

    .btn-primary {
        background: var(--gradiente-hero); border: none; font-weight: 600; border-radius: 12px;
        padding: 0.5rem 1.1rem; box-shadow: 0 6px 20px rgba(15,118,110,0.28);
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .btn-primary:hover, .btn-primary:focus {
        background: linear-gradient(135deg, #065f46 0%, #0d9488 50%, #06b6d4 100%);
        transform: translateY(-1px); box-shadow: 0 10px 24px rgba(15,118,110,0.35); color: #fff;
    }
    .btn-outline-primary { color: var(--verde-principal); border-color: rgba(20,184,166,0.4); border-radius: 12px; font-weight: 500; }
    .btn-outline-primary:hover { background: var(--verde-suave); border-color: var(--verde-claro); color: var(--verde-principal); }
    .text-primary { color: var(--verde-principal) !important; }

    .card {
        background: var(--gradiente-card); border: 1px solid var(--card-borda);
        border-radius: var(--raio-card); box-shadow: var(--sombra-card); overflow: hidden;
    }
    .card .card-header.bg-primary { background: var(--gradiente-hero) !important; color: #fff; border: none; }

    .table thead th {
        font-weight: 600; font-size: 0.78rem; color: var(--texto-suave);
        background: linear-gradient(180deg, #f8fafc 0%, #ecfdf5 100%);
        border-bottom: 1px solid var(--card-borda);
    }
    .form-control, .form-select {
        border: 1px solid var(--card-borda); border-radius: 12px;
        background: rgba(255,255,255,0.9); transition: border-color 0.15s, box-shadow 0.15s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--verde-claro); box-shadow: 0 0 0 4px rgba(20,184,166,0.12);
    }
    .form-label { font-weight: 600; font-size: 0.85rem; color: var(--texto-suave); }

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
        background: radial-gradient(circle at 100% 0%, rgba(255,255,255,0.22) 0%, transparent 55%);
        pointer-events: none;
    }
    .farpop-kpi-card:hover { transform: translateY(-3px); }
    .farpop-kpi-card.kpi-1 { background: var(--gradiente-kpi-1); }
    .farpop-kpi-card.kpi-2 { background: var(--gradiente-kpi-2); }
    .farpop-kpi-card.kpi-3 { background: var(--gradiente-kpi-3); }
    .farpop-kpi-card.kpi-4 { background: var(--gradiente-kpi-4); }
    .farpop-kpi-card.kpi-5 { background: var(--gradiente-kpi-5); }
    .farpop-kpi-card .kpi-label { font-size: 0.78rem; font-weight: 600; opacity: 0.9; margin-bottom: 6px; }
    .farpop-kpi-card .kpi-valor { font-size: 2rem; font-weight: 800; letter-spacing: -0.03em; line-height: 1; }
    .farpop-kpi-card .kpi-hint { font-size: 0.72rem; opacity: 0.85; margin-top: 8px; }

    .farpop-section-title {
        font-size: 1rem; font-weight: 700; color: var(--verde-principal);
        margin: 1.5rem 0 1rem; padding-left: 12px;
        border-left: 4px solid transparent;
        border-image: linear-gradient(180deg, #14b8a6, #6366f1) 1;
    }

    .card-clinica {
        border-radius: var(--raio-card); padding: 1rem 1.1rem; border: 1px solid var(--card-borda);
        background: var(--gradiente-card); box-shadow: var(--sombra-card);
        border-top: 4px solid var(--verde-claro); transition: transform 0.15s;
    }
    .card-clinica:hover { transform: translateY(-2px); }
    .card-clinica.eh-central { border-top-color: var(--ambar-acento); background: linear-gradient(160deg, #fffbeb 0%, #fff 60%); }
    .card-clinica.tem-critico { border-top-color: var(--vermelho-alerta); background: linear-gradient(160deg, #fef2f2 0%, #fff 60%); }

    /* Barra inferior de avisos */
    .farpop-barra-avisos {
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 1100;
        min-height: 44px; display: flex; align-items: center; overflow: hidden;
        box-shadow: 0 -4px 20px rgba(15,23,42,0.12);
    }
    .farpop-barra-marquee { display: flex; white-space: nowrap; animation: farpop-marquee 40s linear infinite; }
    @keyframes farpop-marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }

    /* Chat flutuante */
    .farpop-chat-fab {
        position: fixed; bottom: 58px; right: 22px; z-index: 1050;
        width: 56px; height: 56px; border-radius: 50%; border: none;
        background: var(--gradiente-hero); color: #fff; font-size: 22px;
        box-shadow: 0 8px 28px rgba(15,118,110,0.4); cursor: pointer;
        transition: transform 0.15s;
    }
    .farpop-chat-fab:hover { transform: scale(1.06); }
    .farpop-chat-badge {
        position: absolute; top: -2px; right: -2px; min-width: 20px; height: 20px;
        background: linear-gradient(135deg, #dc2626, #f59e0b); border-radius: 999px;
        font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center;
        border: 2px solid #fff;
    }

    .menu-mobile-topo { display: none; }
    .menu-overlay { display: none; }
    .menu-mobile-botao {
        background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.15);
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
        .menu-overlay.ativo { display: block; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1250; }
        .main-content { padding: 16px; }
        .conteudo-sistema { padding-bottom: 52px; }
    }
</style>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FARPOP 1.1')</title>
    <link rel="icon" type="image/png" href="{{ config('farpop.brasao_url') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('partials.theme')
    @stack('styles')
</head>
<body>
@php
    use App\Services\ModuloService;
    use App\Services\PerfilService;
    $perfil = session('perfil', '');
    $podeAdmin = PerfilService::ehAdmin($perfil);
    $podeOperacional = PerfilService::ehOperacional($perfil);
    $ehConsulta = PerfilService::ehConsulta($perfil);
    $podeConfig = ModuloService::podeAcessar($perfil, 'configuracoes');
    $podeUsuarios = ModuloService::podeAcessarSubmodulo($perfil, 'cadastros', 'usuarios');
    $podeCadastros = ModuloService::podeAcessar($perfil, 'cadastros');
    $podePacientes = ModuloService::podeAcessarSubmodulo($perfil, 'cadastros', 'pacientes');
    $podeRelatorios = ModuloService::podeAcessar($perfil, 'relatorios');
    $podeFornecedores = ModuloService::podeAcessarSubmodulo($perfil, 'cadastros', 'fornecedores');
    $podeSolicitacao = ModuloService::podeAcessarSubmodulo($perfil, 'transferencias', 'solicitacao');
    $podeEntreUnidades = ModuloService::podeAcessarSubmodulo($perfil, 'transferencias', 'entre_unidades');
    $podeAlertas = ModuloService::podeAcessar($perfil, 'alertas');
    $podeHorus = ModuloService::podeAcessarSubmodulo($perfil, 'integracoes', 'horus');
    $podeIntegracoes = ModuloService::podeAcessar($perfil, 'integracoes');
    $logado = session()->has('usuario_id');
@endphp

<div class="layout-sistema">
    @if($logado)
        <div class="menu-overlay" id="menuOverlay" onclick="fecharMenuMobile()"></div>
        <aside class="sidebar-sistema" id="sidebarSistema">
            <div class="sidebar-deco-cruz" aria-hidden="true"></div>
            <div class="sidebar-topo">
                <button type="button" class="sidebar-pin-btn" id="btnFixarSidebar" title="Fixar menu"><i class="fas fa-thumbtack"></i></button>
                <div class="sidebar-logo-area">
                    <img src="{{ config('farpop.brasao_url') }}" alt="Brasão Mesquita" class="sidebar-logo">
                    <div class="sidebar-texto-topo">
                        <h1 class="sidebar-titulo">FARPOP 1.1</h1>
                        <p class="sidebar-subtitulo">Farmácia Popular</p>
                    </div>
                </div>
                <div class="sidebar-usuario-box">
                    <p class="sidebar-usuario-nome">{{ session('nome_completo') ?: session('usuario') }}</p>
                    <p class="sidebar-usuario-info">{{ PerfilService::nome($perfil) }}</p>
                    @if(session('clinica_nome'))<p class="sidebar-usuario-info">{{ session('clinica_nome') }}</p>@endif
                </div>
            </div>
            <nav class="sidebar-menu">
                @if($ehConsulta)
                    <div class="sidebar-item">
                        <a href="{{ route('aqui-tem.index') }}" class="sidebar-link {{ request()->routeIs('aqui-tem.*') ? 'active' : '' }}">
                            <i class="fas fa-search sidebar-icone"></i><span class="sidebar-label">Aqui Tem</span>
                        </a>
                        <span class="sidebar-tooltip">Aqui Tem</span>
                    </div>
                @else
                    <div class="sidebar-item">
                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-home sidebar-icone"></i><span class="sidebar-label">Início</span>
                        </a>
                        <span class="sidebar-tooltip">Início</span>
                    </div>

                    @if(in_array($perfil, ['superuser','admin','farmaceutico','consulta'], true))
                    <div class="sidebar-item">
                        <a href="{{ route('aqui-tem.index') }}" class="sidebar-link {{ request()->routeIs('aqui-tem.*') ? 'active' : '' }}">
                            <i class="fas fa-map-marker-alt sidebar-icone"></i><span class="sidebar-label">Aqui Tem</span>
                        </a>
                        <span class="sidebar-tooltip">Aqui Tem Remédio</span>
                    </div>
                    @endif

                    @if($podeOperacional)
                    <div class="sidebar-item">
                        <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('estoque.*','entradas.*','saidas.*','pacientes.*','lotes.*','ajustes.*','inventario.*','movimentacoes.*','reservas.*') ? 'active' : '' }}" onclick="toggleSubmenu('menuEstoque', this)">
                            <i class="fas fa-boxes sidebar-icone"></i><span class="sidebar-label">Estoque e dispensa</span>
                            <span class="sidebar-seta {{ request()->routeIs('estoque.*','entradas.*','saidas.*','pacientes.*','lotes.*','ajustes.*','inventario.*','movimentacoes.*','reservas.*') ? 'open' : '' }}">▾</span>
                        </button>
                        <span class="sidebar-tooltip">Estoque e dispensa</span>
                    </div>
                    <div id="menuEstoque" class="sidebar-submenu {{ request()->routeIs('estoque.*','entradas.*','saidas.*','pacientes.*','lotes.*','ajustes.*','inventario.*','movimentacoes.*','reservas.*') ? 'show' : '' }}">
                        <a href="{{ route('estoque.index') }}" class="sidebar-sublink {{ request()->routeIs('estoque.*') ? 'active' : '' }}"><i class="fas fa-boxes sidebar-icone"></i><span class="sidebar-label">Consultar estoque</span></a>
                        <a href="{{ route('entradas.index') }}" class="sidebar-sublink {{ request()->routeIs('entradas.*') ? 'active' : '' }}"><i class="fas fa-arrow-down sidebar-icone"></i><span class="sidebar-label">Entradas</span></a>
                        <a href="{{ route('saidas.index') }}" class="sidebar-sublink {{ request()->routeIs('saidas.*') ? 'active' : '' }}"><i class="fas fa-pills sidebar-icone"></i><span class="sidebar-label">Dispensa</span></a>
                        <a href="{{ route('reservas.index') }}" class="sidebar-sublink {{ request()->routeIs('reservas.*') ? 'active' : '' }}"><i class="fas fa-bookmark sidebar-icone"></i><span class="sidebar-label">Reservas</span></a>
                        @if($podePacientes)
                        <a href="{{ route('pacientes.index') }}" class="sidebar-sublink {{ request()->routeIs('pacientes.*') ? 'active' : '' }}"><i class="fas fa-user-injured sidebar-icone"></i><span class="sidebar-label">Pacientes</span></a>
                        @endif
                        <a href="{{ route('lotes.index') }}" class="sidebar-sublink {{ request()->routeIs('lotes.*','ajustes.*','inventario.*','movimentacoes.*') ? 'active' : '' }}"><i class="fas fa-layer-group sidebar-icone"></i><span class="sidebar-label">Lotes (PEPS)</span></a>
                    </div>

                    @if($podeAlertas)
                    <div class="sidebar-item">
                        <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('alertas.*') ? 'active' : '' }}" onclick="toggleSubmenu('menuAlertas', this)">
                            <i class="fas fa-bell sidebar-icone"></i><span class="sidebar-label">Alertas</span>
                            <span class="sidebar-seta {{ request()->routeIs('alertas.*') ? 'open' : '' }}">▾</span>
                        </button>
                        <span class="sidebar-tooltip">Alertas</span>
                    </div>
                    <div id="menuAlertas" class="sidebar-submenu {{ request()->routeIs('alertas.*') ? 'show' : '' }}">
                        <a href="{{ route('alertas.index') }}" class="sidebar-sublink {{ request()->routeIs('alertas.index') ? 'active' : '' }}"><i class="fas fa-bell sidebar-icone"></i><span class="sidebar-label">Central</span></a>
                        <a href="{{ route('alertas.estoque') }}" class="sidebar-sublink {{ request()->routeIs('alertas.estoque') ? 'active' : '' }}"><i class="fas fa-exclamation-triangle sidebar-icone"></i><span class="sidebar-label">Estoque baixo</span></a>
                        <a href="{{ route('alertas.vencimento') }}" class="sidebar-sublink {{ request()->routeIs('alertas.vencimento') ? 'active' : '' }}"><i class="fas fa-calendar-times sidebar-icone"></i><span class="sidebar-label">Validade</span></a>
                        <a href="{{ route('alertas.pendencias') }}" class="sidebar-sublink {{ request()->routeIs('alertas.pendencias') ? 'active' : '' }}"><i class="fas fa-clock sidebar-icone"></i><span class="sidebar-label">Pendências</span></a>
                        <a href="{{ route('alertas.ruptura') }}" class="sidebar-sublink {{ request()->routeIs('alertas.ruptura') ? 'active' : '' }}"><i class="fas fa-chart-line sidebar-icone"></i><span class="sidebar-label">Ruptura / reposição</span></a>
                        <a href="{{ route('pedidos-reposicao.index') }}" class="sidebar-sublink {{ request()->routeIs('pedidos-reposicao.*') ? 'active' : '' }}"><i class="fas fa-shopping-cart sidebar-icone"></i><span class="sidebar-label">Pedido de reposição</span></a>
                    </div>
                    @endif

                    <div class="sidebar-item">
                        <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('transferencias.*','solicitacoes.*') ? 'active' : '' }}" onclick="toggleSubmenu('menuTransferencias', this)">
                            <i class="fas fa-truck sidebar-icone"></i><span class="sidebar-label">Transferências</span>
                            <span class="sidebar-seta {{ request()->routeIs('transferencias.*','solicitacoes.*') ? 'open' : '' }}">▾</span>
                        </button>
                        <span class="sidebar-tooltip">Transferências</span>
                    </div>
                    <div id="menuTransferencias" class="sidebar-submenu {{ request()->routeIs('transferencias.*','solicitacoes.*') ? 'show' : '' }}">
                        @if($podeSolicitacao)
                        <a href="{{ route('solicitacoes.index') }}" class="sidebar-sublink {{ request()->routeIs('solicitacoes.*') ? 'active' : '' }}"><i class="fas fa-clipboard-list sidebar-icone"></i><span class="sidebar-label">Solicitações</span></a>
                        @endif
                        @if($podeEntreUnidades)
                        <a href="{{ route('transferencias.entre-unidades.create') }}" class="sidebar-sublink {{ request()->routeIs('transferencias.entre-unidades*') ? 'active' : '' }}"><i class="fas fa-exchange-alt sidebar-icone"></i><span class="sidebar-label">Entre unidades</span></a>
                        @endif
                        <a href="{{ route('transferencias.create') }}" class="sidebar-sublink {{ request()->routeIs('transferencias.create') ? 'active' : '' }}"><i class="fas fa-dolly sidebar-icone"></i><span class="sidebar-label">Enviar (matriz)</span></a>
                        <a href="{{ route('transferencias.receber') }}" class="sidebar-sublink {{ request()->routeIs('transferencias.receber*') ? 'active' : '' }}"><i class="fas fa-barcode sidebar-icone"></i><span class="sidebar-label">Receber</span></a>
                        <a href="{{ route('transferencias.transito') }}" class="sidebar-sublink {{ request()->routeIs('transferencias.transito') ? 'active' : '' }}"><i class="fas fa-shipping-fast sidebar-icone"></i><span class="sidebar-label">Em transporte</span></a>
                        <a href="{{ route('transferencias.index') }}" class="sidebar-sublink {{ request()->routeIs('transferencias.index','transferencias.edit') ? 'active' : '' }}"><i class="fas fa-list sidebar-icone"></i><span class="sidebar-label">Histórico / guias</span></a>
                    </div>
                    @endif

                    @if($podeRelatorios)
                    <div class="sidebar-item">
                        <a href="{{ route('relatorios.index') }}" class="sidebar-link {{ request()->routeIs('relatorios.*') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar sidebar-icone"></i><span class="sidebar-label">Relatórios</span>
                        </a>
                        <span class="sidebar-tooltip">Relatórios</span>
                    </div>
                    @endif

                    @if($podeCadastros)
                    <div class="sidebar-item">
                        <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('clinicas.*','medicamentos.*','fornecedores.*','fabricantes.*') ? 'active' : '' }}" onclick="toggleSubmenu('menuCadastros', this)">
                            <i class="fas fa-database sidebar-icone"></i><span class="sidebar-label">Cadastros</span>
                            <span class="sidebar-seta {{ request()->routeIs('clinicas.*','medicamentos.*','fornecedores.*','fabricantes.*') ? 'open' : '' }}">▾</span>
                        </button>
                        <span class="sidebar-tooltip">Cadastros</span>
                    </div>
                    <div id="menuCadastros" class="sidebar-submenu {{ request()->routeIs('clinicas.*','medicamentos.*','fornecedores.*','fabricantes.*') ? 'show' : '' }}">
                        <a href="{{ route('clinicas.index') }}" class="sidebar-sublink {{ request()->routeIs('clinicas.*') ? 'active' : '' }}"><i class="fas fa-hospital sidebar-icone"></i><span class="sidebar-label">Unidades de saúde</span></a>
                        <a href="{{ route('medicamentos.index') }}" class="sidebar-sublink {{ request()->routeIs('medicamentos.index','medicamentos.edit') ? 'active' : '' }}"><i class="fas fa-capsules sidebar-icone"></i><span class="sidebar-label">Medicamentos</span></a>
                        @if($podeFornecedores)
                        <a href="{{ route('fornecedores.index') }}" class="sidebar-sublink {{ request()->routeIs('fornecedores.*') ? 'active' : '' }}"><i class="fas fa-truck-loading sidebar-icone"></i><span class="sidebar-label">Fornecedores</span></a>
                        <a href="{{ route('fabricantes.index') }}" class="sidebar-sublink {{ request()->routeIs('fabricantes.*') ? 'active' : '' }}"><i class="fas fa-industry sidebar-icone"></i><span class="sidebar-label">Fabricantes</span></a>
                        @endif
                        <a href="{{ route('medicamentos.importar') }}" class="sidebar-sublink {{ request()->routeIs('medicamentos.importar*') ? 'active' : '' }}"><i class="fas fa-file-import sidebar-icone"></i><span class="sidebar-label">Importar REMUME</span></a>
                    </div>
                    @endif

                    @if($podeUsuarios)
                    <div class="sidebar-item">
                        <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" onclick="toggleSubmenu('menuUsuarios', this)">
                            <i class="fas fa-users sidebar-icone"></i><span class="sidebar-label">Usuários</span>
                            <span class="sidebar-seta {{ request()->routeIs('usuarios.*') ? 'open' : '' }}">▾</span>
                        </button>
                        <span class="sidebar-tooltip">Usuários</span>
                    </div>
                    <div id="menuUsuarios" class="sidebar-submenu {{ request()->routeIs('usuarios.*') ? 'show' : '' }}">
                        <a href="{{ route('usuarios.create') }}" class="sidebar-sublink {{ request()->routeIs('usuarios.create') ? 'active' : '' }}"><i class="fas fa-user-plus sidebar-icone"></i><span class="sidebar-label">Cadastrar</span></a>
                        <a href="{{ route('usuarios.index') }}" class="sidebar-sublink {{ request()->routeIs('usuarios.index','usuarios.edit') ? 'active' : '' }}"><i class="fas fa-users-cog sidebar-icone"></i><span class="sidebar-label">Gerenciar</span></a>
                    </div>
                    @endif

                    @if($podeConfig)
                    <div class="sidebar-item">
                        <button type="button" class="sidebar-dropdown-btn {{ request()->routeIs('configuracoes.*','auditoria.*','avisos.*','integracoes.*') ? 'active' : '' }}" onclick="toggleSubmenu('menuConfig', this)">
                            <i class="fas fa-cog sidebar-icone"></i><span class="sidebar-label">Administração</span>
                            <span class="sidebar-seta {{ request()->routeIs('configuracoes.*','auditoria.*','avisos.*','integracoes.*') ? 'open' : '' }}">▾</span>
                        </button>
                        <span class="sidebar-tooltip">Administração</span>
                    </div>
                    <div id="menuConfig" class="sidebar-submenu {{ request()->routeIs('configuracoes.*','auditoria.*','avisos.*','integracoes.*') ? 'show' : '' }}">
                        <a href="{{ route('configuracoes.index') }}" class="sidebar-sublink {{ request()->routeIs('configuracoes.*') ? 'active' : '' }}"><i class="fas fa-sliders-h sidebar-icone"></i><span class="sidebar-label">Configurações</span></a>
                        @if($podeIntegracoes)
                        <a href="{{ route('integracoes.index') }}" class="sidebar-sublink {{ request()->routeIs('integracoes.index') ? 'active' : '' }}"><i class="fas fa-plug sidebar-icone"></i><span class="sidebar-label">Integrações</span></a>
                        @endif
                        @if($podeHorus)
                        <a href="{{ route('integracoes.horus') }}" class="sidebar-sublink {{ request()->routeIs('integracoes.horus*') ? 'active' : '' }}"><i class="fas fa-file-export sidebar-icone"></i><span class="sidebar-label">Hórus / SUS</span></a>
                        @endif
                        <a href="{{ route('auditoria.index') }}" class="sidebar-sublink {{ request()->routeIs('auditoria.index') ? 'active' : '' }}"><i class="fas fa-shield-alt sidebar-icone"></i><span class="sidebar-label">Auditoria</span></a>
                        <a href="{{ route('auditoria.chat') }}" class="sidebar-sublink {{ request()->routeIs('auditoria.chat') ? 'active' : '' }}"><i class="fas fa-comment-dots sidebar-icone"></i><span class="sidebar-label">Auditoria chat</span></a>
                        <a href="{{ route('avisos.index') }}" class="sidebar-sublink {{ request()->routeIs('avisos.*') ? 'active' : '' }}"><i class="fas fa-bullhorn sidebar-icone"></i><span class="sidebar-label">Barra de avisos</span></a>
                    </div>
                    @endif
                @endif

                <div class="sidebar-item">
                    <a href="{{ route('trocar-senha.index') }}" class="sidebar-link {{ request()->routeIs('trocar-senha.*') ? 'active' : '' }}">
                        <i class="fas fa-key sidebar-icone"></i><span class="sidebar-label">Trocar senha</span>
                    </a>
                    <span class="sidebar-tooltip">Trocar senha</span>
                </div>
                <div class="sidebar-item">
                    <a href="{{ route('logout') }}" class="sidebar-link"><i class="fas fa-sign-out-alt sidebar-icone"></i><span class="sidebar-label">Sair</span></a>
                    <span class="sidebar-tooltip">Sair</span>
                </div>
            </nav>
        </aside>
    @endif

    <div class="conteudo-sistema">
        @if($logado)
        <div class="menu-mobile-topo">
            <button type="button" class="menu-mobile-botao" id="btnAbrirMenuMobile"><i class="fas fa-bars"></i></button>
            <p class="menu-mobile-titulo">FARPOP 1.1</p>
            <span style="width:42px;"></span>
        </div>
        @endif
        <div class="main-content">@yield('content')</div>
    </div>
</div>

@include('partials.aviso_barra')
@include('partials.chat_widget')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSubmenu(id, btn) {
    document.getElementById(id)?.classList.toggle('show');
    btn.querySelector('.sidebar-seta')?.classList.toggle('open');
}
(function () {
    const sidebar = document.getElementById('sidebarSistema');
    const pinBtn = document.getElementById('btnFixarSidebar');
    const overlay = document.getElementById('menuOverlay');
    const mobileBtn = document.getElementById('btnAbrirMenuMobile');
    const key = 'farpop_sidebar_fixada';
    if (!sidebar) return;
    const isMobile = () => window.matchMedia('(max-width: 991px)').matches;
    const fixar = (v) => {
        sidebar.classList.toggle('sidebar-fixada', v && !isMobile());
        if (!isMobile()) localStorage.setItem(key, v ? '1' : '0');
    };
    window.fecharMenuMobile = () => {
        sidebar.classList.remove('ativo-mobile');
        overlay?.classList.remove('ativo');
        document.body.style.removeProperty('overflow');
    };
    if (localStorage.getItem(key) === '1') fixar(true);
    pinBtn?.addEventListener('click', (e) => { e.stopPropagation(); if (!isMobile()) fixar(!sidebar.classList.contains('sidebar-fixada')); });
    mobileBtn?.addEventListener('click', () => { sidebar.classList.add('ativo-mobile'); overlay?.classList.add('ativo'); document.body.style.overflow = 'hidden'; });
    sidebar.querySelectorAll('.sidebar-link[href], .sidebar-sublink[href]').forEach(l => l.addEventListener('click', () => { if (isMobile()) window.fecharMenuMobile(); }));
    window.addEventListener('resize', () => { if (isMobile()) { sidebar.classList.remove('sidebar-fixada'); window.fecharMenuMobile(); } else if (localStorage.getItem(key) === '1') fixar(true); });
})();
</script>
@stack('scripts')
</body>
</html>

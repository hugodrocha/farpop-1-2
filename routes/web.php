<?php

use App\Http\Controllers\AquitemController;
use App\Http\Controllers\AuditoriaChatController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvisoBarraController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClinicaController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmTransitoController;
use App\Http\Controllers\EntradaController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\ImportarMedicamentoController;
use App\Http\Controllers\MedicamentoBuscaController;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\NotaTransferenciaController;
use App\Http\Controllers\AjusteController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\MovimentacaoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\PecController;
use App\Http\Controllers\ReceberTransferenciaController;
use App\Http\Controllers\SaidaController;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\TrocarSenhaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\SolicitacaoTransferenciaController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\FabricanteController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\HorusController;
use App\Http\Controllers\IntegracaoController;
use App\Http\Controllers\TransferenciaEntreUnidadesController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::middleware('farpop.auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    /* ── 10. Dashboard ─────────────────────────────────────────────── */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('farpop.perfil:superuser,admin,gestor,farmaceutico,tecnico')
        ->name('dashboard');
    Route::get('/dashboard/clinica/{id}', [DashboardController::class, 'clinica'])
        ->middleware('farpop.perfil:superuser,admin,gestor,farmaceutico,tecnico')
        ->name('dashboard.clinica');

    Route::get('/aqui-tem', [AquitemController::class, 'index'])
        ->middleware('farpop.perfil:consulta,superuser,admin,farmaceutico,tecnico')
        ->name('aqui-tem.index');

    Route::get('/trocar-senha', [TrocarSenhaController::class, 'index'])->name('trocar-senha.index');
    Route::post('/trocar-senha', [TrocarSenhaController::class, 'update'])->name('trocar-senha.update');

    /* ── 2–5. Estoque, dispensa e transferências ───────────────────── */
    Route::middleware('farpop.perfil:superuser,admin,gestor,farmaceutico,tecnico')->group(function () {
        Route::get('/estoque', [EstoqueController::class, 'index'])->name('estoque.index');
        Route::resource('entradas', EntradaController::class)->except(['show']);
        Route::resource('saidas', SaidaController::class)->except(['show']);
        Route::get('/saidas/{id}/comprovante', [SaidaController::class, 'comprovante'])->name('saidas.comprovante');

        Route::get('/transferencias/em-transito', [EmTransitoController::class, 'index'])->name('transferencias.transito');
        Route::get('/transferencias/receber', [ReceberTransferenciaController::class, 'index'])->name('transferencias.receber');
        Route::get('/transferencias/receber/buscar', [ReceberTransferenciaController::class, 'buscarNota'])->name('transferencias.receber.buscar');
        Route::post('/transferencias/receber', [ReceberTransferenciaController::class, 'confirmar'])->name('transferencias.receber.confirmar');
        Route::get('/transferencias/{id}/nota', [NotaTransferenciaController::class, 'show'])->name('transferencias.nota');
        Route::get('/transferencias/buscar-med', [TransferenciaController::class, 'buscarMedicamento'])->name('transferencias.buscar-med');
        Route::post('/transferencias/lote', [TransferenciaController::class, 'finalizarLote'])->name('transferencias.lote');
        Route::post('/transferencias/{id}/cancelar', [TransferenciaController::class, 'cancelar'])->name('transferencias.cancelar');
        Route::get('/transferencias/entre-unidades', [TransferenciaEntreUnidadesController::class, 'create'])->name('transferencias.entre-unidades.create');
        Route::post('/transferencias/entre-unidades', [TransferenciaEntreUnidadesController::class, 'store'])->name('transferencias.entre-unidades.store');
        Route::resource('transferencias', TransferenciaController::class)->except(['show']);

        Route::get('/solicitacoes', [SolicitacaoTransferenciaController::class, 'index'])->name('solicitacoes.index');
        Route::get('/solicitacoes/nova', [SolicitacaoTransferenciaController::class, 'create'])->name('solicitacoes.create');
        Route::post('/solicitacoes', [SolicitacaoTransferenciaController::class, 'store'])->name('solicitacoes.store');
        Route::get('/solicitacoes/{id}', [SolicitacaoTransferenciaController::class, 'show'])->name('solicitacoes.show');
        Route::post('/solicitacoes/{id}/aprovar', [SolicitacaoTransferenciaController::class, 'aprovar'])->name('solicitacoes.aprovar');
        Route::post('/solicitacoes/{id}/recusar', [SolicitacaoTransferenciaController::class, 'recusar'])->name('solicitacoes.recusar');
        Route::post('/solicitacoes/{id}/separar', [SolicitacaoTransferenciaController::class, 'separar'])->name('solicitacoes.separar');
        Route::post('/solicitacoes/{id}/enviar', [SolicitacaoTransferenciaController::class, 'enviar'])->name('solicitacoes.enviar');

        Route::get('/reservas', [ReservaController::class, 'index'])->name('reservas.index');
        Route::get('/reservas/nova', [ReservaController::class, 'create'])->name('reservas.create');
        Route::post('/reservas', [ReservaController::class, 'store'])->name('reservas.store');
        Route::post('/reservas/{id}/cancelar', [ReservaController::class, 'cancelar'])->name('reservas.cancelar');

        Route::get('/pec/cidadao', [PecController::class, 'buscarCidadao'])->name('pec.cidadao');
        Route::get('/medicamentos/buscar', [MedicamentoBuscaController::class, 'buscar'])->name('medicamentos.buscar');
        Route::get('/medicamentos/autocomplete', [MedicamentoBuscaController::class, 'autocomplete'])->name('medicamentos.autocomplete');

        /* ── 1. Cadastros — pacientes ──────────────────────────────── */
        Route::get('/pacientes', [PacienteController::class, 'index'])->name('pacientes.index');
        Route::get('/pacientes/{id}/editar', [PacienteController::class, 'edit'])->name('pacientes.edit');
        Route::put('/pacientes/{id}', [PacienteController::class, 'update'])->name('pacientes.update');
        Route::get('/pacientes/{id}', [PacienteController::class, 'show'])->name('pacientes.show');

        Route::get('/alertas', [AlertaController::class, 'index'])->name('alertas.index');
        Route::get('/alertas/estoque', [AlertaController::class, 'estoque'])->name('alertas.estoque');
        Route::get('/alertas/pendencias', [AlertaController::class, 'pendencias'])->name('alertas.pendencias');
        Route::get('/alertas/ruptura', [AlertaController::class, 'ruptura'])->name('alertas.ruptura');
        Route::get('/alertas/vencimento', [AlertaController::class, 'vencimento'])->name('alertas.vencimento');

        Route::get('/lotes', [LoteController::class, 'index'])->name('lotes.index');
        Route::get('/lotes/{id}/etiqueta', [LoteController::class, 'etiqueta'])->name('lotes.etiqueta');
        Route::get('/ajustes/novo', [AjusteController::class, 'create'])->name('ajustes.create');
        Route::post('/ajustes', [AjusteController::class, 'store'])->name('ajustes.store');
        Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
        Route::post('/inventario', [InventarioController::class, 'store'])->name('inventario.store');
        Route::post('/inventario/importar', [InventarioController::class, 'importar'])->name('inventario.importar');
        Route::get('/movimentacoes', [MovimentacaoController::class, 'index'])->name('movimentacoes.index');
    });

    /* ── 1. Cadastros — unidades e medicamentos ────────────────────── */
    Route::middleware('farpop.perfil:superuser,admin,gestor')->group(function () {
        Route::resource('clinicas', ClinicaController::class)->except(['show']);
        Route::get('/medicamentos/importar', [ImportarMedicamentoController::class, 'index'])->name('medicamentos.importar');
        Route::post('/medicamentos/importar', [ImportarMedicamentoController::class, 'store'])->name('medicamentos.importar.store');
        Route::resource('medicamentos', MedicamentoController::class)->except(['show']);
        Route::resource('fornecedores', FornecedorController::class)->except(['show']);
        Route::resource('fabricantes', FabricanteController::class)->except(['show']);
    });

    /* ── 1. Cadastros — usuários ───────────────────────────────────── */
    Route::middleware('farpop.perfil:superuser,admin,gestor,farmaceutico')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
    });

    /* ── 9. Relatórios (hub) ───────────────────────────────────────── */
    Route::middleware('farpop.perfil:superuser,admin,gestor,farmaceutico')->group(function () {
        Route::get('/relatorios', [RelatorioController::class, 'index'])->name('relatorios.index');
        Route::get('/relatorios/exportar/{tipo}', [RelatorioController::class, 'exportar'])->name('relatorios.exportar');
        Route::get('/relatorios/imprimir/{tipo}', [RelatorioController::class, 'imprimir'])->name('relatorios.imprimir');
    });

    /* ── 11. Auditoria, alertas e configurações ──────────────────── */
    Route::middleware('farpop.perfil:superuser,admin')->group(function () {
        Route::get('/configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes.index');
        Route::post('/configuracoes', [ConfiguracaoController::class, 'update'])->name('configuracoes.update');
        Route::post('/configuracoes/pec-teste', [ConfiguracaoController::class, 'testarPec'])->name('configuracoes.pec-teste');
        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
        Route::post('/auditoria/critica', [AuditoriaController::class, 'acaoCritica'])->name('auditoria.critica');
        Route::get('/auditoria/chat', [AuditoriaChatController::class, 'index'])->name('auditoria.chat');
        Route::get('/avisos-barra', [AvisoBarraController::class, 'index'])->name('avisos.index');
        Route::post('/avisos-barra', [AvisoBarraController::class, 'store'])->name('avisos.store');
        Route::post('/avisos-barra/{id}/encerrar', [AvisoBarraController::class, 'encerrar'])->name('avisos.encerrar');

        Route::get('/integracoes', [IntegracaoController::class, 'index'])->name('integracoes.index');
        Route::get('/integracoes/horus', [HorusController::class, 'index'])->name('integracoes.horus');
        Route::get('/integracoes/horus/exportar', [HorusController::class, 'exportar'])->name('integracoes.horus.exportar');
        Route::post('/configuracoes/backup', [ConfiguracaoController::class, 'backup'])->name('configuracoes.backup');
        Route::post('/configuracoes/alerta-email-teste', [ConfiguracaoController::class, 'testarAlertaEmail'])->name('configuracoes.alerta-email-teste');
    });

    /* ── Chat interno ──────────────────────────────────────────────── */
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::match(['get', 'post'], '/chat/api', [ChatController::class, 'api'])->name('chat.api');
    Route::get('/chat/mensagens', [ChatController::class, 'mensagens'])->name('chat.mensagens');
    Route::get('/chat/usuarios', [ChatController::class, 'usuarios'])->name('chat.usuarios');
    Route::post('/chat/enviar', [ChatController::class, 'enviar'])->name('chat.enviar');
});

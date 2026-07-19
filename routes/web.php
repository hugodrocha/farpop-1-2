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
use App\Http\Controllers\PecController;
use App\Http\Controllers\ReceberTransferenciaController;
use App\Http\Controllers\SaidaController;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\TrocarSenhaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::middleware('farpop.auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

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
        Route::resource('transferencias', TransferenciaController::class)->except(['show']);

        Route::get('/pec/cidadao', [PecController::class, 'buscarCidadao'])->name('pec.cidadao');
        Route::get('/medicamentos/buscar', [MedicamentoBuscaController::class, 'buscar'])->name('medicamentos.buscar');
        Route::get('/medicamentos/autocomplete', [MedicamentoBuscaController::class, 'autocomplete'])->name('medicamentos.autocomplete');
    });

    Route::middleware('farpop.perfil:superuser,admin,gestor')->group(function () {
        Route::resource('clinicas', ClinicaController::class)->except(['show']);
        Route::get('/medicamentos/importar', [ImportarMedicamentoController::class, 'index'])->name('medicamentos.importar');
        Route::post('/medicamentos/importar', [ImportarMedicamentoController::class, 'store'])->name('medicamentos.importar.store');
        Route::resource('medicamentos', MedicamentoController::class)->except(['show']);
    });

    Route::middleware('farpop.perfil:superuser,admin,gestor,farmaceutico')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show']);
    });

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
    });

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/mensagens', [ChatController::class, 'mensagens'])->name('chat.mensagens');
    Route::get('/chat/usuarios', [ChatController::class, 'usuarios'])->name('chat.usuarios');
    Route::post('/chat/enviar', [ChatController::class, 'enviar'])->name('chat.enviar');
});

<?php

use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntradaController;
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\SaidaController;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('clinicas', ClinicaController::class)->except(['show']);
Route::resource('medicamentos', MedicamentoController::class)->except(['show']);
Route::resource('usuarios', UsuarioController::class)->except(['show']);
Route::resource('estoque', EstoqueController::class)->except(['show']);
Route::resource('entradas', EntradaController::class)->except(['show']);
Route::resource('saidas', SaidaController::class)->except(['show']);
Route::resource('transferencias', TransferenciaController::class)->except(['show']);
Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');

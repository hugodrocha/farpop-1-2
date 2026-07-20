<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('farpop:backup-banco')->dailyAt('02:00');
        $schedule->call(function (): void {
            \App\Services\ReservaService::expirarVencidas();
        })->dailyAt('01:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'farpop.auth' => \App\Http\Middleware\FarpopAuth::class,
            'farpop.perfil' => \App\Http\Middleware\PerfilAutorizado::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

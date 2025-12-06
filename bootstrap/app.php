<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Remover el middleware de sesión que está causando el error de array
        $middleware->removeFromGroup('web', \Illuminate\Session\Middleware\StartSession::class);
        
        // Agregar middlewares personalizados
        $middleware->web(append: [
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\LoadSystemSettings::class,
        ]);

        // Registrar middleware personalizado
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'guardia' => \App\Http\Middleware\EnsureUserIsGuardia::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // ✅ TEMPORAL: forzar que cualquier error salga en pantalla y stderr
        $exceptions->render(function (Throwable $e, $request) {

            // Esto manda el error a los Runtime Logs de Render
            error_log("=== LARAVEL EXCEPTION (Render Free) ===");
            error_log($e->__toString());

            // Esto lo muestra en el navegador
            return response(
                "<pre style='white-space:pre-wrap;font-size:14px'>"
                . htmlspecialchars($e->__toString())
                . "</pre>",
                500
            );
        });

    })->create();

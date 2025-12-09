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
        // NO remover el middleware de sesión - es necesario para la aplicación
        
        // ✅ AGREGAR ESTE MIDDLEWARE PRIMERO para corregir configuración de sesión
        $middleware->web(prepend: [
            \App\Http\Middleware\FixSessionConfig::class,
        ]);
        
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

        // Dejar que Laravel maneje las excepciones de validación normalmente
        $exceptions->render(function (Throwable $e, $request) {
            // Si es una excepción de validación, dejar que Laravel la maneje
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null; // Permitir que el manejador predeterminado la procese
            }

            // Para otras excepciones, mostrar el error en desarrollo
            error_log("=== LARAVEL EXCEPTION (Render Free) ===");
            error_log($e->__toString());

            return response(
                "<pre style='white-space:pre-wrap;font-size:14px'>"
                . htmlspecialchars($e->__toString())
                . "</pre>",
                500
            );
        });

    })->create();
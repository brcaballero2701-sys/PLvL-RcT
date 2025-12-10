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

        // Manejador mejorado para excepciones AJAX y API
        $exceptions->render(function (Throwable $e, $request) {
            // Para solicitudes AJAX/JSON, siempre devolver JSON
            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                // Log del error para debugging
                error_log("=== AJAX EXCEPTION ===");
                error_log($e->__toString());

                // Determinar el código HTTP
                $status = 500;
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $status = 422;
                } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    $status = $e->getStatusCode();
                } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $status = 404;
                }

                return response()->json([
                    'success' => false,
                    'message' => '❌ ' . ($e->getMessage() ?: 'Error al procesar la solicitud'),
                    'error' => config('app.debug') ? $e->getMessage() : null,
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null
                ], $status);
            }

            // Para otras solicitudes, mostrar error HTML en desarrollo
            if (config('app.debug')) {
                error_log("=== LARAVEL EXCEPTION ===");
                error_log($e->__toString());

                return response(
                    "<pre style='white-space:pre-wrap;font-size:14px'>"
                    . htmlspecialchars($e->__toString())
                    . "</pre>",
                    500
                );
            }

            // En producción, devolver error genérico
            return response(
                "Se produjo un error al procesar su solicitud. Por favor, intente más tarde.",
                500
            );
        });

    })->create();
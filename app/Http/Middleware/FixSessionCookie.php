<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Session\Session;

class FixSessionCookie
{
    /**
     * Handle an incoming request - implementación manual de sesión
     */
    public function handle($request, Closure $next)
    {
        // Crear una sesión en memoria para evitar el error de StartSession
        $session = app('session')->driver('array');
        
        // Verificar si existe una sesión en la cookie
        $sessionId = $request->cookies->get('laravel_session');
        if ($sessionId) {
            try {
                // Intentar cargar la sesión existente
                $session->setId($sessionId);
            } catch (\Exception $e) {
                // Si falla, crear una nueva
                $session->start();
            }
        } else {
            // Crear nueva sesión
            $session->start();
        }
        
        // Asignar la sesión al request
        $request->setLaravelSession($session);
        
        // Procesar la siguiente parte del middleware
        $response = $next($request);
        
        // Guardar la sesión en la cookie si fue modificada
        if ($session->isStarted()) {
            $response->headers->setCookie(
                cookie(
                    'laravel_session',
                    $session->getId(),
                    config('session.lifetime', 120),
                    config('session.cookie.path', '/'),
                    config('session.cookie.domain', null),
                    config('session.cookie.secure', false),
                    config('session.cookie.http_only', true),
                    false,
                    config('session.cookie.same_site', 'lax')
                )
            );
        }
        
        return $response;
    }
}

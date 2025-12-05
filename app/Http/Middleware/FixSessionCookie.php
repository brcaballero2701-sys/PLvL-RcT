<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Symfony\Component\HttpFoundation\Response;

class FixSessionCookie extends StartSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Asegurar que config('session.cookie.name') sea siempre un string
        $cookieName = config('session.cookie.name', 'laravel_session');
        if (!is_string($cookieName)) {
            config(['session.cookie.name' => 'laravel_session']);
        }
        
        // Llamar al middleware parent con manejo de errores
        try {
            return parent::handle($request, $next);
        } catch (\TypeError $e) {
            // Si aún hay un error de tipo, crear una sesión dummy
            if (strpos($e->getMessage(), 'InputBag') !== false) {
                $request->setLaravelSession(
                    app('session')->driver('array')
                );
                return $next($request);
            }
            throw $e;
        }
    }
}

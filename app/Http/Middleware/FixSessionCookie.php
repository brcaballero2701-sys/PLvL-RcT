<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FixSessionCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Asegurar que la configuración de sesión esté correcta antes de procesarla
        $sessionConfig = config('session.cookie');
        
        // Si config.session.cookie es un array, verificar que tenga las claves correctas
        if (is_array($sessionConfig)) {
            // Asegurar que todas las claves existan
            $defaults = [
                'name' => 'laravel_session',
                'path' => '/',
                'domain' => null,
                'secure' => false,
                'http_only' => true,
                'same_site' => 'lax',
            ];
            
            foreach ($defaults as $key => $value) {
                if (!isset($sessionConfig[$key])) {
                    config(['session.cookie.' . $key => $value]);
                }
            }
        }
        
        return $next($request);
    }
}

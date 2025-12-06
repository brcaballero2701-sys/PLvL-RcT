<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FixSessionConfig
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Forzar que SESSION_PATH y SESSION_DOMAIN sean strings
        // Bug conocido de Laravel cuando hay configuración cacheada incorrecta
        
        if (is_array(config('session.path'))) {
            config(['session.path' => '/']);
        }
        
        if (is_array(config('session.domain'))) {
            config(['session.domain' => null]);
        }
        
        // También asegurar que el cookie path sea string
        if (is_array(config('session.cookie'))) {
            config(['session.cookie' => 'laravel_session']);
        }

        return $next($request);
    }
}
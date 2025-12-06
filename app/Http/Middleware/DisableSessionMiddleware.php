<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisableSessionMiddleware
{
    /**
     * Deshabilitar completamente el middleware de sesiones para evitar el error
     * de array en el nombre de la cookie
     */
    public function handle(Request $request, Closure $next)
    {
        // Bypass del middleware de sesiones - no usar sesiones en este request
        // Permitir que la aplicación continúe sin sesiones
        return $next($request);
    }
}

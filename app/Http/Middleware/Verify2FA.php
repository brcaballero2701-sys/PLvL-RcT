<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Verify2FA
{
    /**
     * Handle an incoming request.
     * Verifica que si el usuario tiene 2FA habilitado, debe completar la verificación
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Rutas que no requieren 2FA
        $publicRoutes = [
            'login',
            'register',
            'password.request',
            'password.reset',
            '2fa.verify',
            'logout',
        ];

        // Si la ruta es pública, dejar pasar
        if (in_array($request->route()?->getName(), $publicRoutes)) {
            return $next($request);
        }

        // Si no está autenticado, dejar pasar (lo maneja auth middleware)
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $twoFactor = $user->twoFactorAuth;

        // Si 2FA está habilitado y confirmado, pero no ha sido verificado en esta sesión
        if ($twoFactor?->isActive()) {
            if (!session('2fa_verified')) {
                return redirect()->route('2fa.challenge')
                    ->with('warning', 'Por favor completa la verificación de dos factores');
            }
        }

        return $next($request);
    }
}

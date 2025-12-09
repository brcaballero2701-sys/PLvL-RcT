<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;

class CheckInactivity
{
    /**
     * Tiempo de inactividad en minutos (default: 30 minutos)
     */
    protected $inactivityTimeout = 30;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $lastActivity = session('last_activity');
            $now = now();

            // Si no hay último acceso, lo registramos
            if (!$lastActivity) {
                session(['last_activity' => $now->timestamp]);
            } else {
                // Calcular minutos de inactividad
                $lastActivityTime = \Carbon\Carbon::createFromTimestamp($lastActivity);
                $inactiveMinutes = $lastActivityTime->diffInMinutes($now);

                // Si excede el timeout, desloguear
                if ($inactiveMinutes > $this->inactivityTimeout) {
                    // Registrar en auditoría el bloqueo por inactividad
                    AuditLog::logAction(
                        'session_locked',
                        'User',
                        $user->id,
                        null,
                        ['reason' => 'inactivity', 'minutes' => $inactiveMinutes],
                        "Sesión bloqueada por inactividad ($inactiveMinutes minutos)"
                    );

                    // Desloguear
                    auth()->logout();
                    session()->flush();

                    return redirect()->route('login')
                        ->with('warning', 'Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.');
                }
            }

            // Actualizar último acceso en cada petición
            session(['last_activity' => $now->timestamp]);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\Middleware\StartSession as BaseStartSession;

class FixSessionCookie extends BaseStartSession
{
    /**
     * Handle an incoming request - override para capturar el error
     */
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (\TypeError $e) {
            // Si hay error de InputBag, significa que config('session.cookie')
            // está siendo pasado como array. Crear sesión dummy y continuar
            if (strpos($e->getMessage(), 'InputBag::get()') !== false) {
                // Crear una sesión en memoria
                $session = app('session')->driver('array');
                $request->setLaravelSession($session);
                
                return $next($request);
            }
            throw $e;
        }
    }
}

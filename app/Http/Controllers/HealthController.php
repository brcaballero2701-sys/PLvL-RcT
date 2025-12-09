<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * RF027: Health check endpoint para monitoreo de disponibilidad
     * Verifica estado de BD, cache y aplicación
     */
    public function check(): JsonResponse
    {
        $status = 'ok';
        $checks = [
            'app' => true,
            'database' => false,
            'cache' => false,
        ];

        // Verificar base de datos
        try {
            DB::connection()->getPdo();
            $checks['database'] = true;
        } catch (\Exception $e) {
            $status = 'degraded';
            $checks['database'] = false;
        }

        // Verificar cache
        try {
            cache()->put('health_check_' . time(), 'ok', 60);
            $checks['cache'] = cache()->has('health_check_' . (time() - 1));
        } catch (\Exception $e) {
            $checks['cache'] = false;
        }

        // Si BD no está disponible, estado crítico
        if (!$checks['database']) {
            $status = 'critical';
        }

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'uptime' => intval(microtime(true) - \LARAVEL_START),
            'checks' => $checks,
            'version' => config('app.version', '1.0.0')
        ], $status === 'ok' ? 200 : 503);
    }

    /**
     * RF027: Endpoint detallado para SLA monitoring
     */
    public function metrics(): JsonResponse
    {
        try {
            $asistenciasHoy = DB::table('asistencias')
                ->whereDate('fecha_hora_registro', today())
                ->count();

            $instructoresActivos = DB::table('instructors')
                ->where('activo', true)
                ->count();

            $usuariosConSesionActiva = DB::table('sessions')
                ->where('last_activity', '>=', now()->subHours(1)->timestamp)
                ->count();

            return response()->json([
                'status' => 'ok',
                'timestamp' => now()->toIso8601String(),
                'metrics' => [
                    'asistencias_hoy' => $asistenciasHoy,
                    'instructores_activos' => $instructoresActivos,
                    'usuarios_conectados' => $usuariosConSesionActiva,
                    'total_registros_bd' => DB::table('asistencias')->count(),
                    'respuesta_ms' => intval((microtime(true) - \LARAVEL_START) * 1000)
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener métricas',
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }
}

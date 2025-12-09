<?php

namespace App\Services;

use App\Models\Instructor;
use App\Models\Asistencia;
use Carbon\Carbon;

class PatternDetectionService
{
    /**
     * RF034: Detectar instructores con rachas de retrasos (3+ en 5 días)
     */
    public function detectLateArrivalRachas(int $days = 30, int $threshold = 3, int $windowDays = 5): array
    {
        $instructoresConRachas = [];

        $instructores = Instructor::activos()->get();

        foreach ($instructores as $instructor) {
            // Obtener entradas en los últimos X días
            $entradas = Asistencia::where('instructor_id', $instructor->id)
                ->where('tipo_movimiento', 'entrada')
                ->where('fecha_hora_registro', '>=', now()->subDays($days))
                ->orderBy('fecha_hora_registro', 'desc')
                ->get();

            if ($entradas->isEmpty()) {
                continue;
            }

            // Agrupar por ventanas de 5 días y contar retrasos
            $ventanas = [];
            foreach ($entradas as $entrada) {
                $fecha = $entrada->fecha_hora_registro->format('Y-m-d');
                $ventana = $entrada->fecha_hora_registro->copy()->startOfDay();

                // Agrupar por ventana de 5 días
                $key = $ventana->format('Y-m-d');
                
                if (!isset($ventanas[$key])) {
                    $ventanas[$key] = [
                        'fecha_inicio' => $ventana->copy()->subDays($windowDays - 1),
                        'fecha_fin' => $ventana,
                        'retrasos' => 0,
                        'total' => 0,
                        'registros' => [],
                    ];
                }

                $ventanas[$key]['total']++;
                if ($entrada->es_tardanza) {
                    $ventanas[$key]['retrasos']++;
                }
                $ventanas[$key]['registros'][] = $entrada;
            }

            // Detectar rachas
            foreach ($ventanas as $ventana) {
                if ($ventana['retrasos'] >= $threshold) {
                    $instructoresConRachas[] = [
                        'instructor_id' => $instructor->id,
                        'instructor_nombre' => $instructor->nombres . ' ' . $instructor->apellidos,
                        'instructor_email' => $instructor->email,
                        'retrasos' => $ventana['retrasos'],
                        'total_registros' => $ventana['total'],
                        'porcentaje' => round(($ventana['retrasos'] / $ventana['total']) * 100, 2),
                        'fecha_inicio' => $ventana['fecha_inicio']->format('Y-m-d'),
                        'fecha_fin' => $ventana['fecha_fin']->format('Y-m-d'),
                        'tipo_alerta' => 'retrasos',
                        'criticidad' => $ventana['retrasos'] >= 5 ? 'crítica' : 'alta',
                    ];
                }
            }
        }

        return $instructoresConRachas;
    }

    /**
     * RF034: Detectar instructores con rachas de ausencias (3+ en 5 días)
     */
    public function detectAbsenceRachas(int $days = 30, int $threshold = 3, int $windowDays = 5): array
    {
        $instructoresConRachas = [];

        $instructores = Instructor::activos()->get();

        foreach ($instructores as $instructor) {
            // Obtener registros en los últimos X días
            $registros = Asistencia::where('instructor_id', $instructor->id)
                ->where('fecha_hora_registro', '>=', now()->subDays($days))
                ->selectRaw('DATE(fecha_hora_registro) as fecha')
                ->distinct()
                ->get();

            if ($registros->isEmpty()) {
                continue;
            }

            // Agrupar fechas en ventanas de 5 días y contar ausencias
            $ventanas = [];
            $diasLaborales = $this->getDiasLaboralesPasados($days);

            foreach ($diasLaborales as $dia) {
                $tieneRegistro = $registros->contains('fecha', $dia->format('Y-m-d'));

                if (!$tieneRegistro) {
                    // Es una ausencia
                    $ventana = $dia->format('Y-m-d');
                    
                    if (!isset($ventanas[$ventana])) {
                        $ventanas[$ventana] = [
                            'fecha_inicio' => $dia->copy()->subDays($windowDays - 1),
                            'fecha_fin' => $dia,
                            'ausencias' => 0,
                            'dias_laborales' => 0,
                        ];
                    }

                    $ventanas[$ventana]['ausencias']++;
                    $ventanas[$ventana]['dias_laborales']++;
                }
            }

            // Detectar rachas
            foreach ($ventanas as $ventana) {
                if ($ventana['ausencias'] >= $threshold) {
                    $instructoresConRachas[] = [
                        'instructor_id' => $instructor->id,
                        'instructor_nombre' => $instructor->nombres . ' ' . $instructor->apellidos,
                        'instructor_email' => $instructor->email,
                        'ausencias' => $ventana['ausencias'],
                        'total_dias' => $ventana['dias_laborales'],
                        'porcentaje' => round(($ventana['ausencias'] / $ventana['dias_laborales']) * 100, 2),
                        'fecha_inicio' => $ventana['fecha_inicio']->format('Y-m-d'),
                        'fecha_fin' => $ventana['fecha_fin']->format('Y-m-d'),
                        'tipo_alerta' => 'ausencias',
                        'criticidad' => $ventana['ausencias'] >= 5 ? 'crítica' : 'alta',
                    ];
                }
            }
        }

        return $instructoresConRachas;
    }

    /**
     * Obtener días laborales (lunes a viernes) en los últimos X días
     */
    private function getDiasLaboralesPasados(int $dias): array
    {
        $diasLaborales = [];
        $fecha = now()->subDays($dias);
        $hoy = now();

        while ($fecha <= $hoy) {
            // Día laboral es lunes (1) a viernes (5)
            if ($fecha->dayOfWeek >= 1 && $fecha->dayOfWeek <= 5) {
                $diasLaborales[] = $fecha->copy();
            }
            $fecha->addDay();
        }

        return $diasLaborales;
    }
}

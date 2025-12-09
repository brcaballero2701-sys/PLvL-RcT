<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Asistencia;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ReportesVigilantesController extends Controller
{
    /**
     * RF005: Mostrar reportes de turnos de vigilantes
     */
    public function index(Request $request): Response
    {
        $vigilantes = User::where('role', 'guardia')
            ->orderBy('name')
            ->get();

        // Filtro por vigilante si viene en request
        $vigilanteSeleccionado = $request->get('vigilante_id');
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        $reporteTurnos = [];

        if ($vigilanteSeleccionado) {
            $vigilante = User::findOrFail($vigilanteSeleccionado);

            // Obtener todos los registros de asistencia registrados por este vigilante
            $registros = Asistencia::where('guardia_id', $vigilante->id)
                ->whereBetween('fecha_hora_registro', [
                    Carbon::parse($fechaInicio)->startOfDay(),
                    Carbon::parse($fechaFin)->endOfDay()
                ])
                ->orderBy('fecha_hora_registro', 'desc')
                ->get();

            // Agrupar por fecha para calcular turnos
            $porFecha = $registros->groupBy(function($asistencia) {
                return $asistencia->fecha_hora_registro->format('Y-m-d');
            });

            foreach ($porFecha as $fecha => $asistenciasDelDia) {
                $entradas = $asistenciasDelDia->where('tipo_movimiento', 'entrada');
                $salidas = $asistenciasDelDia->where('tipo_movimiento', 'salida');

                $horaInicio = $entradas->min('fecha_hora_registro');
                $horaFin = $salidas->max('fecha_hora_registro');

                $duracion = 0;
                if ($horaInicio && $horaFin) {
                    $duracion = $horaInicio->diffInMinutes($horaFin);
                }

                $reporteTurnos[] = [
                    'fecha' => Carbon::parse($fecha)->format('d/m/Y'),
                    'dia_semana' => Carbon::parse($fecha)->locale('es')->dayName,
                    'hora_inicio' => $horaInicio?->format('H:i'),
                    'hora_fin' => $horaFin?->format('H:i'),
                    'duracion_minutos' => $duracion,
                    'duracion_horas' => round($duracion / 60, 2),
                    'total_registros' => $asistenciasDelDia->count(),
                    'total_entradas' => $entradas->count(),
                    'total_salidas' => $salidas->count(),
                    'estado' => $horaInicio && $horaFin ? 'Completo' : ($horaInicio ? 'Sin salida' : 'Sin entrada'),
                ];
            }

            // Estadísticas del período
            $estadisticas = [
                'dias_trabajados' => count($reporteTurnos),
                'total_horas' => round(collect($reporteTurnos)->sum('duracion_minutos') / 60, 2),
                'promedio_horas_dia' => count($reporteTurnos) > 0 
                    ? round((collect($reporteTurnos)->sum('duracion_minutos') / count($reporteTurnos)) / 60, 2)
                    : 0,
                'total_registros' => $registros->count(),
                'total_entradas' => $registros->where('tipo_movimiento', 'entrada')->count(),
                'total_salidas' => $registros->where('tipo_movimiento', 'salida')->count(),
                'turnos_incompletos' => collect($reporteTurnos)->where('estado', '!=', 'Completo')->count(),
            ];
        } else {
            // Mostrar resumen de todos los vigilantes
            $estadisticas = [
                'dias_trabajados' => 0,
                'total_horas' => 0,
                'promedio_horas_dia' => 0,
                'total_registros' => 0,
                'total_entradas' => 0,
                'total_salidas' => 0,
                'turnos_incompletos' => 0,
            ];
        }

        return Inertia::render('Admin/Reportes/VigilantesReportes', [
            'vigilantes' => $vigilantes,
            'reporteTurnos' => $reporteTurnos,
            'estadisticas' => $estadisticas,
            'filtros' => [
                'vigilante_id' => $vigilanteSeleccionado,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
            'vigilanteSeleccionado' => $vigilanteSeleccionado ? $vigilantes->find($vigilanteSeleccionado) : null,
        ]);
    }

    /**
     * RF005: Exportar reporte de turnos en PDF/Excel (opcional)
     */
    public function exportar(Request $request)
    {
        $vigilanteId = $request->get('vigilante_id');
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(30)->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        if (!$vigilanteId) {
            return response()->json([
                'success' => false,
                'message' => 'Debe seleccionar un vigilante'
            ], 400);
        }

        $vigilante = User::findOrFail($vigilanteId);

        // Obtener registros
        $registros = Asistencia::where('guardia_id', $vigilante->id)
            ->whereBetween('fecha_hora_registro', [
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay()
            ])
            ->orderBy('fecha_hora_registro', 'asc')
            ->get();

        // Generar CSV
        $csv = "Reporte de Turnos - {$vigilante->name}\n";
        $csv .= "Período: {$fechaInicio} a {$fechaFin}\n";
        $csv .= "Fecha de Generación: " . now()->format('Y-m-d H:i:s') . "\n\n";
        $csv .= "Fecha,Hora,Tipo,Instructor,Observaciones\n";

        foreach ($registros as $registro) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s"' . "\n",
                $registro->fecha_hora_registro->format('Y-m-d'),
                $registro->fecha_hora_registro->format('H:i:s'),
                strtoupper($registro->tipo_movimiento),
                $registro->instructor->nombres . ' ' . $registro->instructor->apellidos,
                $registro->observaciones ?? ''
            );
        }

        return response()->streamDownload(
            fn() => print($csv),
            "reporte-vigilante-{$vigilante->id}-{$fechaInicio}.csv",
            ['Content-Type' => 'text/csv; charset=utf-8']
        );
    }

    /**
     * RF005: API para obtener datos de turnos de un vigilante específico
     */
    public function getTurnosAPI(Request $request)
    {
        $vigilanteId = $request->get('vigilante_id');
        $fechaInicio = $request->get('fecha_inicio', now()->subDays(7)->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        $registros = Asistencia::where('guardia_id', $vigilanteId)
            ->whereBetween('fecha_hora_registro', [
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay()
            ])
            ->with('instructor')
            ->orderBy('fecha_hora_registro', 'desc')
            ->get()
            ->map(function($asistencia) {
                return [
                    'id' => $asistencia->id,
                    'fecha' => $asistencia->fecha_hora_registro->format('d/m/Y'),
                    'hora' => $asistencia->fecha_hora_registro->format('H:i'),
                    'tipo' => $asistencia->tipo_movimiento,
                    'instructor' => $asistencia->instructor?->nombres . ' ' . $asistencia->instructor?->apellidos,
                ];
            });

        return response()->json([
            'success' => true,
            'registros' => $registros
        ]);
    }
}

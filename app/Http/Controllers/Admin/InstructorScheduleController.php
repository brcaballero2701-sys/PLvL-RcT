<?php

namespace App\Http\Controllers\Admin;

use App\Models\Instructor;
use App\Models\InstructorSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class InstructorScheduleController extends Controller
{
    /**
     * RF008: Mostrar horarios de un instructor específico
     */
    public function show(Instructor $instructor): Response
    {
        $horarios = $instructor->schedules()
            ->orderByRaw("FIELD(dia_semana, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo')")
            ->get();

        $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

        return Inertia::render('Admin/InstructorSchedules/Show', [
            'instructor' => $instructor,
            'horarios' => $horarios,
            'diasSemana' => $diasSemana,
        ]);
    }

    /**
     * RF008: Guardar o actualizar horarios de un instructor
     */
    public function store(Request $request, Instructor $instructor): JsonResponse
    {
        $validated = $request->validate([
            'horarios' => 'required|array',
            'horarios.*.dia_semana' => 'required|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'horarios.*.hora_entrada' => 'required|date_format:H:i',
            'horarios.*.hora_salida' => 'required|date_format:H:i',
            'horarios.*.activo' => 'boolean',
            'horarios.*.observaciones' => 'nullable|string|max:255',
        ]);

        // Eliminar horarios anteriores
        $instructor->schedules()->delete();

        // Crear nuevos horarios
        foreach ($validated['horarios'] as $horario) {
            InstructorSchedule::create([
                'instructor_id' => $instructor->id,
                'dia_semana' => $horario['dia_semana'],
                'hora_entrada' => $horario['hora_entrada'],
                'hora_salida' => $horario['hora_salida'],
                'activo' => $horario['activo'] ?? true,
                'observaciones' => $horario['observaciones'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Horarios actualizados exitosamente',
        ]);
    }

    /**
     * RF008: API para obtener horarios de un instructor
     */
    public function getSchedules(Instructor $instructor): JsonResponse
    {
        $horarios = $instructor->schedules()
            ->select('dia_semana', 'hora_entrada', 'hora_salida', 'activo', 'observaciones')
            ->orderByRaw("FIELD(dia_semana, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo')")
            ->get();

        return response()->json([
            'success' => true,
            'horarios' => $horarios,
        ]);
    }

    /**
     * RF008: Obtener horario del día actual para un instructor
     */
    public function getTodaySchedule(Instructor $instructor): JsonResponse
    {
        $diaSemana = InstructorSchedule::getNombreDia(now()->dayOfWeek);

        $horarioHoy = $instructor->schedules()
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->first();

        if (!$horarioHoy) {
            return response()->json([
                'success' => false,
                'message' => 'Sin horario para hoy',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'horario' => [
                'dia' => $diaSemana,
                'hora_entrada' => $horarioHoy->hora_entrada,
                'hora_salida' => $horarioHoy->hora_salida,
            ],
        ]);
    }

    /**
     * RF008: Verificar si hora registrada es retraso según horario
     */
    public function verificarRetraso(Request $request, Instructor $instructor): JsonResponse
    {
        $validated = $request->validate([
            'hora_registro' => 'required|date_format:H:i',
            'tipo_movimiento' => 'required|in:entrada,salida',
        ]);

        $diaSemana = InstructorSchedule::getNombreDia(now()->dayOfWeek);
        $horario = $instructor->schedules()
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->first();

        if (!$horario) {
            return response()->json([
                'success' => false,
                'message' => 'Sin horario registrado para hoy',
            ], 404);
        }

        $esRetraso = false;
        if ($validated['tipo_movimiento'] === 'entrada') {
            $esRetraso = $validated['hora_registro'] > $horario->hora_entrada;
        }

        return response()->json([
            'success' => true,
            'es_retraso' => $esRetraso,
            'hora_esperada' => $validated['tipo_movimiento'] === 'entrada' 
                ? $horario->hora_entrada 
                : $horario->hora_salida,
        ]);
    }
}

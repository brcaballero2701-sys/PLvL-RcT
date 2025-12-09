<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Models\Asistencia;
use App\Models\User;

class DiagnosticoController extends Controller
{
    public function check()
    {
        $data = [
            'instructores_total' => Instructor::count(),
            'asistencias_total' => Asistencia::count(),
            'usuarios_total' => User::count(),
            'usuarios_admin' => User::where('role', 'admin')->count(),
            'instructores_con_asistencias' => Instructor::has('asistencias')->count(),
            'asistencias_hoy' => Asistencia::whereDate('fecha_hora_registro', today())->count(),
            'primer_instructor' => Instructor::first() ? Instructor::first()->toArray() : null,
            'primer_asistencia' => Asistencia::with('instructor')->first() ? Asistencia::with('instructor')->first()->toArray() : null,
            'ultima_asistencia' => Asistencia::latest('fecha_hora_registro')->first() ? Asistencia::latest('fecha_hora_registro')->first()->toArray() : null,
        ];

        return response()->json($data);
    }
}

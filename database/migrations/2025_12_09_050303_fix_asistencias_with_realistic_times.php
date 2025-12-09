<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener todos los registros de asistencia y asignarles horas realistas
        $asistencias = DB::table('asistencias')->get();
        
        foreach ($asistencias as $asistencia) {
            // Obtener la fecha del registro
            $fecha = Carbon::parse($asistencia->fecha_hora_registro);
            
            if ($asistencia->tipo_movimiento === 'entrada') {
                // Asignar horas de entrada realistas entre 06:00 y 09:00
                $horaEntrada = rand(6, 9); // 6 a 9 AM
                $minutoEntrada = rand(0, 59); // 0 a 59 minutos
                $fecha = $fecha->setTime($horaEntrada, $minutoEntrada, 0);
            } else {
                // Asignar horas de salida realistas entre 16:00 y 18:00
                $horaSalida = rand(16, 18); // 4 a 6 PM
                $minutoSalida = rand(0, 59); // 0 a 59 minutos
                $fecha = $fecha->setTime($horaSalida, $minutoSalida, 0);
            }
            
            // Actualizar el registro con la nueva hora
            DB::table('asistencias')
                ->where('id', $asistencia->id)
                ->update(['fecha_hora_registro' => $fecha]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertir cambios de datos
    }
};

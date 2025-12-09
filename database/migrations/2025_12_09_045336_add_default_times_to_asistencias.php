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
        // Actualizar registros de entrada sin hora: asignar 08:00 (entrada normal)
        DB::table('asistencias')
            ->where('tipo_movimiento', 'entrada')
            ->whereNull('fecha_hora_registro')
            ->orWhere(function($query) {
                $query->where('tipo_movimiento', 'entrada')
                      ->where('fecha_hora_registro', '');
            })
            ->update([
                'fecha_hora_registro' => Carbon::now()->setTime(8, 0, 0)
            ]);

        // Actualizar registros de salida sin hora: asignar 17:00 (salida normal)
        DB::table('asistencias')
            ->where('tipo_movimiento', 'salida')
            ->whereNull('fecha_hora_registro')
            ->orWhere(function($query) {
                $query->where('tipo_movimiento', 'salida')
                      ->where('fecha_hora_registro', '');
            })
            ->update([
                'fecha_hora_registro' => Carbon::now()->setTime(17, 0, 0)
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Esta migración solo agrega datos, no modifica estructura
        // Por lo que no hay nada que deshacer
    }
};

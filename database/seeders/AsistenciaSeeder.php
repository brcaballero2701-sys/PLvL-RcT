<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Instructor;
use App\Models\Asistencia;
use App\Models\User;
use Carbon\Carbon;

class AsistenciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los instructores existentes
        $instructores = Instructor::all();
        
        if ($instructores->isEmpty()) {
            $this->command->error('No hay instructores en la base de datos. Crear instructores primero.');
            return;
        }

        // Obtener un guardia para asociar las asistencias
        $guardia = User::where('role', 'guardia')->first();
        
        if (!$guardia) {
            // Crear un guardia si no existe
            $guardia = User::create([
                'name' => 'Carlos Ortiz',
                'email' => 'carlos.ortiz@sena.edu.co',
                'password' => bcrypt('password123'),
                'role' => 'guardia',
                'codigo_guardia' => 'GUARD001'
            ]);
        }

        $this->command->info("🔄 Limpiando asistencias anteriores...");
        Asistencia::truncate();

        // Contador de asistencias creadas
        $contadorAsistencias = 0;
        $asistenciasTarget = 100;
        
        // Distribuir asistencias de forma equitativa entre instructores
        $asistenciasPorInstructor = intdiv($asistenciasTarget, $instructores->count());
        $asistenciasExtra = $asistenciasTarget % $instructores->count();

        $this->command->info("📝 Generando {$asistenciasTarget} asistencias...");

        foreach ($instructores as $index => $instructor) {
            // Calcular cuántas asistencias crear para este instructor
            $asistenciasParaEste = $asistenciasPorInstructor + ($index < $asistenciasExtra ? 1 : 0);
            
            $this->command->info("👤 {$instructor->nombres} - {$asistenciasParaEste} registros");
            
            // Crear asistencias para este instructor
            for ($i = 0; $i < $asistenciasParaEste && $contadorAsistencias < $asistenciasTarget; $i++) {
                // Variar entre los últimos 15 días
                $diasAtras = rand(0, 14);
                $fecha = Carbon::now()->subDays($diasAtras);
                
                // Solo días laborales (lunes a viernes)
                while ($fecha->isWeekend()) {
                    $diasAtras++;
                    $fecha = Carbon::now()->subDays($diasAtras);
                }
                
                // Probabilidad del 70% de crear entrada
                if (rand(1, 100) <= 70) {
                    // ENTRADA
                    $horaBaseEntrada = Carbon::createFromTimeString('07:00');
                    $minutosVariacion = rand(-15, 45);
                    $horaEntrada = $fecha->copy()
                        ->setTimeFromTimeString($horaBaseEntrada->format('H:i'))
                        ->addMinutes($minutosVariacion);
                    
                    $esTardanza = $minutosVariacion > 15;
                    
                    Asistencia::create([
                        'instructor_id' => $instructor->id,
                        'guardia_id' => $guardia->id,
                        'tipo_movimiento' => 'entrada',
                        'fecha_hora_registro' => $horaEntrada,
                        'codigo_barras_leido' => $instructor->codigo_instructor ?? 'INST' . str_pad($instructor->id, 4, '0', STR_PAD_LEFT),
                        'ubicacion' => 'Entrada Principal',
                        'es_tardanza' => $esTardanza,
                        'es_salida_anticipada' => false,
                        'observaciones' => $esTardanza ? 'Llegada tardía' : null,
                        'estado_registro' => $esTardanza ? 'novedad' : 'normal'
                    ]);
                    
                    $contadorAsistencias++;
                    
                    // 80% de probabilidad de salida si hay entrada
                    if (rand(1, 100) <= 80 && $contadorAsistencias < $asistenciasTarget) {
                        $horaBaseSalida = Carbon::createFromTimeString('17:00');
                        $minutosVariacion = rand(-30, 20);
                        $horaSalida = $fecha->copy()
                            ->setTimeFromTimeString($horaBaseSalida->format('H:i'))
                            ->addMinutes($minutosVariacion);
                        
                        $esSalidaAnticipada = $minutosVariacion < -30;
                        
                        Asistencia::create([
                            'instructor_id' => $instructor->id,
                            'guardia_id' => $guardia->id,
                            'tipo_movimiento' => 'salida',
                            'fecha_hora_registro' => $horaSalida,
                            'codigo_barras_leido' => $instructor->codigo_instructor ?? 'INST' . str_pad($instructor->id, 4, '0', STR_PAD_LEFT),
                            'ubicacion' => 'Entrada Principal',
                            'es_tardanza' => false,
                            'es_salida_anticipada' => $esSalidaAnticipada,
                            'observaciones' => $esSalidaAnticipada ? 'Salida anticipada' : null,
                            'estado_registro' => $esSalidaAnticipada ? 'novedad' : 'normal'
                        ]);
                        
                        $contadorAsistencias++;
                    }
                }
            }
        }

        $totalAsistencias = Asistencia::count();
        $this->command->info("");
        $this->command->info("✅ Asistencias creadas exitosamente!");
        $this->command->info("📊 Total de asistencias en BD: {$totalAsistencias}");
        $this->command->info("👥 Instructores: {$instructores->count()}");
        $this->command->info("🔐 Guardia asignado: {$guardia->name}");
    }
}

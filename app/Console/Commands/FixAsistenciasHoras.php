<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asistencia;
use Carbon\Carbon;

class FixAsistenciasHoras extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-asistencias-horas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna horas por defecto a registros de asistencia sin fecha_hora_registro';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando registros de asistencia sin horas...');
        
        // Contar registros sin hora
        $sinHorasEntrada = Asistencia::where('tipo_movimiento', 'entrada')
            ->whereNull('fecha_hora_registro')
            ->count();
        
        $sinHorasSalida = Asistencia::where('tipo_movimiento', 'salida')
            ->whereNull('fecha_hora_registro')
            ->count();
        
        $this->line("Registros de ENTRADA sin hora: {$sinHorasEntrada}");
        $this->line("Registros de SALIDA sin hora: {$sinHorasSalida}");
        
        // Actualizar entradas sin hora: asignar 08:00 AM
        if ($sinHorasEntrada > 0) {
            $this->info("\nAsignando hora 08:00 AM a {$sinHorasEntrada} registros de entrada...");
            
            $today = Carbon::today();
            Asistencia::where('tipo_movimiento', 'entrada')
                ->whereNull('fecha_hora_registro')
                ->each(function($asistencia) use ($today) {
                    $asistencia->fecha_hora_registro = $today->copy()->setTime(8, 0, 0);
                    $asistencia->save();
                });
            
            $this->line("✓ {$sinHorasEntrada} registros de entrada actualizados");
        }
        
        // Actualizar salidas sin hora: asignar 17:00 (5 PM)
        if ($sinHorasSalida > 0) {
            $this->info("\nAsignando hora 17:00 (5 PM) a {$sinHorasSalida} registros de salida...");
            
            $today = Carbon::today();
            Asistencia::where('tipo_movimiento', 'salida')
                ->whereNull('fecha_hora_registro')
                ->each(function($asistencia) use ($today) {
                    $asistencia->fecha_hora_registro = $today->copy()->setTime(17, 0, 0);
                    $asistencia->save();
                });
            
            $this->line("✓ {$sinHorasSalida} registros de salida actualizados");
        }
        
        // Verificar resultado final
        $totalSinHoras = Asistencia::whereNull('fecha_hora_registro')->count();
        
        if ($totalSinHoras === 0) {
            $this->info("\n✓ ¡ÉXITO! Todos los registros de asistencia tienen hora asignada.");
            $this->line("Total de asistencias en el sistema: " . Asistencia::count());
        } else {
            $this->warn("\n⚠ Aún hay {$totalSinHoras} registros sin hora.");
        }
        
        return Command::SUCCESS;
    }
}

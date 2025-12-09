<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\DetectAttendancePatterns;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // RF034: Ejecutar detección de patrones diariamente a las 6:00 AM
        $schedule->job(new DetectAttendancePatterns())
                 ->dailyAt('06:00')
                 ->timezone('America/Bogota');

        // Ejecutar comandos de limpieza de logs antiguos
        $schedule->command('model:prune')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

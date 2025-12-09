<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\PatternDetectionService;
use App\Notifications\LateArrivalPatternAlert;
use App\Notifications\AbsencePatternAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DetectAttendancePatterns implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * RF034: Job programado que se ejecuta diariamente para detectar rachas de retrasos/ausencias
     */
    public function handle(): void
    {
        $patternService = new PatternDetectionService();

        // Detectar rachas de retrasos
        $retrasosAlerta = $patternService->detectLateArrivalRachas(
            days: 30,
            threshold: 3,
            windowDays: 5
        );

        // Detectar rachas de ausencias
        $ausenciasAlerta = $patternService->detectAbsenceRachas(
            days: 30,
            threshold: 3,
            windowDays: 5
        );

        // Obtener coordinadores/admins para notificar
        $coordinadores = User::where('role', 'admin')
            ->orWhere('role', 'coordinador')
            ->get();

        // Enviar alertas si existen
        if (!empty($retrasosAlerta)) {
            foreach ($coordinadores as $coordinador) {
                $coordinador->notify(new LateArrivalPatternAlert($retrasosAlerta));
            }
            \Log::info('RF034: Alerta de retrasos enviada', [
                'cantidad_alertas' => count($retrasosAlerta),
                'destinatarios' => $coordinadores->count(),
            ]);
        }

        if (!empty($ausenciasAlerta)) {
            foreach ($coordinadores as $coordinador) {
                $coordinador->notify(new AbsencePatternAlert($ausenciasAlerta));
            }
            \Log::info('RF034: Alerta de ausencias enviada', [
                'cantidad_alertas' => count($ausenciasAlerta),
                'destinatarios' => $coordinadores->count(),
            ]);
        }

        // Registrar en log incluso si no hay alertas
        \Log::info('RF034: Detección de patrones completada', [
            'fecha' => now()->toIso8601String(),
            'retrasos_detectados' => count($retrasosAlerta),
            'ausencias_detectadas' => count($ausenciasAlerta),
        ]);
    }
}

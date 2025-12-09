<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LateArrivalPatternAlert extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * RF034: Notificación de alerta por racha de retrasos
     */
    protected $instructorData;
    protected $alertas;

    public function __construct(array $alertas)
    {
        $this->alertas = $alertas;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $cantidad = count($this->alertas);
        $lista = collect($this->alertas)
            ->map(fn($a) => "- {$a['instructor_nombre']}: {$a['retrasos']} retrasos en {$a['total_registros']} registros ({$a['porcentaje']}%)")
            ->implode("\n");

        return (new MailMessage)
            ->subject("🚨 Alerta: {$cantidad} instructores con rachas de retrasos")
            ->line("Se han detectado instructores con patrones preocupantes de retrasos:")
            ->line($lista)
            ->line("Período: Últimos 30 días")
            ->action('Ver Detalles en Panel', url('/admin/reportes-patrones'))
            ->line('Por favor revise estos casos y tome las acciones necesarias.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'tipo' => 'patron_retrasos',
            'cantidad_alertas' => count($this->alertas),
            'alertas' => $this->alertas,
            'mensaje' => 'Se detectaron ' . count($this->alertas) . ' instructores con rachas de retrasos',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}

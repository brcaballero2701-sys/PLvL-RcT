<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbsencePatternAlert extends Notification implements ShouldQueue
{
    use Queueable;

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
            ->map(fn($a) => "- {$a['instructor_nombre']}: {$a['ausencias']} ausencias en {$a['total_dias']} días ({$a['porcentaje']}%)")
            ->implode("\n");

        return (new MailMessage)
            ->subject("🚨 Alerta: {$cantidad} instructores con rachas de ausencias")
            ->line("Se han detectado instructores con patrones preocupantes de ausencias:")
            ->line($lista)
            ->line("Período: Últimos 30 días")
            ->action('Ver Detalles en Panel', url('/admin/reportes-patrones'))
            ->line('Por favor revise estos casos y tome las acciones necesarias.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'tipo' => 'patron_ausencias',
            'cantidad_alertas' => count($this->alertas),
            'alertas' => $this->alertas,
            'mensaje' => 'Se detectaron ' . count($this->alertas) . ' instructores con rachas de ausencias',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}

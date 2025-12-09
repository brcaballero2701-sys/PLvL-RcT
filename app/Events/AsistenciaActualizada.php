<?php

namespace App\Events;

use App\Models\Asistencia;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AsistenciaActualizada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $asistencia;
    public $cambios;

    /**
     * Create a new event instance.
     */
    public function __construct(Asistencia $asistencia, array $cambios = [])
    {
        $this->asistencia = $asistencia;
        $this->cambios = $cambios;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('asistencias.' . $this->asistencia->instructor_id),
            new PrivateChannel('asistencias'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->asistencia->id,
            'instructor_id' => $this->asistencia->instructor_id,
            'cambios' => $this->cambios,
            'estado_registro' => $this->asistencia->estado_registro,
            'observaciones' => $this->asistencia->observaciones,
            'fecha_actualizacion' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the name of the broadcast event.
     */
    public function broadcastAs(): string
    {
        return 'AsistenciaActualizada';
    }
}

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

class AsistenciaRegistrada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $asistencia;
    public $instructor;
    public $guardia;

    /**
     * Create a new event instance.
     */
    public function __construct(Asistencia $asistencia)
    {
        $this->asistencia = $asistencia;
        $this->instructor = $asistencia->instructor;
        $this->guardia = $asistencia->guardia;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
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
            'instructor_name' => $this->instructor->nombre ?? 'Desconocido',
            'tipo_movimiento' => $this->asistencia->tipo_movimiento,
            'fecha_hora_registro' => $this->asistencia->fecha_hora_registro->toIso8601String(),
            'ubicacion' => $this->asistencia->ubicacion,
            'es_tardanza' => $this->asistencia->es_tardanza,
            'es_salida_anticipada' => $this->asistencia->es_salida_anticipada,
            'estado_registro' => $this->asistencia->estado_registro,
            'observaciones' => $this->asistencia->observaciones,
            'guardia_name' => $this->guardia->name ?? 'Desconocido',
        ];
    }

    /**
     * Get the name of the broadcast event.
     */
    public function broadcastAs(): string
    {
        return 'AsistenciaRegistrada';
    }
}

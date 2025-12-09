<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DashboardSincronizado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $datos;
    public $tipo;

    /**
     * Create a new event instance.
     */
    public function __construct(string $tipo, array $datos = [])
    {
        $this->tipo = $tipo;
        $this->datos = $datos;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('dashboard'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'tipo' => $this->tipo,
            'datos' => $this->datos,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the name of the broadcast event.
     */
    public function broadcastAs(): string
    {
        return 'DashboardSincronizado';
    }
}

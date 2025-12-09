<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UsuarioEnLinea implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $estado;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, string $estado = 'online')
    {
        $this->user = $user;
        $this->estado = $estado;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('usuarios'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'nombre' => $this->user->name,
            'rol' => $this->user->role,
            'estado' => $this->estado,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the name of the broadcast event.
     */
    public function broadcastAs(): string
    {
        return 'UsuarioEnLinea';
    }
}

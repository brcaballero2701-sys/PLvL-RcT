<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $fillable = [
        'reader_code',
        'ip_address',
        'mac_address',
        'ubicacion',
        'active',
        'last_ping',
        'guardia_id',
        'modelo',
        'firmware',
        'intentos_fallidos',
        'notas',
    ];

    protected $casts = [
        'last_ping' => 'datetime',
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con guardia asignado
     */
    public function guardia(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardia_id');
    }

    /**
     * Scope para dispositivos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para dispositivos inactivos
     */
    public function scopeInactivos($query)
    {
        return $query->where('active', false);
    }

    /**
     * Scope para dispositivos con conexión reciente (últimas 5 min)
     */
    public function scopeConectados($query)
    {
        return $query->where('last_ping', '>=', now()->subMinutes(5));
    }

    /**
     * Scope para dispositivos desconectados
     */
    public function scopeDesconectados($query)
    {
        return $query->where('last_ping', '<', now()->subMinutes(5))
                     ->orWhereNull('last_ping');
    }

    /**
     * Actualizar último ping del dispositivo
     */
    public function registrarPing(): void
    {
        $this->update([
            'last_ping' => now(),
            'intentos_fallidos' => 0,
        ]);
    }

    /**
     * Registrar intento fallido de conexión
     */
    public function registrarIntentoFallido(): void
    {
        $intentos = $this->intentos_fallidos + 1;

        // Desactivar después de 5 intentos fallidos
        if ($intentos >= 5) {
            $this->update([
                'active' => false,
                'intentos_fallidos' => $intentos,
            ]);
        } else {
            $this->update(['intentos_fallidos' => $intentos]);
        }
    }

    /**
     * Verificar si está conectado
     */
    public function estaConectado(): bool
    {
        return $this->active && $this->last_ping && 
               $this->last_ping->greaterThan(now()->subMinutes(5));
    }
}

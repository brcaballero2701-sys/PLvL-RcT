<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorSchedule extends Model
{
    protected $fillable = [
        'instructor_id',
        'dia_semana',
        'hora_entrada',
        'hora_salida',
        'activo',
        'observaciones',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con instructor
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    /**
     * Scope para horarios activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para obtener horario de un día específico
     */
    public function scopeDelDia($query, string $dia)
    {
        return $query->where('dia_semana', strtolower($dia));
    }

    /**
     * Mapeo de números de día a nombres (Carbon weekday)
     */
    public static function getNombreDia(int $dayOfWeek): string
    {
        $mapeo = [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            0 => 'domingo',
        ];

        return $mapeo[$dayOfWeek] ?? 'lunes';
    }

    /**
     * Verificar si el instructor está llegando tarde según su horario
     */
    public function esRetrasoPara($horaLlegada): bool
    {
        return $horaLlegada > $this->hora_entrada;
    }
}

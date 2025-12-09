<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    protected $table = 'team_members';

    protected $fillable = [
        'nombre',
        'rol',
        'email',
        'celular',
        'cedula',
        'descripcion',
        'foto_url',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener todos los miembros activos ordenados
     */
    public static function getActivos()
    {
        return self::where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Obtener por rol
     */
    public static function getByRol($rol)
    {
        return self::where('rol', $rol)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
    }
}

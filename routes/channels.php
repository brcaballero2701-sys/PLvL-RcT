<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Canal privado para asistencias de un instructor específico
Broadcast::channel('asistencias.{instructorId}', function ($user, $instructorId) {
    // Solo admins pueden escuchar todas las asistencias
    // Los guardias pueden escuchar las suyas propias
    return $user->role === 'admin' || $user->id == auth()->id();
});

// Canal privado para todas las asistencias (solo admins)
Broadcast::channel('asistencias', function ($user) {
    return $user->role === 'admin';
});

// Canal privado para dashboard (solo usuarios autenticados)
Broadcast::channel('dashboard', function ($user) {
    return $user !== null;
});

// Canal privado para usuarios en línea (solo admins)
Broadcast::channel('usuarios', function ($user) {
    return $user->role === 'admin';
});

// Canal privado para notificaciones del usuario
Broadcast::channel('notificaciones.{userId}', function ($user, $userId) {
    return $user->id == $userId;
});

// Canal privado para alertas administrativas
Broadcast::channel('alertas.admin', function ($user) {
    return $user->role === 'admin';
});

// Canal privado para configuraciones (solo admins)
Broadcast::channel('configuraciones', function ($user) {
    return $user->role === 'admin';
});

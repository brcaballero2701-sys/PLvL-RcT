<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\AsistenciaController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\AdminUserController;

// Ruta de diagnóstico simple (sin React)
Route::get('/test', function () {
    return response('<h1>✅ Laravel está funcionando correctamente</h1><p>Si ves este mensaje, el servidor Laravel funciona.</p><a href="/login">Ir al Login</a>');
});

// Ruta de diagnóstico de datos
Route::get('/diagnostico', [\App\Http\Controllers\DiagnosticoController::class, 'check'])->name('diagnostico.check');

// RF027: Rutas públicas de health check y monitoreo
Route::get('/health', [\App\Http\Controllers\HealthController::class, 'check'])->name('health.check');
Route::get('/health/metrics', [\App\Http\Controllers\HealthController::class, 'metrics'])->name('health.metrics');

// RF033: Endpoint público para que lectores de código de barras registren su actividad
Route::post('/api/devices/ping', [\App\Http\Controllers\Admin\DeviceController::class, 'ping'])->name('api.devices.ping');
Route::get('/api/devices/status', [\App\Http\Controllers\Admin\DeviceController::class, 'status'])->name('api.devices.status');

// Ruta de diagnóstico con React/Inertia
Route::get('/test-react', function () {
    return Inertia::render('Test');
});

// Redirigir la página principal directamente al login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    
    // Redirigir según el rol del usuario
    if ($user && $user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user && $user->role === 'guardia') {
        return redirect()->route('guardia.dashboard');
    } elseif ($user && $user->role === 'vigilante') {
        return redirect()->route('guardia.dashboard');
    }
    
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // ===== RUTAS DE 2FA (Nueva) =====
    Route::get('/2fa/settings', [\App\Http\Controllers\TwoFactorAuthController::class, 'show'])->name('2fa.show');
    Route::post('/2fa/enable-email', [\App\Http\Controllers\TwoFactorAuthController::class, 'enableEmail'])->name('2fa.enable-email');
    Route::post('/2fa/send-code', [\App\Http\Controllers\TwoFactorAuthController::class, 'sendVerificationCode'])->name('2fa.send-code');
    Route::post('/2fa/verify-code', [\App\Http\Controllers\TwoFactorAuthController::class, 'verifyCode'])->name('2fa.verify-code');
    Route::post('/2fa/disable', [\App\Http\Controllers\TwoFactorAuthController::class, 'disable'])->name('2fa.disable');
    Route::post('/2fa/regenerate-backup-codes', [\App\Http\Controllers\TwoFactorAuthController::class, 'regenerateBackupCodes'])->name('2fa.regenerate-backup-codes');
    // ===== FIN RUTAS DE 2FA =====
    
    // ===== RUTAS DE TEMA (Nueva) =====
    Route::get('/theme/current', [\App\Http\Controllers\ThemeController::class, 'getCurrent'])->name('theme.current');
    Route::post('/theme/toggle', [\App\Http\Controllers\ThemeController::class, 'toggle'])->name('theme.toggle');
    Route::get('/accessibility/preferences', [\App\Http\Controllers\ThemeController::class, 'getAccessibility'])->name('accessibility.preferences');
    Route::post('/accessibility/update', [\App\Http\Controllers\ThemeController::class, 'updateAccessibility'])->name('accessibility.update');
    // ===== FIN RUTAS DE TEMA =====
});

Route::middleware(['auth'])->group(function () {
    Route::post('/admin/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])
        ->name('admin.users.reset-password');
});

// Admin Routes - Panel de administración
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('users', UserController::class);
    
    // Rutas consolidadas para gestión de instructores
    Route::resource('instructores', InstructorController::class);
    
    // ===== RUTAS DE HORARIOS POR INSTRUCTOR (RF008) =====
    Route::get('/instructores/{instructor}/horarios', [\App\Http\Controllers\Admin\InstructorScheduleController::class, 'show'])->name('instructores.horarios.show');
    Route::post('/instructores/{instructor}/horarios', [\App\Http\Controllers\Admin\InstructorScheduleController::class, 'store'])->name('instructores.horarios.store');
    Route::get('/api/instructores/{instructor}/horarios', [\App\Http\Controllers\Admin\InstructorScheduleController::class, 'getSchedules'])->name('api.instructores.horarios');
    Route::get('/api/instructores/{instructor}/horario-hoy', [\App\Http\Controllers\Admin\InstructorScheduleController::class, 'getTodaySchedule'])->name('api.instructores.horario-hoy');
    Route::post('/api/instructores/{instructor}/verificar-retraso', [\App\Http\Controllers\Admin\InstructorScheduleController::class, 'verificarRetraso'])->name('api.instructores.verificar-retraso');
    // ===== FIN RUTAS DE HORARIOS =====
    
    // Rutas para historial de asistencias
    Route::get('/historial', [AdminController::class, 'historial'])->name('historial');
    
    // Rutas para reportes
    Route::get('/reportes', [AdminController::class, 'reportes'])->name('reportes');
    Route::get('/reportes/exportar', [AdminController::class, 'exportarReporte'])->name('reportes.exportar');
    
    // ===== RUTAS DE CONFIGURACIONES (Nuevas) =====
    Route::get('/configuraciones', [\App\Http\Controllers\ConfigurationController::class, 'index'])->name('configuraciones');
    
    // Horarios y Asistencias
    Route::post('/configuraciones/horarios', [\App\Http\Controllers\ConfigurationController::class, 'updateSchedules'])->name('configuraciones.horarios');
    
    // Notificaciones
    Route::post('/configuraciones/notificaciones', [\App\Http\Controllers\ConfigurationController::class, 'updateNotifications'])->name('configuraciones.notificaciones');
    
    // Personalización - Rutas consolidadas
    Route::post('/configuraciones/personalizacion', [\App\Http\Controllers\ConfigurationController::class, 'updateCustomization'])->name('configuraciones.personalizacion');
    Route::post('/configuraciones/upload-logo', [\App\Http\Controllers\ConfigurationController::class, 'uploadLogo'])->name('customization.upload-logo');
    Route::post('/configuraciones/reset-logo', [\App\Http\Controllers\ConfigurationController::class, 'resetLogo'])->name('customization.reset');
    Route::post('/configuraciones/update-color', [\App\Http\Controllers\ConfigurationController::class, 'updateColor'])->name('customization.update-color');
    
    // Seguridad y Contraseñas
    Route::post('/configuraciones/seguridad', [\App\Http\Controllers\ConfigurationController::class, 'updateSecuritySettings'])->name('configuraciones.seguridad');
    Route::post('/configuraciones/resetear-contraseñas', [\App\Http\Controllers\ConfigurationController::class, 'resetPasswords'])->name('configuraciones.resetear-contraseñas');
    
    // Configuración Avanzada
    Route::post('/configuraciones/avanzada', [\App\Http\Controllers\ConfigurationController::class, 'updateAdvancedSettings'])->name('configuraciones.avanzada');
    
    // Respaldo y Restauración - Rutas consolidadas
    Route::post('/respaldo/generar', [\App\Http\Controllers\ConfigurationController::class, 'generarRespaldo'])->name('backup.generate');
    Route::get('/respaldo/listar', [\App\Http\Controllers\ConfigurationController::class, 'listarRespaldos'])->name('backup.list');
    Route::get('/respaldo/{filename}/descargar', [\App\Http\Controllers\ConfigurationController::class, 'descargarRespaldo'])->name('backup.download');
    Route::delete('/respaldo/{filename}', [\App\Http\Controllers\ConfigurationController::class, 'eliminarRespaldo'])->name('backup.delete');
    Route::post('/respaldo/subir', [\App\Http\Controllers\ConfigurationController::class, 'subirYRestaurar'])->name('backup.upload');
    Route::post('/respaldo/restaurar', [\App\Http\Controllers\ConfigurationController::class, 'confirmarRestauracion'])->name('backup.restore');
    Route::post('/respaldo/confirmar-restauracion', [\App\Http\Controllers\Admin\BackupController::class, 'confirmRestorePassword'])->name('backup.confirm-restore');
    Route::post('/respaldo/ejecutar-restauracion', [\App\Http\Controllers\Admin\BackupController::class, 'restoreBackup'])->name('backup.restore-execute');
    
    // Usuarios y Roles
    Route::post('/configuraciones/usuarios', [\App\Http\Controllers\ConfigurationController::class, 'storeUser'])->name('configuraciones.usuarios.store');
    Route::put('/configuraciones/usuarios/{user}', [\App\Http\Controllers\ConfigurationController::class, 'updateUser'])->name('configuraciones.usuarios.update');
    Route::delete('/configuraciones/usuarios/{user}', [\App\Http\Controllers\ConfigurationController::class, 'destroyUser'])->name('configuraciones.usuarios.destroy');
    Route::post('/configuraciones/usuarios/{user}/toggle-status', [\App\Http\Controllers\ConfigurationController::class, 'toggleUserStatus'])->name('configuraciones.usuarios.toggle');
    
    // Caché y Datos
    Route::post('/configuraciones/limpiar-cache', [\App\Http\Controllers\ConfigurationController::class, 'limpiarCache'])->name('configuraciones.limpiar-cache');
    Route::post('/configuraciones/eliminar-datos', [\App\Http\Controllers\ConfigurationController::class, 'eliminarTodosDatos'])->name('configuraciones.eliminar-datos');
    Route::post('/configuraciones/resetear-valores', [\App\Http\Controllers\ConfigurationController::class, 'resetToDefaults'])->name('configuraciones.resetear-valores');
    // ===== FIN RUTAS DE CONFIGURACIONES =====
    
    // Rutas para gestión de roles (antiguas - mantener compatibilidad)
    Route::get('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');
    Route::get('/roles/stats', [\App\Http\Controllers\Admin\RoleController::class, 'getUserStats'])->name('roles.stats');
    
    // Rutas para seguridad
    Route::get('/seguridad-contraseñas', [\App\Http\Controllers\Admin\SecurityController::class, 'index'])->name('security.index');
    Route::post('/seguridad/actualizar', [\App\Http\Controllers\Admin\SecurityController::class, 'updateAll'])->name('security.update');
    Route::post('/seguridad/politicas-contraseñas', [\App\Http\Controllers\Admin\SecurityController::class, 'updatePasswordPolicies'])->name('security.password-policies');
    Route::post('/seguridad/dos-factores', [\App\Http\Controllers\Admin\SecurityController::class, 'updateTwoFactor'])->name('security.two-factor');
    Route::post('/seguridad/bloqueo-automatico', [\App\Http\Controllers\Admin\SecurityController::class, 'updateAutoLock'])->name('security.auto-lock');
    Route::post('/seguridad/restablecer', [\App\Http\Controllers\Admin\SecurityController::class, 'resetToDefaults'])->name('security.reset');
    Route::get('/seguridad/estadisticas', [\App\Http\Controllers\Admin\SecurityController::class, 'getSecurityStats'])->name('security.stats');
    
    // ===== RUTAS DE DISPOSITIVOS (RF033) =====
    Route::resource('dispositivos', \App\Http\Controllers\Admin\DeviceController::class);
    Route::post('/dispositivos/{device}/toggle-active', [\App\Http\Controllers\Admin\DeviceController::class, 'toggleActive'])->name('dispositivos.toggle-active');
    Route::post('/dispositivos/{device}/reset-failed', [\App\Http\Controllers\Admin\DeviceController::class, 'resetFailedAttempts'])->name('dispositivos.reset-failed');
    Route::get('/dispositivos/api/status', [\App\Http\Controllers\Admin\DeviceController::class, 'status'])->name('dispositivos.api.status');
    // ===== FIN RUTAS DE DISPOSITIVOS =====
    
    // ===== RUTAS DE REPORTES VIGILANTES (RF005) =====
    Route::get('/reportes-vigilantes', [\App\Http\Controllers\Admin\ReportesVigilantesController::class, 'index'])->name('reportes-vigilantes.index');
    Route::get('/reportes-vigilantes/exportar', [\App\Http\Controllers\Admin\ReportesVigilantesController::class, 'exportar'])->name('reportes-vigilantes.exportar');
    Route::get('/api/reportes-vigilantes/turnos', [\App\Http\Controllers\Admin\ReportesVigilantesController::class, 'getTurnosAPI'])->name('api.reportes-vigilantes.turnos');
    // ===== FIN RUTAS DE REPORTES VIGILANTES =====
    
    // Rutas para vigilantes
    Route::get('/vigilantes', [AdminController::class, 'vigilantes'])->name('vigilantes.index');
    Route::get('/vigilantes/create', [AdminController::class, 'createVigilante'])->name('vigilantes.create');
    Route::post('/vigilantes', [AdminController::class, 'storeVigilante'])->name('vigilantes.store');
    Route::get('/vigilantes/{vigilante}', [AdminController::class, 'showVigilante'])->name('vigilantes.show');
    Route::get('/vigilantes/{vigilante}/edit', [AdminController::class, 'editVigilante'])->name('vigilantes.edit');
    Route::put('/vigilantes/{vigilante}', [AdminController::class, 'updateVigilante'])->name('vigilantes.update');
    Route::delete('/vigilantes/{vigilante}', [AdminController::class, 'destroyVigilante'])->name('vigilantes.destroy');
    
    // ===== RUTAS DE AUDITORÍA (Nueva) =====
    Route::get('/auditoria', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('auditoria.index');
    Route::get('/auditoria/{auditLog}', [\App\Http\Controllers\AuditLogController::class, 'show'])->name('auditoria.show');
    Route::get('/auditoria/estadisticas', [\App\Http\Controllers\AuditLogController::class, 'stats'])->name('auditoria.stats');
    Route::get('/auditoria/exportar', [\App\Http\Controllers\AuditLogController::class, 'export'])->name('auditoria.export');
    Route::post('/auditoria/limpiar', [\App\Http\Controllers\AuditLogController::class, 'cleanup'])->name('auditoria.cleanup');
    // ===== FIN RUTAS DE AUDITORÍA =====
    
    // Ruta para página Acerca De
    Route::get('/acerca-de', function () {
        return Inertia::render('Admin/AcercaDe');
    })->name('acerca-de');
});

// Guardia Routes - Sistema de registro de asistencias
Route::prefix('guardia')->middleware(['auth', 'guardia'])->name('guardia.')->group(function () {
    Route::get('/dashboard', [AsistenciaController::class, 'dashboard'])->name('dashboard');
    Route::post('/registrar-asistencia', [AsistenciaController::class, 'registrarAsistencia'])->name('registrar-asistencia');
    Route::get('/historial', [AsistenciaController::class, 'historial'])->name('historial');
    Route::post('/iniciar-turno', [AsistenciaController::class, 'iniciarTurno'])->name('iniciar-turno');
    Route::post('/finalizar-turno', [AsistenciaController::class, 'finalizarTurno'])->name('finalizar-turno');
});

// Rutas Públicas de Equipo/Créditos
Route::get('/team', [\App\Http\Controllers\TeamController::class, 'show'])->name('team.show');
Route::get('/api/team', [\App\Http\Controllers\TeamController::class, 'getAll'])->name('api.team.all');
Route::get('/api/team/{rol}', [\App\Http\Controllers\TeamController::class, 'getByRole'])->name('api.team.byRole');

// Rutas de autenticación
require __DIR__.'/auth.php';

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\NotificationSetting;
use App\Models\Asistencia;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Carbon\Carbon;

class ConfigurationController extends Controller
{
    public function index()
    {
        // Obtener todos los usuarios con sus datos reales
        $usuarios = User::select('id', 'name', 'email', 'role', 'codigo_guardia', 'ubicacion_asignada', 'turno_activo', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'codigo_guardia' => $user->codigo_guardia,
                    'ubicacion_asignada' => $user->ubicacion_asignada,
                    'status' => $user->turno_activo ? 'Activo' : 'Inactivo',
                    'created_at' => $user->created_at->format('d/m/Y'),
                    'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random'
                ];
            });

        // Estadísticas de roles reales
        $rolesStats = [
            'admin' => User::where('role', 'admin')->count(),
            'guardia' => User::where('role', 'guardia')->count(),
            'user' => User::where('role', 'user')->count(),
            'total' => User::count(),
        ];

        // Estadísticas de asistencias
        $today = Carbon::today();
        $asistenciasStats = [
            'total_hoy' => Asistencia::whereDate('created_at', $today)->count(),
            'puntuales' => Asistencia::whereDate('created_at', $today)->where('es_tardanza', false)->count(),
            'tarde' => Asistencia::whereDate('created_at', $today)->where('es_tardanza', true)->count(),
            'ausencias' => Asistencia::whereDate('created_at', $today)->where('estado_registro', 'alerta')->count(),
        ];

        // Asistencias recientes - OBTENER TODAS LAS ASISTENCIAS
        $asistenciasRecientes = Asistencia::with('instructor')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($asistencia) {
                // Determinar estado basado en columnas reales
                $estado = 'Presente';
                if ($asistencia->es_tardanza) {
                    $estado = 'Tarde';
                } elseif ($asistencia->estado_registro === 'alerta') {
                    $estado = 'Ausencia';
                } elseif ($asistencia->es_salida_anticipada) {
                    $estado = 'Salida Anticipada';
                } else {
                    $estado = 'Puntual';
                }

                // Obtener hora según tipo de movimiento
                $hora = $asistencia->fecha_hora_registro 
                    ? Carbon::parse($asistencia->fecha_hora_registro)->format('H:i') 
                    : 'N/A';

                return [

                    'instructor' => $asistencia->instructor->name ?? $asistencia->instructor->nombre_completo ?? 'N/A',
                    'area' => $asistencia->instructor->area_asignada ?? 'N/A',
                    'fecha' => $asistencia->created_at->format('d/m/Y'),
                    'entrada' => $asistencia->tipo_movimiento === 'entrada' ? $hora : 'N/A',
                    'salida' => $asistencia->tipo_movimiento === 'salida' ? $hora : 'N/A',
                    'estado' => $estado,
                ];
            })
            ->values()
            ->toArray();

        // Configuraciones del sistema
        $systemSettings = [
            'manana_inicio' => SystemSetting::getSetting('manana_inicio', '06:00'),
            'manana_fin' => SystemSetting::getSetting('manana_fin', '11:45'),
            'tarde_inicio' => SystemSetting::getSetting('tarde_inicio', '12:00'),
            'tarde_fin' => SystemSetting::getSetting('tarde_fin', '17:45'),
            'noche_inicio' => SystemSetting::getSetting('noche_inicio', '18:00'),
            'noche_fin' => SystemSetting::getSetting('noche_fin', '21:45'),
            'debug_mode' => SystemSetting::getSetting('debug_mode', false),
            'maintenance_mode' => SystemSetting::getSetting('maintenance_mode', false),
            'cache_enabled' => SystemSetting::getSetting('cache_enabled', true),
            'audit_logging' => SystemSetting::getSetting('audit_logging', true),
            'password_min_length' => SystemSetting::getSetting('password_min_length', 8),
            'password_require_uppercase' => SystemSetting::getSetting('password_require_uppercase', true),
            'password_require_numbers' => SystemSetting::getSetting('password_require_numbers', true),
            'password_require_special' => SystemSetting::getSetting('password_require_special', true),
            'max_login_attempts' => SystemSetting::getSetting('max_login_attempts', 5),
            'logo_path' => SystemSetting::getSetting('logo_path', '/images/sena-logo.png'),
            'language' => SystemSetting::getSetting('language', 'es'),
            'primary_color' => SystemSetting::getSetting('primary_color', 'green'),
            'color_scheme' => SystemSetting::getSetting('color_scheme', 'green-600'),
            'notification_color_success' => NotificationSetting::getSetting('color_success', 'green'),
            'notification_color_error' => NotificationSetting::getSetting('color_error', 'red'),
            'notification_color_warning' => NotificationSetting::getSetting('color_warning', 'yellow'),
            'notification_color_info' => NotificationSetting::getSetting('color_info', 'blue'),
            'notification_duration' => NotificationSetting::getSetting('duration', 5),
            'notification_position' => NotificationSetting::getSetting('position', 'top-right'),
            'notification_sound' => NotificationSetting::getSetting('sound', false),
            'notification_animation' => NotificationSetting::getSetting('animation', 'slide'),
        ];

        // Cargar lista de respaldos
        $backupFiles = [];
        $backupPath = storage_path('app/backups');
        if (is_dir($backupPath)) {
            $files = array_diff(scandir($backupPath), ['.', '..']);
            foreach ($files as $file) {
                $fullPath = $backupPath . '/' . $file;
                
                // Saltar directorios
                if (is_dir($fullPath)) {
                    continue;
                }
                
                // Incluir archivos que tengan extensión .sql o empiecen con 'backup_'
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $isBackupFile = (
                    $extension === 'sql' || 
                    strpos($file, 'backup_') === 0
                );
                
                if ($isBackupFile && is_file($fullPath)) {
                    $backupFiles[] = [
                        'name' => $file,
                        'size' => filesize($fullPath),
                        'size_formatted' => $this->formatBytes(filesize($fullPath)),
                        'date' => filemtime($fullPath),
                        'date_formatted' => date('d/m/Y H:i:s', filemtime($fullPath)),
                    ];
                }
            }
            
            // Ordenar por fecha descendente
            usort($backupFiles, fn($a, $b) => $b['date'] <=> $a['date']);
        }

        return Inertia::render('Admin/Configuraciones', [
            'usuarios' => $usuarios,
            'rolesStats' => $rolesStats,
            'asistenciasStats' => $asistenciasStats,
            'asistenciasRecientes' => $asistenciasRecientes,
            'systemSettings' => $systemSettings,
            'backupFiles' => $backupFiles
        ]);
    }

    // ========== HORARIOS Y ASISTENCIAS ==========
    public function updateSchedules(Request $request)
    {
        $validated = $request->validate([
            'manana_inicio' => 'required|date_format:H:i',
            'manana_fin' => 'required|date_format:H:i',
            'tarde_inicio' => 'required|date_format:H:i',
            'tarde_fin' => 'required|date_format:H:i',
            'noche_inicio' => 'required|date_format:H:i',
            'noche_fin' => 'required|date_format:H:i',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::setSetting($key, $value, 'string', 'Horario configurado');
        }

        return redirect()->back()->with('success', 'Horarios actualizados correctamente');
    }

    // ========== NOTIFICACIONES ==========
    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'color_success' => 'required|in:green,blue,purple',
            'color_error' => 'required|in:red,orange',
            'color_warning' => 'required|in:yellow,orange',
            'color_info' => 'required|in:blue,gray',
            'duration' => 'required|integer|in:3,5,8,0',
            'position' => 'required|in:top-right,top-left,bottom-right,bottom-left,top-center',
            'sound' => 'boolean',
            'animation' => 'required|in:slide,fade,bounce,scale',
        ]);

        NotificationSetting::setSetting('color_success', $validated['color_success'], 'string', 'Color de notificaciones de éxito');
        NotificationSetting::setSetting('color_error', $validated['color_error'], 'string', 'Color de notificaciones de error');
        NotificationSetting::setSetting('color_warning', $validated['color_warning'], 'string', 'Color de notificaciones de advertencia');
        NotificationSetting::setSetting('color_info', $validated['color_info'], 'string', 'Color de notificaciones de información');
        NotificationSetting::setSetting('duration', $validated['duration'], 'integer', 'Duración de notificaciones en segundos');
        NotificationSetting::setSetting('position', $validated['position'], 'string', 'Posición de notificaciones');
        NotificationSetting::setSetting('sound', $validated['sound'] ?? false, 'boolean', 'Reproducir sonido en notificaciones');
        NotificationSetting::setSetting('animation', $validated['animation'], 'string', 'Animación de notificaciones');

        return response()->json([
            'success' => true,
            'message' => 'Configuración de notificaciones actualizada exitosamente'
        ]);
    }

    // ========== PERSONALIZACIÓN ==========
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg,webp|max:2048'
        ]);

        if ($request->hasFile('logo')) {
            try {
                // Eliminar logo anterior si existe
                $oldLogo = SystemSetting::getSetting('logo_path');
                if ($oldLogo && Storage::exists($oldLogo)) {
                    Storage::delete($oldLogo);
                }

                $path = SystemSetting::uploadLogo($request->file('logo'));
                
                return response()->json([
                    'success' => true,
                    'message' => 'Logo actualizado correctamente',
                    'logo_path' => $path
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al subir el logo: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo subir el logo'
        ], 400);
    }

    public function resetLogo(Request $request)
    {
        try {
            $oldLogo = SystemSetting::getSetting('logo_path');
            if ($oldLogo && Storage::exists($oldLogo)) {
                Storage::delete($oldLogo);
            }

            SystemSetting::setSetting('logo_path', '/images/sena-logo.png', 'string', 'Logo por defecto');
            
            return response()->json([
                'success' => true,
                'message' => 'Logo restablecido al valor predeterminado',
                'logo_path' => '/images/sena-logo.png'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restablecer el logo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateCustomization(Request $request)
    {
        $validated = $request->validate([
            'color_scheme' => 'required|string',
            'language' => 'required|in:es,en,fr,pt',
        ]);

        SystemSetting::setSetting('color_scheme', $validated['color_scheme'], 'string', 'Esquema de colores del sistema');
        SystemSetting::setSetting('language', $validated['language'], 'string', 'Idioma del sistema');

        return redirect()->back()->with('success', 'Personalización actualizada correctamente');
    }

    public function updateColor(Request $request)
    {
        $validated = $request->validate([
            'primary_color' => 'required|string|in:green,blue,indigo,purple,red,orange,yellow,teal,cyan,gray,slate,stone',
        ]);

        SystemSetting::setSetting('primary_color', $validated['primary_color'], 'string', 'Color primario del sistema');

        return response()->json([
            'success' => true,
            'message' => 'Color guardado exitosamente',
            'color' => $validated['primary_color']
        ]);
    }

    // ========== SEGURIDAD Y CONTRASEÑAS ==========
    public function updateSecuritySettings(Request $request)
    {
        try {
            // Verificar que el usuario sea admin
            if (!auth()->user() || auth()->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => '❌ No tienes permiso para acceder a esta configuración'
                ], 403);
            }

            // Obtener datos del request - convertir explícitamente a booleanos
            $passwordMinLength = intval($request->input('password_min_length', 8));
            $requireUppercase = filter_var($request->input('password_require_uppercase', false), FILTER_VALIDATE_BOOLEAN);
            $requireNumbers = filter_var($request->input('password_require_numbers', false), FILTER_VALIDATE_BOOLEAN);
            $requireSpecial = filter_var($request->input('password_require_special', false), FILTER_VALIDATE_BOOLEAN);
            $expirationDays = intval($request->input('password_expiration_days', 90));

            // Validar datos con reglas complejas
            $validated = [
                'password_min_length' => $passwordMinLength,
                'password_require_uppercase' => $requireUppercase,
                'password_require_numbers' => $requireNumbers,
                'password_require_special' => $requireSpecial,
                'password_expiration_days' => $expirationDays,
            ];

            // Validar con reglas específicas
            if ($passwordMinLength < 6 || $passwordMinLength > 20) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ La longitud mínima debe estar entre 6 y 20 caracteres',
                    'errors' => ['password_min_length' => ['La longitud mínima debe estar entre 6 y 20 caracteres']]
                ], 422);
            }

            if ($requireSpecial && $passwordMinLength < 10) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Si se requieren caracteres especiales, la longitud mínima debe ser al menos 10 caracteres',
                    'errors' => ['password_min_length' => ['La longitud mínima debe ser al menos 10 caracteres si se requieren caracteres especiales']]
                ], 422);
            }

            if ($expirationDays < 0 || $expirationDays > 365) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Los días de expiración deben estar entre 0 y 365',
                    'errors' => ['password_expiration_days' => ['Los días de expiración deben estar entre 0 y 365']]
                ], 422);
            }

            // Iniciar transacción para garantizar consistencia
            \DB::beginTransaction();

            // Guardar todas las configuraciones de seguridad
            SystemSetting::setSetting(
                'password_min_length',
                $passwordMinLength,
                'integer',
                'Longitud mínima de contraseña'
            );
            SystemSetting::setSetting(
                'password_require_uppercase',
                $requireUppercase,
                'boolean',
                'Requiere mayúsculas en contraseña'
            );
            SystemSetting::setSetting(
                'password_require_numbers',
                $requireNumbers,
                'boolean',
                'Requiere números en contraseña'
            );
            SystemSetting::setSetting(
                'password_require_special',
                $requireSpecial,
                'boolean',
                'Requiere caracteres especiales en contraseña'
            );
            SystemSetting::setSetting(
                'password_expiration_days',
                $expirationDays,
                'integer',
                'Días de expiración de contraseña (0 = sin expiración)'
            );

            // Registrar en auditoría si existe el método
            if (auth()->user() && method_exists(auth()->user(), 'logActivity')) {
                auth()->user()->logActivity('security.settings.updated', [
                    'min_length' => $passwordMinLength,
                    'require_uppercase' => $requireUppercase,
                    'require_numbers' => $requireNumbers,
                    'require_special' => $requireSpecial,
                    'expiration_days' => $expirationDays,
                ]);
            }

            \DB::commit();

            // Obtener datos actualizados para devolver al frontend
            $updatedSettings = [
                'password_min_length' => $passwordMinLength,
                'password_require_uppercase' => $requireUppercase,
                'password_require_numbers' => $requireNumbers,
                'password_require_special' => $requireSpecial,
                'password_expiration_days' => $expirationDays,
                'updated_at' => now()->toDateTimeString(),
            ];

            return response()->json([
                'success' => true,
                'message' => '✅ Políticas de seguridad guardadas exitosamente',
                'data' => $updatedSettings,
                'timestamp' => now()->timestamp
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => '❌ Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('Error al actualizar configuración de seguridad', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => '❌ Error al guardar: ' . ($e->getMessage() ?: 'Error desconocido'),
                'timestamp' => now()->timestamp
            ], 500);
        }
    }

    public function resetPasswords(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|exists:users,id',
            'new_password' => ['required', Rules\Password::defaults()],
        ]);

        foreach ($validated['user_ids'] as $userId) {
            $user = User::find($userId);
            if ($user && $user->id !== auth()->id()) {
                $user->update(['password' => Hash::make($validated['new_password'])]);
            }
        }

        return redirect()->back()->with('success', 'Contraseñas reiniciadas correctamente');
    }

    // ========== RESPALDO Y RESTAURACIÓN ==========
    public function createBackup(Request $request)
    {
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $path = storage_path('app/backups');

            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }

            $command = sprintf(
                'mysqldump -u%s -p%s %s > %s',
                escapeshellarg(config('database.connections.mysql.username')),
                escapeshellarg(config('database.connections.mysql.password')),
                escapeshellarg(config('database.connections.mysql.database')),
                escapeshellarg($path . '/' . $filename)
            );

            exec($command, $output, $return);

            if ($return === 0) {
                return redirect()->back()->with('success', 'Respaldo creado exitosamente: ' . $filename);
            } else {
                return redirect()->back()->with('error', 'Error al crear el respaldo de base de datos');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function deleteBackup($filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (file_exists($path)) {
            unlink($path);
            return redirect()->back()->with('success', 'Respaldo eliminado correctamente');
        }

        return redirect()->back()->with('error', 'El archivo de respaldo no existe');
    }

    // ========== CONFIGURACIÓN AVANZADA ==========
    public function updateAdvancedSettings(Request $request)
    {
        $validated = $request->validate([
            'debug_mode' => 'boolean',
            'maintenance_mode' => 'boolean',
            'cache_enabled' => 'boolean',
            'audit_logging' => 'boolean',
        ]);

        SystemSetting::setSetting('debug_mode', $validated['debug_mode'] ?? false, 'boolean', 'Modo de depuración');
        SystemSetting::setSetting('maintenance_mode', $validated['maintenance_mode'] ?? false, 'boolean', 'Modo de mantenimiento');
        SystemSetting::setSetting('cache_enabled', $validated['cache_enabled'] ?? false, 'boolean', 'Caché habilitado');
        SystemSetting::setSetting('audit_logging', $validated['audit_logging'] ?? false, 'boolean', 'Registro de auditoría');

        return redirect()->back()->with('success', 'Configuración avanzada actualizada');
    }

    public function clearCache()
    {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        return redirect()->back()->with('success', 'Caché del sistema limpiado correctamente');
    }

    public function resetToDefaults(Request $request)
    {
        $request->validate([
            'confirm' => 'required|accepted',
        ]);

        // Restablecer configuraciones predeterminadas
        SystemSetting::setSetting('debug_mode', false, 'boolean');
        SystemSetting::setSetting('maintenance_mode', false, 'boolean');
        SystemSetting::setSetting('cache_enabled', true, 'boolean');
        SystemSetting::setSetting('audit_logging', true, 'boolean');
        SystemSetting::setSetting('password_min_length', 8, 'integer');
        SystemSetting::setSetting('max_login_attempts', 5, 'integer');
        SystemSetting::setSetting('manana_inicio', '06:00', 'string');
        SystemSetting::setSetting('manana_fin', '11:45', 'string');
        SystemSetting::setSetting('tarde_inicio', '12:00', 'string');
        SystemSetting::setSetting('tarde_fin', '17:45', 'string');
        SystemSetting::setSetting('noche_inicio', '18:00', 'string');
        SystemSetting::setSetting('noche_fin', '21:45', 'string');

        return redirect()->back()->with('success', 'Configuración restablecida a valores predeterminados');
    }

    public function deleteAllData(Request $request)
    {
        $request->validate([
            'confirm' => 'required|accepted',
            'password' => 'required|current_password',
        ]);

        // Esta es una acción irreversible, solo admin puede hacerlo
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'No tienes permiso para esta acción');
        }

        try {
            // Eliminar todos los datos excepto el usuario admin actual
            Asistencia::truncate();
            User::where('id', '!=', auth()->id())->delete();
            
            // Restablecer configuraciones
            \Illuminate\Support\Facades\Artisan::call('migrate:refresh', ['--seed' => false]);

            return redirect()->back()->with('success', 'Todos los datos han sido eliminados correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar datos: ' . $e->getMessage());
        }
    }

    // ========== USUARIOS Y ROLES ==========
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Rules\Password::defaults()],
            'role' => 'required|in:admin,guardia,user',
            'codigo_guardia' => 'nullable|string|max:20|unique:users',
            'ubicacion_asignada' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'codigo_guardia' => $request->codigo_guardia,
            'ubicacion_asignada' => $request->ubicacion_asignada,
        ]);

        return redirect()->back()->with('success', 'Usuario creado correctamente');
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,guardia,user',
            'codigo_guardia' => 'nullable|string|max:20|unique:users,codigo_guardia,' . $user->id,
            'ubicacion_asignada' => 'nullable|string|max:255',
        ]);

        $user->update($request->only('name', 'email', 'role', 'codigo_guardia', 'ubicacion_asignada'));

        return redirect()->back()->with('success', 'Usuario actualizado correctamente');
    }

    public function destroyUser(User $user)
    {
        // No permitir que el admin se elimine a sí mismo
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes eliminar tu propia cuenta');
        }

        $user->delete();

        return redirect()->back()->with('success', 'Usuario eliminado correctamente');
    }

    public function toggleUserStatus(User $user)
    {
        $user->update([
            'turno_activo' => !$user->turno_activo
        ]);

        $status = $user->turno_activo ? 'activado' : 'desactivado';
        return redirect()->back()->with('success', "Usuario {$status} correctamente");
    }

    // ========== CACHÉ Y DATOS ==========
    public function limpiarCache()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');

            return response()->json([
                'success' => true,
                'message' => '✅ Caché del sistema limpiado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Error al limpiar caché: ' . $e->getMessage()
            ], 500);
        }
    }

    public function eliminarTodosDatos(Request $request)
    {
        try {
            $validated = $request->validate([
                'confirm' => 'required|accepted',
                'password' => 'required|current_password',
            ]);

            // Esta es una acción irreversible, solo admin puede hacerlo
            if (auth()->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => '❌ No tienes permiso para esta acción'
                ], 403);
            }

            // Eliminar todos los datos excepto el usuario admin actual
            Asistencia::truncate();
            User::where('id', '!=', auth()->id())->delete();

            return response()->json([
                'success' => true,
                'message' => '✅ Todos los datos han sido eliminados correctamente'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Error al eliminar datos: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== MÉTODOS MEJORADOS DE RESPALDO ==========
    public function generarRespaldo(Request $request)
    {
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $path = storage_path('app/backups');

            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }

            $connection = config('database.default');
            $credentials = config('database.connections.' . $connection);

            if ($connection === 'sqlite') {
                // Para SQLite, simplemente copiar el archivo
                copy($credentials['database'], $path . '/' . $filename);
            } else {
                // Para MySQL
                $command = sprintf(
                    'mysqldump -u%s -p%s %s > %s',
                    escapeshellarg($credentials['username']),
                    escapeshellarg($credentials['password']),
                    escapeshellarg($credentials['database']),
                    escapeshellarg($path . '/' . $filename)
                );

                exec($command, $output, $return);

                if ($return !== 0) {
                    return response()->json([
                        'success' => false,
                        'message' => '❌ Error al crear el respaldo de base de datos'
                    ], 500);
                }
            }

            return response()->json([
                'success' => true,
                'message' => '✅ Respaldo creado exitosamente: ' . $filename,
                'filename' => $filename,
                'size' => filesize($path . '/' . $filename),
                'date' => filemtime($path . '/' . $filename)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listarRespaldos()
    {
        try {
            $path = storage_path('app/backups');
            $backups = [];

            if (is_dir($path)) {
                $files = array_diff(scandir($path), ['.', '..']);
                foreach ($files as $file) {
                    $fullPath = $path . '/' . $file;
                    
                    // Saltar directorios
                    if (is_dir($fullPath)) {
                        continue;
                    }
                    
                    // Incluir archivos que:
                    // 1. Tengan extensión .sql
                    // 2. Empiecen con 'backup_' o 'backup_sistema_'
                    // 3. Sean archivos válidos (no directorios)
                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                    $isBackupFile = (
                        $extension === 'sql' || 
                        strpos($file, 'backup_') === 0
                    );
                    
                    if ($isBackupFile && is_file($fullPath)) {
                        $backups[] = [
                            'filename' => $file,
                            'size' => filesize($fullPath),
                            'size_formatted' => $this->formatBytes(filesize($fullPath)),
                            'date' => filemtime($fullPath),
                            'date_formatted' => date('d/m/Y H:i:s', filemtime($fullPath)),
                            'extension' => $extension ?: 'backup'
                        ];
                    }
                }

                // Ordenar por fecha descendente
                usort($backups, fn($a, $b) => $b['date'] <=> $a['date']);
            }

            return response()->json([
                'success' => true,
                'backups' => $backups,
                'total' => count($backups)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Error al listar respaldos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function descargarRespaldo($filename)
    {
        try {
            $path = storage_path('app/backups/' . $filename);

            if (!file_exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ El archivo de respaldo no existe'
                ], 404);
            }

            // Leer el archivo y devolverlo como blob
            $fileContent = file_get_contents($path);
            
            return response()->make($fileContent, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => filesize($path),
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function subirYRestaurar(Request $request)
    {
        try {
            $request->validate([
                'backup_file' => 'required|file|mimes:sql|max:102400' // 100MB max
            ]);

            $path = storage_path('app/backups');
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }

            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $request->file('backup_file')->move($path, $filename);

            return response()->json([
                'success' => true,
                'message' => '✅ Archivo de respaldo subido correctamente',
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function confirmarRestauracion(Request $request)
    {
        try {
            $validated = $request->validate([
                'filename' => 'required|string',
                'password' => 'required|current_password'
            ]);

            $path = storage_path('app/backups/' . $validated['filename']);

            if (!file_exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ El archivo de respaldo no existe'
                ], 404);
            }

            $connection = config('database.default');
            $credentials = config('database.connections.' . $connection);

            if ($connection === 'sqlite') {
                // Para SQLite - hacer backup de seguridad primero
                $dbPath = $credentials['database'];
                $safetyBackup = $dbPath . '.safety_' . date('Y-m-d_H-i-s');
                
                if (file_exists($dbPath)) {
                    copy($dbPath, $safetyBackup);
                }
                
                // Restaurar
                if (!copy($path, $dbPath)) {
                    throw new \Exception('No se pudo restaurar el archivo de base de datos SQLite');
                }
            } else {
                // Para MySQL - usar mejor manejo de errores
                $tempErrorFile = tempnam(sys_get_temp_dir(), 'mysql_restore_');
                
                $command = sprintf(
                    'mysql -u%s -p%s %s < %s 2>%s',
                    escapeshellarg($credentials['username']),
                    escapeshellarg($credentials['password']),
                    escapeshellarg($credentials['database']),
                    escapeshellarg($path),
                    escapeshellarg($tempErrorFile)
                );

                exec($command, $output, $return);
                
                // Leer errores si los hay
                $errors = '';
                if (file_exists($tempErrorFile)) {
                    $errors = file_get_contents($tempErrorFile);
                    unlink($tempErrorFile);
                }

                if ($return !== 0) {
                    $errorMsg = $errors ?: 'Error desconocido al restaurar';
                    
                    // Limpiar el mensaje de error
                    $errorMsg = trim(preg_replace('/Warning:.*?\n/', '', $errorMsg));
                    
                    return response()->json([
                        'success' => false,
                        'message' => '❌ Error al restaurar: ' . substr($errorMsg, 0, 200)
                    ], 500);
                }
            }

            return response()->json([
                'success' => true,
                'message' => '✅ Base de datos restaurada correctamente'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Error de validación: contraseña incorrecta',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Error durante la restauración: ' . $e->getMessage()
            ], 500);
        }
    }

    public function eliminarRespaldo($filename)
    {
        try {
            $path = storage_path('app/backups/' . $filename);

            if (!file_exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ El archivo de respaldo no existe'
                ], 404);
            }

            unlink($path);

            return response()->json([
                'success' => true,
                'message' => '✅ Respaldo eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '❌ Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== MÉTODO AUXILIAR ==========
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
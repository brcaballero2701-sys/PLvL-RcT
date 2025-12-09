<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Seguridad Avanzada
    |--------------------------------------------------------------------------
    */

    'two_factor_auth' => [
        'enabled' => true,
        'method' => 'email', // 'email', 'sms', 'authenticator'
        'code_length' => 6,
        'code_expiry_minutes' => 10,
        'max_failed_attempts' => 5,
        'lock_duration_minutes' => 15,
        'backup_codes_count' => 10,
    ],

    'inactivity' => [
        'enabled' => true,
        'timeout_minutes' => 30,
        'warning_minutes' => 5, // Mostrar advertencia 5 minutos antes
        'check_on_every_request' => true,
        'exclude_routes' => [
            'login',
            'logout',
            'register',
            'password.request',
            'password.reset',
            'password.email',
        ],
    ],

    'audit' => [
        'enabled' => true,
        'track_logins' => true,
        'track_logouts' => true,
        'track_failed_logins' => true,
        'track_password_changes' => true,
        'track_role_changes' => true,
        'track_permission_changes' => true,
        'retention_days' => 90, // Mantener logs por 90 días
        'cleanup_schedule' => 'weekly', // daily, weekly, monthly
    ],

    'theme' => [
        'enabled' => true,
        'default' => 'light', // 'light', 'dark', 'system'
        'allow_system_preference' => true,
        'persist_preference' => true, // Guardar en BD y sesión
    ],

    'accessibility' => [
        'enable_high_contrast' => true,
        'enable_reduced_motion' => true,
        'enable_font_size_adjustment' => true,
        'enable_line_spacing_adjustment' => true,
        'font_sizes' => ['small', 'normal', 'large', 'extra-large'],
        'line_spacings' => ['tight', 'normal', 'loose', 'extra-loose'],
    ],

    'realtime' => [
        'enabled' => false, // Cambiar a true cuando implementes WebSockets
        'driver' => 'websockets', // 'websockets', 'pusher', 'redis'
        'events' => [
            'asistencias' => true,
            'usuarios' => true,
            'configuracion' => true,
            'seguridad' => true,
            'notificaciones' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Auditoría Avanzada
    |--------------------------------------------------------------------------
    */

    'audit_excludes' => [
        'models' => [
            // Modelos excluidos de auditoría
        ],
        'attributes' => [
            'password',
            'remember_token',
            'api_token',
            'secret',
        ],
    ],

    'audit_categories' => [
        'security' => ['login', 'logout', 'password_change', 'mfa_enabled', '2fa_verified'],
        'users' => ['create', 'update', 'delete', 'role_change'],
        'system' => ['configuration', 'settings_change', 'backup', 'restore'],
        'attendance' => ['check_in', 'check_out', 'report_generated'],
    ],
];

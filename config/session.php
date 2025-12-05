<?php

return [

    'driver' => env('SESSION_DRIVER', 'database'),

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => false,

    'encrypt' => (bool) env('SESSION_ENCRYPT', false),

    'files' => storage_path('framework/sessions'),

    'connection' => null,

    'table' => 'sessions',

    'store' => null,

    'lottery' => [2, 100],

    'cookie' => [
        'name' => 'laravel_session',
        'path' => env('SESSION_PATH', '/'),
        'domain' => env('SESSION_DOMAIN') === 'null' ? null : env('SESSION_DOMAIN', null),
        'secure' => (bool) env('SESSION_SECURE', false),
        'http_only' => true,
        'same_site' => env('SESSION_SAME_SITE', 'lax'),
    ],

];

<?php

return [

    'driver' => env('SESSION_DRIVER', 'database'),

    'lifetime' => env('SESSION_LIFETIME', 120),

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
        'domain' => env('SESSION_DOMAIN', null),
        'secure' => false,
        'http_only' => true,
        'same_site' => 'lax',
    ],

];

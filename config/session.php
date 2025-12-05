<?php

return [

    'driver' => env('SESSION_DRIVER', 'database'),

    'lifetime' => env('SESSION_LIFETIME', 120),

    'expire_on_close' => false,

    'encrypt' => false,

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION', null),

    'table' => 'sessions',

    'store' => env('SESSION_STORE', null),

    'lottery' => [2, 100],

    'cookie' => [
        'name' => env(
            'SESSION_COOKIE',
            Illuminate\Support\Str::slug(env('APP_NAME', 'Laravel'), '_').'_session'
        ),
        'path' => '/',
        'domain' => env('SESSION_DOMAIN', null),
        'secure' => env('SESSION_SECURE_COOKIES', false),
        'http_only' => true,
        'same_site' => 'lax',
        'partitioned' => false,
    ],

];

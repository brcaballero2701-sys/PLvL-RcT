<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | This option determines the default session driver that is utilized for
    | incoming requests. Laravel supports a variety of storage options to
    | persist session data. Database storage is a great default choice.
    |
    | Supported: "file", "cookie", "database", "memcached",
    |            "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of minutes that you wish the session
    | to be allowed to remain idle before it expires. If you want them
    | to immediately expire on the browser closing, set that option.
    |
    */

    'lifetime' => env('SESSION_LIFETIME', 120),

    'expire_on_close' => false,

    /*
    |--------------------------------------------------------------------------
    | Session Encryption
    |--------------------------------------------------------------------------
    |
    | This option allows you to easily specify that all of your sessions
    | should be encrypted before they are stored. All encryption will
    | be run automatically by Laravel and you can use the session
    | variables normally. This is great for security features.
    |
    */

    'encrypt' => false,

    /*
    |--------------------------------------------------------------------------
    | Session File Location
    |--------------------------------------------------------------------------
    |
    | When using the native session driver, we need a location where session
    | files may be stored. A default has been set for you but a different
    | location may be specified. This is only needed for file sessions.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Connection
    |--------------------------------------------------------------------------
    |
    | When using the "database" or "redis" session drivers, you may specify a
    | connection that should be used to manage these sessions. This should
    | correspond to a connection in your database configuration options.
    |
    */

    'connection' => env('SESSION_CONNECTION', null),

    /*
    |--------------------------------------------------------------------------
    | Session Database Table
    |--------------------------------------------------------------------------
    |
    | When using the "database" session driver, you may specify the table we
    | should use to manage the sessions. Of course, a sensible default is
    | provided for you; however, you are free to change this as needed.
    |
    */

    'table' => 'sessions',

    /*
    |--------------------------------------------------------------------------
    | Session Cache Store
    |--------------------------------------------------------------------------
    |
    | When using the "apc", "dynamodb", "memcached", or "redis" session
    | drivers and you wish to use cache for session storage, you should
    | specify the cache store that should be used. This value defines
    | the application cache stores configured in "cache.php".
    |
    */

    'store' => env('SESSION_STORE', null),

    /*
    |--------------------------------------------------------------------------
    | Session Sweeping Lottery
    |--------------------------------------------------------------------------
    |
    | Some session drivers must manually sweep their storage location to get
    | rid of old sessions from storage. Here are the chances that the
    | garbage collector will run on a given request. By default, the odds
    | are 2 out of 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may change the properties of the cookie that is used to store
    | a session identifier. The default settings may be used by most apps,
    | but you are free to change these when you want an added control.
    |
    */

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

    /*
    |--------------------------------------------------------------------------
    | Session Expiration Time
    |--------------------------------------------------------------------------
    |
    | This option allows you to set when a session expires based on the
    | amount of inactivity. This is useful if you want to automatically
    | log users out after a certain amount of time. You may specify this
    | in minutes.
    |
    */

    'same_site' => 'lax',

];

<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

try {
    $app->handleRequest(Request::capture());
} catch (Throwable $e) {
    // FORZAR que Render Free vea el error real
    error_log("=== LARAVEL BOOT ERROR ===");
    error_log($e->__toString());

    http_response_code(500);
    echo "<pre style='white-space:pre-wrap;font-size:14px'>";
    echo htmlspecialchars($e->__toString());
    echo "</pre>";
}

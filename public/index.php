<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Ensure required runtime storage directories exist
foreach ([
    __DIR__.'/../storage/framework/cache/data',
    __DIR__.'/../storage/framework/sessions',
    __DIR__.'/../storage/framework/views',
    __DIR__.'/../storage/logs',
    __DIR__.'/../bootstrap/cache',
    __DIR__.'/../database',
] as $runtimeDir) {
    if (!is_dir($runtimeDir)) {
        @mkdir($runtimeDir, 0777, true);
    }
}

// Ensure SQLite database file exists if SQLite connection is used
$sqlitePath = __DIR__.'/../database/database.sqlite';
if (!file_exists($sqlitePath)) {
    @touch($sqlitePath);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';


// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());

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

// Resilient database fallback: If no explicit remote DB host or URL is given in environment,
// default to pre-migrated sqlite database to ensure instant edge and serverless operation.
if (empty($_ENV['DB_HOST']) && empty(getenv('DB_HOST')) && empty($_ENV['DATABASE_URL']) && empty(getenv('DATABASE_URL'))) {
    if (file_exists($sqlitePath)) {
        $_ENV['DB_CONNECTION'] = 'sqlite';
        putenv('DB_CONNECTION=sqlite');
        $realDbPath = realpath($sqlitePath) ?: $sqlitePath;
        $_ENV['DB_DATABASE'] = $realDbPath;
        putenv("DB_DATABASE={$realDbPath}");
    }
}

// Instant platform diagnostic endpoint
if (isset($_SERVER['REQUEST_URI']) && str_starts_with($_SERVER['REQUEST_URI'], '/__diag')) {
    header('Content-Type: application/json; charset=utf-8');
    $dbOk = false;
    $dbError = null;
    $usersCount = 0;
    try {
        $pdo = new PDO('sqlite:' . ($realDbPath ?? $sqlitePath));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $usersCount = (int) $pdo->query('SELECT count(*) FROM users')->fetchColumn();
        $dbOk = true;
    } catch (\Throwable $e) {
        $dbError = $e->getMessage();
    }
    echo json_encode([
        'status' => 'ok',
        'php' => PHP_VERSION,
        'sapi' => PHP_SAPI,
        'db_sqlite' => [
            'exists' => file_exists($sqlitePath),
            'size' => file_exists($sqlitePath) ? filesize($sqlitePath) : 0,
            'connected' => $dbOk,
            'users_count' => $usersCount,
            'error' => $dbError,
        ],
        'env' => [
            'DB_CONNECTION' => getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? null),
            'APP_KEY_SET' => !empty(getenv('APP_KEY')) || !empty($_ENV['APP_KEY']),
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
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

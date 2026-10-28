<?php
header('Content-Type: application/json; charset=utf-8');

$res = [
    'time' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'pdo_drivers' => extension_loaded('pdo') ? PDO::getAvailableDrivers() : [],
    'extensions' => [
        'pdo' => extension_loaded('pdo'),
        'pdo_sqlite' => extension_loaded('pdo_sqlite'),
        'sqlite3' => extension_loaded('sqlite3'),
        'pdo_mysql' => extension_loaded('pdo_mysql'),
        'openssl' => extension_loaded('openssl'),
        'mbstring' => extension_loaded('mbstring'),
    ],
    'env_vars' => [
        'APP_KEY_SET' => !empty($_ENV['APP_KEY']) || !empty(getenv('APP_KEY')),
        'DB_CONNECTION' => $_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: null,
        'DB_HOST' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: null,
        'DB_PORT' => $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: null,
        'DB_DATABASE' => $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: null,
        'DATABASE_URL_SET' => !empty($_ENV['DATABASE_URL']) || !empty(getenv('DATABASE_URL')),
    ],
];

// Determine if SQLite should be used
$sqlitePath = __DIR__ . '/../database/database.sqlite';
$useSqlite = (getenv('DB_CONNECTION') === 'sqlite' || ($_ENV['DB_CONNECTION'] ?? null) === 'sqlite') ||
    (empty($_ENV['DB_HOST']) && empty(getenv('DB_HOST')) && empty($_ENV['DATABASE_URL']) && empty(getenv('DATABASE_URL')));

if ($useSqlite && file_exists($sqlitePath)) {
    try {
        $realPath = realpath($sqlitePath) ?: $sqlitePath;
        $pdo = new PDO('sqlite:' . $realPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $res['pdo_connected'] = true;
        $res['driver'] = 'sqlite';
        $res['database_file'] = $realPath;
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
        $res['tables_count'] = count($tables);
        $res['has_users_table'] = in_array('users', $tables);

        if ($res['has_users_table']) {
            $userCountStmt = $pdo->query("SELECT count(*) FROM users");
            $res['users_count'] = (int) $userCountStmt->fetchColumn();

            if ($res['users_count'] > 0) {
                $usersStmt = $pdo->query("SELECT id, name, email, is_super_admin FROM users LIMIT 10");
                $res['sample_users'] = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } catch (\Throwable $sqle) {
        $res['sqlite_error'] = $sqle->getMessage();
    }
}

// If SQLite was not connected, attempt MySQL
if (empty($res['pdo_connected'])) {
    $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
    $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
    $dbName = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: ($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'laravel');
    $user = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root';
    $pass = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

    $res['resolved_db'] = [
        'host' => $host,
        'port' => $port,
        'database' => $dbName,
        'username' => $user,
    ];

    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 2,
        ]);
        $res['pdo_connected'] = true;
        $res['driver'] = 'mysql';

        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $res['tables_count'] = count($tables);
        $res['has_users_table'] = in_array('users', $tables);

        if ($res['has_users_table']) {
            $userCountStmt = $pdo->query("SELECT count(*) FROM users");
            $res['users_count'] = (int) $userCountStmt->fetchColumn();

            if ($res['users_count'] > 0) {
                $usersStmt = $pdo->query("SELECT id, name, email, is_super_admin FROM users LIMIT 10");
                $res['sample_users'] = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } catch (\Throwable $e) {
        $res['pdo_connected'] = false;
        $res['pdo_error'] = $e->getMessage();
    }
}

// Handle action=clear (cache clear)
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        $res['clear_output'] = \Illuminate\Support\Facades\Artisan::output();
        $res['clear_status'] = 'success';
    } catch (\Throwable $e) {
        $res['clear_status'] = 'error';
        $res['clear_error'] = $e->getMessage();
    }
}

echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

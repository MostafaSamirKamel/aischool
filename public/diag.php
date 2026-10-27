<?php
header('Content-Type: application/json; charset=utf-8');

$res = [
    'time' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'extensions' => [
        'pdo' => extension_loaded('pdo'),
        'pdo_mysql' => extension_loaded('pdo_mysql'),
        'openssl' => extension_loaded('openssl'),
        'mbstring' => extension_loaded('mbstring'),
    ],
    'env_vars' => [
        'APP_KEY_SET' => !empty($_ENV['APP_KEY']) || !empty(getenv('APP_KEY')),
        'DB_HOST' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: null,
        'DB_PORT' => $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: null,
        'DB_DATABASE' => $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: null,
        'DB_NAME' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: null,
        'DB_USERNAME' => $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: null,
        'DB_USER' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: null,
        'DATABASE_URL_SET' => !empty($_ENV['DATABASE_URL']) || !empty(getenv('DATABASE_URL')),
    ],
];

// Determine DB credentials
$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
$dbName = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: ($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: ($_ENV['MYSQL_DATABASE'] ?? getenv('MYSQL_DATABASE') ?: 'laravel'));
$user = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: ($_ENV['DB_USER'] ?? getenv('DB_USER') ?: ($_ENV['MYSQL_USER'] ?? getenv('MYSQL_USER') ?: 'root'));
$pass = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: ($_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: ($_ENV['MYSQL_PASSWORD'] ?? getenv('MYSQL_PASSWORD') ?: ''));

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
        PDO::ATTR_TIMEOUT => 3,
    ]);
    $res['pdo_connected'] = true;

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

// Handle action=migrate
if (isset($_GET['action']) && $_GET['action'] === 'migrate') {
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        /** @var \Illuminate\Foundation\Application $app */
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $res['migrate_output'] = \Illuminate\Support\Facades\Artisan::output();

        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\TenantSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DemoUserSeeder', '--force' => true]);
        $res['seed_output'] = \Illuminate\Support\Facades\Artisan::output();

        $res['migration_status'] = 'success';
    } catch (\Throwable $e) {
        $res['migration_status'] = 'error';
        $res['migration_error'] = $e->getMessage();
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

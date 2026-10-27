<?php
echo 'PHP Version: ' . PHP_VERSION . PHP_EOL;
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'Laravel Version: ' . app()->version() . PHP_EOL;
echo 'DB Connection: ' . config('database.default') . PHP_EOL;
echo 'DB Database: ' . config('database.connections.sqlite.database') . PHP_EOL;

if (!is_dir('database/backups')) {
    mkdir('database/backups', 0777, true);
}

$backupPath = 'database/backups/backup_final_uat_20261025.sqlite';
copy('database/database.sqlite', $backupPath);
echo 'Backup created: ' . $backupPath . ' (size: ' . filesize($backupPath) . ' bytes)' . PHP_EOL;

// Check integrity
$db = DB::connection()->getPdo();
$integrity = $db->query("PRAGMA integrity_check")->fetchColumn();
$fks = count($db->query("PRAGMA foreign_key_check")->fetchAll());
echo "SQLite Integrity: {$integrity} | Foreign Key Violations: {$fks}" . PHP_EOL;

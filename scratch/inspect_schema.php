<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

echo "=== SCHEMA INSPECTION ===\n\n";

echo "--- installments table ---\n";
$cols = DB::select('PRAGMA table_info(installments)');
foreach ($cols as $c) {
    echo "  {$c->name}: {$c->type}" . ($c->dflt_value ? " DEFAULT {$c->dflt_value}" : "") . " " . ($c->notnull ? "NOT NULL" : "NULLABLE") . "\n";
}

echo "\n--- Check for installments CHECK constraint ---\n";
$sql = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='installments'");
foreach ($sql as $s) {
    echo $s->sql . "\n";
}

echo "\n--- attendance_records table ---\n";
$cols2 = DB::select('PRAGMA table_info(attendance_records)');
foreach ($cols2 as $c) {
    echo "  {$c->name}: {$c->type}" . ($c->dflt_value ? " DEFAULT {$c->dflt_value}" : "") . " " . ($c->notnull ? "NOT NULL" : "NULLABLE") . "\n";
}

echo "\n--- expense_categories table ---\n";
$cols3 = DB::select('PRAGMA table_info(expense_categories)');
foreach ($cols3 as $c) {
    echo "  {$c->name}: {$c->type}" . ($c->dflt_value ? " DEFAULT {$c->dflt_value}" : "") . " " . ($c->notnull ? "NOT NULL" : "NULLABLE") . "\n";
}

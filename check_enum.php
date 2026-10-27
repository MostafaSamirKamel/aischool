<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$result = DB::select("SHOW COLUMNS FROM student_parents WHERE Field = 'relation'");

if (!empty($result)) {
    echo "Column: relation\n";
    echo "Type: " . $result[0]->Type . "\n";
    echo "Null: " . $result[0]->Null . "\n";
    echo "Default: " . ($result[0]->Default ?? 'NULL') . "\n";
}

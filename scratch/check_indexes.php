<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$indexes = \Illuminate\Support\Facades\DB::select("PRAGMA index_list('assignment_submissions')");
foreach ($indexes as $idx) {
    echo $idx->name . ' (unique: ' . $idx->unique . ")\n";
    $info = \Illuminate\Support\Facades\DB::select("PRAGMA index_info('{$idx->name}')");
    foreach ($info as $col) {
        echo '  - ' . $col->name . "\n";
    }
}

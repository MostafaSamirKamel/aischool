<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$perms = \Spatie\Permission\Models\Permission::pluck('name')->toArray();
echo "Total permissions: " . count($perms) . "\n";
$academicPerms = array_filter($perms, function($p) {
    return str_contains($p, 'assignment') || str_contains($p, 'exam') || str_contains($p, 'grade') || str_contains($p, 'homework') || str_contains($p, 'submission') || str_contains($p, 'retake') || str_contains($p, 'resubmi');
});
foreach ($academicPerms as $ap) {
    echo " - " . $ap . "\n";
}

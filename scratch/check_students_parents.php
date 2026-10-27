<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$studentUsers = App\Models\User::role('student')->take(3)->get();
echo "--- Students ---" . PHP_EOL;
foreach ($studentUsers as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email}" . PHP_EOL;
}

$parentUsers = App\Models\User::role('parent')->take(3)->get();
echo "--- Parents ---" . PHP_EOL;
foreach ($parentUsers as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email}" . PHP_EOL;
}

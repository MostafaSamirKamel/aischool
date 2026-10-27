<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('is_super_admin', true)->first();
if (!$user) {
    $user = App\Models\User::first();
}
echo "User: " . ($user ? $user->email . " | ID: " . $user->id : "None") . "\n";

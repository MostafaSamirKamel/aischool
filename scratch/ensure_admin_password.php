<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('email', 'admin@admin.com')->first();
if ($user) {
    $user->update(['password' => Hash::make('password')]);
    echo "Password set to 'password' for {$user->email}\n";
} else {
    echo "User not found\n";
}

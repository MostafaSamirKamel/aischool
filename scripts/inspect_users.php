<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

echo "--- ROLES AND SAMPLE USERS ---\n";
foreach (Role::all() as $role) {
    $user = User::role($role->name)->first();
    if ($user) {
        $passMatch = Hash::check('password', $user->password) ? 'YES' : 'NO';
        echo sprintf("Role: %-15s | Email: %-30s | ID: %-4d | Pass is 'password': %s\n", $role->name, $user->email, $user->id, $passMatch);
    }
}


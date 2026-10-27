<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = ['super_admin', 'branch_manager', 'secretary', 'accountant', 'instructor', 'supervisor', 'student', 'parent'];

echo "=== USER CREDENTIALS BY ROLE ===\n";
foreach ($roles as $role) {
    $user = \App\Models\User::role($role)->first();
    if ($user) {
        $pwMatches = \Illuminate\Support\Facades\Hash::check('password', $user->password) ? 'password' : 'other';
        echo "Role: {$role}\n  Email: {$user->email}\n  Password: {$pwMatches}\n  Name: {$user->name}\n  Tenant: " . ($user->tenant_id ?? 'none') . "\n";
    } else {
        // Try searching without role constraint
        $u = null;
        if ($role === 'student') {
            $st = \App\Models\Student::with('user')->first();
            $u = $st?->user;
        } elseif ($role === 'parent') {
            $p = \App\Models\StudentParent::with('user')->first();
            $u = $p?->user;
        } elseif ($role === 'instructor') {
            $ins = \App\Models\Instructor::with('user')->first();
            $u = $ins?->user;
        } elseif ($role === 'supervisor') {
            $sup = \App\Models\Supervisor::with('user')->first();
            $u = $sup?->user;
        }
        if ($u) {
            echo "Role (from relation): {$role}\n  Email: {$u->email}\n  Name: {$u->name}\n";
        } else {
            echo "Role: {$role} -> NOT FOUND\n";
        }
    }
}

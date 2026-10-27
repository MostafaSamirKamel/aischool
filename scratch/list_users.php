<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

$roles = [
    'super_admin', 'admin', 'branch_manager', 'accountant', 'secretary', 
    'registration', 'supervisor', 'instructor', 'student', 'parent'
];

$output = [];
foreach ($roles as $r) {
    $users = User::role($r)->take(3)->get();
    foreach ($users as $u) {
        $output[$r][] = [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'pass_password' => Hash::check('password', $u->password) ? 'password' : 'unknown',
            'tenant_id' => $u->tenant_id,
        ];
    }
}

// Also check student users with clear student codes
$students = \App\Models\Student::with('user')->take(3)->get();
foreach ($students as $s) {
    if ($s->user) {
        $output['student_samples'][] = [
            'student_code' => $s->student_code,
            'name' => $s->user->name,
            'email' => $s->user->email,
            'phone' => $s->phone,
        ];
    }
}

// Check parent users with children
$parents = \App\Models\StudentParent::with(['parent', 'student.user'])->take(3)->get();
foreach ($parents as $p) {
    if ($p->parent) {
        $output['parent_samples'][] = [
            'parent_name' => $p->parent->name,
            'email' => $p->parent->email,
            'phone' => $p->parent->phone,
            'child_name' => $p->student?->user?->name,
        ];
    }
}

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$panels = [
    'الإدارة العامة والمالية والفروع (Admin Panel)' => [
        'url' => 'http://127.0.0.1:8000/admin',
        'accounts' => [
            ['role' => 'المدير العام (Super Admin)', 'email' => 'admin@admin.com', 'pwd' => 'password'],
            ['role' => 'مدير الفرع (Branch Manager)', 'email' => 'manager.cairo@aischool.test', 'pwd' => 'password'],
            ['role' => 'المحاسب المالي (Accountant)', 'email' => 'accountant.cairo@aischool.test', 'pwd' => 'password'],
            ['role' => 'موظف التسجيل والسكرتارية (Receptionist/Registrar)', 'email' => 'secretary.main@admin.com', 'pwd' => 'password'],
        ]
    ],
    'لوحة المدربين (Instructor Panel)' => [
        'url' => 'http://127.0.0.1:8000/instructor',
        'accounts' => [
            ['role' => 'مدرب برمجيات (Instructor)', 'email' => 'instructor.main@admin.com', 'pwd' => 'password'],
            ['role' => 'مدرب فرع القاهرة', 'email' => 'instructor.cairo.1@aischool.test', 'pwd' => 'password']
        ]
    ],
    'لوحة الإشراف التربوي (Supervisor Panel)' => [
        'url' => 'http://127.0.0.1:8000/supervisor',
        'accounts' => [
            ['role' => 'مشرف تربوي وأكاديمي (Supervisor)', 'email' => 'supervisor.main@admin.com', 'pwd' => 'password'],
            ['role' => 'مشرف فرع القاهرة', 'email' => 'supervisor.cairo@aischool.test', 'pwd' => 'password']
        ]
    ],
    'لوحة الطلاب (Student Panel)' => [
        'url' => 'http://127.0.0.1:8000/student',
        'accounts' => [
            ['role' => 'طالب برمجيات تجريبي', 'email' => 'student.cairo.1@aischool.test', 'pwd' => 'password'],
            ['role' => 'طالب منتظم 2', 'email' => 'student.cairo.2@aischool.test', 'pwd' => 'password']
        ]
    ],
    'لوحة أولياء الأمور (Parent Panel)' => [
        'url' => 'http://127.0.0.1:8000/parent',
        'accounts' => [
            ['role' => 'ولي أمر طالب', 'email' => 'parent.cairo.1@aischool.test', 'pwd' => 'password'],
            ['role' => 'ولي أمر طالب 2', 'email' => 'parent.cairo.2@aischool.test', 'pwd' => 'password']
        ]
    ]
];

foreach ($panels as $name => $data) {
    echo "=== {$name} ===" . PHP_EOL;
    echo "URL: {$data['url']}" . PHP_EOL;
    foreach ($data['accounts'] as $acc) {
        $exists = App\Models\User::where('email', $acc['email'])->first();
        if ($exists) {
            echo "  [OK] {$acc['role']}: {$acc['email']} | Pass: {$acc['pwd']} (Name: {$exists->name})" . PHP_EOL;
        } else {
            // Find alternate
            $roleName = match($acc['role']) {
                'المدير العام (Super Admin)' => 'super_admin',
                'مدير الفرع (Branch Manager)' => 'branch_manager',
                'المحاسب المالي (Accountant)' => 'accountant',
                'موظف التسجيل والسكرتارية (Receptionist/Registrar)' => 'secretary',
                'مدرب برمجيات (Instructor)' => 'instructor',
                'مشرف تربوي وأكاديمي (Supervisor)' => 'supervisor',
                'طالب برمجيات تجريبي' => 'student',
                'ولي أمر طالب' => 'parent',
                default => null
            };
            $alt = $roleName ? App\Models\User::role($roleName)->first() : null;
            if ($alt) {
                echo "  [ALT] {$acc['role']}: {$alt->email} | Pass: password (Name: {$alt->name})" . PHP_EOL;
            } else {
                echo "  [X] {$acc['role']}: {$acc['email']} NOT FOUND" . PHP_EOL;
            }
        }
    }
    echo PHP_EOL;
}

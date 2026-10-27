<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Ensure clean memorable demo accounts exist for Student and Parent
$demoStudent = App\Models\User::firstOrCreate(
    ['email' => 'student.demo@aischool.test'],
    [
        'name' => 'طالب برمجيات تجريبي (Demo Student)',
        'password' => Illuminate\Support\Facades\Hash::make('password'),
        'tenant_id' => 1,
        'status' => 'active',
    ]
);
if (!$demoStudent->hasRole('student')) {
    $demoStudent->assignRole('student');
}

$demoParent = App\Models\User::firstOrCreate(
    ['email' => 'parent.demo@aischool.test'],
    [
        'name' => 'ولي أمر تجريبي (Demo Parent)',
        'password' => Illuminate\Support\Facades\Hash::make('password'),
        'tenant_id' => 1,
        'status' => 'active',
    ]
);
if (!$demoParent->hasRole('parent')) {
    $demoParent->assignRole('parent');
}

// Connect student record to demoStudent
$studentRecord = App\Models\Student::where('user_id', $demoStudent->id)->first();
if (!$studentRecord) {
    // Link to an existing student without user_id or create
    $existingStudent = App\Models\Student::whereNull('user_id')->first();
    if ($existingStudent) {
        $existingStudent->update(['user_id' => $demoStudent->id]);
    }
}

// Connect guardian record to demoParent
$guardianRecord = App\Models\Guardian::where('user_id', $demoParent->id)->first();
if (!$guardianRecord) {
    $existingGuardian = App\Models\Guardian::whereNull('user_id')->first();
    if ($existingGuardian) {
        $existingGuardian->update(['user_id' => $demoParent->id]);
    }
}

echo "=== ALL DEMO ACCOUNTS READY ===" . PHP_EOL;
echo "Admin: admin@admin.com | password" . PHP_EOL;
echo "Manager: manager.cairo@aischool.test | password" . PHP_EOL;
echo "Accountant: accountant.cairo@aischool.test | password" . PHP_EOL;
echo "Secretary: secretary.main@admin.com | password" . PHP_EOL;
echo "Instructor: instructor.main@admin.com | password" . PHP_EOL;
echo "Supervisor: supervisor.main@admin.com | password" . PHP_EOL;
echo "Student: student.demo@aischool.test | password" . PHP_EOL;
echo "Parent: parent.demo@aischool.test | password" . PHP_EOL;

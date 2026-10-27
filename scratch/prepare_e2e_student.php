<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Installment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;

$tenant = Tenant::first();

// Clean up any previous browser test student
$oldUser = User::where('email', 'e2e.student@aischool.test')->first();
if ($oldUser) {
    $oldStudent = Student::where('user_id', $oldUser->id)->first();
    if ($oldStudent) {
        $enrs = Enrollment::where('student_id', $oldStudent->id)->get();
        foreach ($enrs as $e) {
            Installment::where('enrollment_id', $e->id)->delete();
            $e->delete();
        }
        $oldStudent->delete();
    }
    $oldUser->delete();
}

$user = User::create([
    'tenant_id' => $tenant->id,
    'name' => 'محمد كمال عبد الرحمن (اختبار سداد الأقساط)',
    'email' => 'e2e.student@aischool.test',
    'password' => bcrypt('password'),
    'phone' => '01099887766',
]);

$student = Student::create([
    'tenant_id' => $tenant->id,
    'user_id' => $user->id,
    'student_code' => 'STD-E2E-2026',
    'phone' => '01099887766',
    'gender' => 'male',
    'status' => 'active',
]);

$group = Group::where('tenant_id', $tenant->id)->first();
if (!$group) {
    $course = Course::where('tenant_id', $tenant->id)->first();
    $group = Group::create([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'name' => 'مجموعة الدفع المعتمد',
        'status' => 'active',
    ]);
}

$enrollment = Enrollment::create([
    'tenant_id' => $tenant->id,
    'student_id' => $student->id,
    'group_id' => $group->id,
    'enrollment_code' => 'ENR-E2E-001',
    'total_amount' => 1000.0,
    'paid_amount' => 0.0,
    'payment_plan' => 'installments',
    'status' => 'active',
    'enrollment_date' => now(),
]);

$installment = Installment::create([
    'tenant_id' => $tenant->id,
    'enrollment_id' => $enrollment->id,
    'installment_number' => 1,
    'amount' => 1000.0,
    'paid_amount' => 0.0,
    'due_date' => now()->format('Y-m-d'), // Due today!
    'status' => 'pending',
]);

echo "Created E2E Test Student:\n";
echo "Name: {$user->name}\n";
echo "Code: {$student->student_code}\n";
echo "Phone: {$student->phone}\n";
echo "Installment ID: {$installment->id} (Amount: {$installment->amount} EGP, Due: {$installment->due_date->format('Y-m-d')})\n";

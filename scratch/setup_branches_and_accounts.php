<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\User;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Group;
use App\Models\Vault;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

echo "=== STEP 1: CONFIGURE THE TWO BRANCHES ===" . PHP_EOL;

// Branch 1: أبو المطامير
$branchMatamir = Tenant::find(1);
if ($branchMatamir) {
    $branchMatamir->name = 'أبو المطامير';
    $branchMatamir->slug = 'abu-el-matamir';
    $branchMatamir->is_active = true;
    $branchMatamir->save();
} else {
    $branchMatamir = Tenant::create([
        'id' => 1,
        'name' => 'أبو المطامير',
        'slug' => 'abu-el-matamir',
        'is_active' => true,
    ]);
}
echo "Branch 1 (أبو المطامير) configured with ID: {$branchMatamir->id}" . PHP_EOL;

// Branch 2: الأونلاين
$branchOnline = Tenant::find(2);
if ($branchOnline) {
    $branchOnline->name = 'الأونلاين';
    $branchOnline->slug = 'online';
    $branchOnline->is_active = true;
    $branchOnline->save();
} else {
    $branchOnline = Tenant::create([
        'id' => 2,
        'name' => 'الأونلاين',
        'slug' => 'online',
        'is_active' => true,
    ]);
}
echo "Branch 2 (الأونلاين) configured with ID: {$branchOnline->id}" . PHP_EOL;

echo PHP_EOL . "=== STEP 2: CONFIGURE THE 4 REQUIRED ACCOUNTS ===" . PHP_EOL;

// 1. Abu El Matamir Manager: 01070801308
$managerMatamir = User::where('username', '01070801308')
    ->orWhere('phone', '01070801308')
    ->first();

if (!$managerMatamir) {
    $managerMatamir = new User();
}
$managerMatamir->name = 'مدير فرع أبو المطامير';
$managerMatamir->username = '01070801308';
$managerMatamir->phone = '01070801308';
$managerMatamir->email = 'manager.matamir@aischool.test';
$managerMatamir->password = Hash::make('password');
$managerMatamir->tenant_id = $branchMatamir->id;
$managerMatamir->status = 'active';
$managerMatamir->is_super_admin = false;
$managerMatamir->save();
$managerMatamir->syncRoles(['branch_manager']);
echo "Abu El Matamir Manager: ID={$managerMatamir->id}, Phone={$managerMatamir->phone}, Role=branch_manager" . PHP_EOL;

// 2. Online Manager: 01501101213
$managerOnline = User::where('username', '01501101213')
    ->orWhere('phone', '01501101213')
    ->first();

if (!$managerOnline) {
    $managerOnline = new User();
}
$managerOnline->name = 'مدير فرع الأونلاين';
$managerOnline->username = '01501101213';
$managerOnline->phone = '01501101213';
$managerOnline->email = 'manager.online@aischool.test';
$managerOnline->password = Hash::make('password');
$managerOnline->tenant_id = $branchOnline->id;
$managerOnline->status = 'active';
$managerOnline->is_super_admin = false;
$managerOnline->save();
$managerOnline->syncRoles(['branch_manager']);
echo "Online Manager: ID={$managerOnline->id}, Phone={$managerOnline->phone}, Role=branch_manager" . PHP_EOL;

// 3. Instructor: 01091812925
$instructorUser = User::where('username', '01091812925')
    ->orWhere('phone', '01091812925')
    ->first();

if (!$instructorUser) {
    $instructorUser = new User();
}
$instructorUser->name = 'أحمد حسن - مدرب أبو المطامير';
$instructorUser->username = '01091812925';
$instructorUser->phone = '01091812925';
$instructorUser->email = 'instructor.matamir@aischool.test';
$instructorUser->password = Hash::make('password');
$instructorUser->tenant_id = $branchMatamir->id;
$instructorUser->status = 'active';
$instructorUser->is_super_admin = false;
$instructorUser->save();
$instructorUser->syncRoles(['instructor']);

$instructorProfile = Instructor::where('user_id', $instructorUser->id)->first();
if (!$instructorProfile) {
    $instructorProfile = new Instructor();
    $instructorProfile->user_id = $instructorUser->id;
    $instructorProfile->employee_code = 'INS-MAT-01';
}
$instructorProfile->tenant_id = $branchMatamir->id;
$instructorProfile->phone = '01091812925';
$instructorProfile->specialization = 'علوم الحاسب والذكاء الاصطناعي';
$instructorProfile->hourly_rate = 150.00;
$instructorProfile->base_salary = 2000.00;
$instructorProfile->status = 'active';
$instructorProfile->save();
echo "Instructor: UserID={$instructorUser->id}, InstructorID={$instructorProfile->id}, Phone={$instructorUser->phone}, Role=instructor" . PHP_EOL;

// 4. Student: 01007287273
$studentUser = User::where('username', '01007287273')
    ->orWhere('phone', '01007287273')
    ->first();

if (!$studentUser) {
    $studentUser = new User();
}
$studentUser->name = 'زياد طارق - طالب أبو المطامير';
$studentUser->username = '01007287273';
$studentUser->phone = '01007287273';
$studentUser->email = 'student.matamir@aischool.test';
$studentUser->password = Hash::make('password');
$studentUser->tenant_id = $branchMatamir->id;
$studentUser->status = 'active';
$studentUser->is_super_admin = false;
$studentUser->save();
$studentUser->syncRoles(['student']);

$studentProfile = Student::where('user_id', $studentUser->id)->first();
if (!$studentProfile) {
    $studentProfile = new Student();
    $studentProfile->user_id = $studentUser->id;
    $studentProfile->student_code = 'STD-MAT-001';
}
$studentProfile->tenant_id = $branchMatamir->id;
$studentProfile->phone = '01007287273';
$studentProfile->gender = 'male';
$studentProfile->birth_date = '2012-05-15';
$studentProfile->national_id = '31205151801234';
$studentProfile->school_name = 'مدرسة أبو المطامير التجريبية';
$studentProfile->education_level = 'primary';
$studentProfile->status = 'active';
$studentProfile->save();
echo "Student: UserID={$studentUser->id}, StudentID={$studentProfile->id}, Phone={$studentUser->phone}, Role=student" . PHP_EOL;

<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Instructor;
use App\Models\Supervisor;
use App\Models\Group;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Tenant;
use App\Models\SupervisionGroupAssignment;
use App\Models\SupervisionInstructorAssignment;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "=== PREPARING REAL-WORLD E2E SYNTHETIC TEST FIXTURES ===\n";

DB::beginTransaction();
try {
    $tenant1 = Tenant::firstOrCreate(['id' => 1], ['name' => 'الفرع الرئيسي - القاهرة', 'status' => 'active']);
    $tenant2 = Tenant::firstOrCreate(['id' => 2], ['name' => 'فرع دمنهور', 'status' => 'active']);

    $roles = ['super_admin', 'branch_manager', 'secretary', 'supervisor', 'instructor', 'student', 'parent', 'accountant', 'registration'];
    foreach ($roles as $r) {
        Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
    }

    // 1. Super Admin
    $superAdmin = User::updateOrCreate(
        ['email' => 'superadmin@aischool.test'],
        [
            'name' => 'المدير العام التجريبي (E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'is_super_admin' => true,
            'status' => 'active',
        ]
    );
    $superAdmin->syncRoles(['super_admin']);

    // 2. Branch Manager Cairo
    $managerCairo = User::updateOrCreate(
        ['email' => 'manager.cairo@aischool.test'],
        [
            'name' => 'مدير فرع القاهرة (E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'is_super_admin' => false,
            'status' => 'active',
        ]
    );
    $managerCairo->syncRoles(['branch_manager']);

    // 3. Branch Manager Damanhour
    $managerDamanhour = User::updateOrCreate(
        ['email' => 'manager.damanhour@aischool.test'],
        [
            'name' => 'مدير فرع دمنهور (E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 2,
            'is_super_admin' => false,
            'status' => 'active',
        ]
    );
    $managerDamanhour->syncRoles(['branch_manager']);

    // 4. Instructor A
    $instAUser = User::updateOrCreate(
        ['email' => 'instructor.a@aischool.test'],
        [
            'name' => 'المدرب أحمد محمود (E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'status' => 'active',
        ]
    );
    $instAUser->syncRoles(['instructor']);
    $instructorA = Instructor::updateOrCreate(
        ['user_id' => $instAUser->id],
        [
            'tenant_id' => 1,
            'employee_code' => 'INS-E2E-001',
            'phone' => '01011112222',
            'status' => 'active'
        ]
    );

    // 5. Instructor B
    $instBUser = User::updateOrCreate(
        ['email' => 'instructor.b@aischool.test'],
        [
            'name' => 'المدرب خالد سليم (E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'status' => 'active',
        ]
    );
    $instBUser->syncRoles(['instructor']);
    $instructorB = Instructor::updateOrCreate(
        ['user_id' => $instBUser->id],
        [
            'tenant_id' => 1,
            'employee_code' => 'INS-E2E-002',
            'phone' => '01022223333',
            'status' => 'active'
        ]
    );

    // Ensure Courses
    $course1 = Course::firstOrCreate(['id' => 1], ['name' => 'HTML & CSS', 'tenant_id' => 1, 'price' => 1200]);
    $course2 = Course::firstOrCreate(['id' => 2], ['name' => 'JavaScript', 'tenant_id' => 1, 'price' => 1500]);

    // Ensure Groups
    $groupA = Group::firstOrCreate(['id' => 1], [
        'name' => 'HTML & CSS - مجموعة 1',
        'course_id' => 1,
        'instructor_id' => $instructorA->id,
        'tenant_id' => 1,
        'status' => 'active'
    ]);
    $groupB = Group::firstOrCreate(['id' => 2], [
        'name' => 'JavaScript - مجموعة 1',
        'course_id' => 2,
        'instructor_id' => $instructorA->id,
        'tenant_id' => 1,
        'status' => 'active'
    ]);

    // 6. Secretary A (Assigned Group A only, has phone visibility)
    $secA = User::updateOrCreate(
        ['email' => 'secretary.a@aischool.test'],
        [
            'name' => 'سارة سكرتيرة المجموعة أ (E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'status' => 'active',
        ]
    );
    $secA->syncRoles(['secretary']);
    $secA->givePermissionTo([
        'view_daily_follow_up_dashboard',
        'view_student_contact_numbers',
        'create_student_followup_task',
        'record_followup_contact',
        'reassign_follow_up_case',
        'view_students',
        'view_student_academic_monitoring'
    ]);
    SupervisionGroupAssignment::updateOrCreate(
        ['user_id' => $secA->id, 'group_id' => $groupA->id],
        ['tenant_id' => 1, 'assigned_by' => 1, 'assigned_at' => now(), 'revoked_at' => null]
    );

    // 7. Secretary B (Assigned Group B only, NO phone visibility)
    $secB = User::updateOrCreate(
        ['email' => 'secretary.b@aischool.test'],
        [
            'name' => 'منى سكرتيرة المجموعة ب (E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'status' => 'active',
        ]
    );
    $secB->syncRoles(['secretary']);
    $secB->givePermissionTo([
        'view_daily_follow_up_dashboard',
        'create_student_followup_task',
        'record_followup_contact',
        'reassign_follow_up_case',
        'view_students'
    ]);
    $secB->revokePermissionTo('view_student_contact_numbers');
    SupervisionGroupAssignment::updateOrCreate(
        ['user_id' => $secB->id, 'group_id' => $groupB->id],
        ['tenant_id' => 1, 'assigned_by' => 1, 'assigned_at' => now(), 'revoked_at' => null]
    );

    // 8. Secretary with Educational Supervision (Assigned Group A and Instructor A)
    $secSuper = User::updateOrCreate(
        ['email' => 'secretary.super@aischool.test'],
        [
            'name' => 'سلوى سكرتيرة ومشرفة أكاديمية (E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'status' => 'active',
        ]
    );
    $secSuper->syncRoles(['secretary']);
    $secSuper->givePermissionTo([
        'view_daily_follow_up_dashboard',
        'view_educational_supervision',
        'view_supervised_groups',
        'view_supervised_instructors',
        'view_instructor_homework_progress',
        'view_instructor_exam_progress',
        'view_instructor_session_progress',
    ]);
    SupervisionGroupAssignment::updateOrCreate(
        ['user_id' => $secSuper->id, 'group_id' => $groupA->id],
        ['tenant_id' => 1, 'assigned_by' => 1, 'assigned_at' => now(), 'revoked_at' => null]
    );
    SupervisionInstructorAssignment::updateOrCreate(
        ['user_id' => $secSuper->id, 'instructor_id' => $instructorA->id],
        ['tenant_id' => 1, 'assigned_by' => 1, 'assigned_at' => now(), 'revoked_at' => null]
    );

    // 9. Supervisor
    $supUser = User::updateOrCreate(
        ['email' => 'supervisor.cairo@aischool.test'],
        [
            'name' => 'المشرف الأكاديمي طارق (E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'status' => 'active',
        ]
    );
    $supUser->syncRoles(['supervisor']);
    Supervisor::updateOrCreate(
        ['user_id' => $supUser->id],
        [
            'tenant_id' => 1,
            'employee_number' => 'SUP-E2E-001',
            'phone' => '01033334444',
            'status' => 'active'
        ]
    );
    SupervisionGroupAssignment::updateOrCreate(
        ['user_id' => $supUser->id, 'group_id' => $groupA->id],
        ['tenant_id' => 1, 'assigned_by' => 1, 'assigned_at' => now(), 'revoked_at' => null]
    );

    // 10. Accountant
    $accUser = User::updateOrCreate(
        ['email' => 'accountant.cairo@aischool.test'],
        [
            'name' => 'محاسب فرع القاهرة (E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'status' => 'active',
        ]
    );
    $accUser->syncRoles(['accountant']);
    $accUser->givePermissionTo([
        'view_vault_balance',
        'view_vault_transactions',
        'create_payments',
        'View:Payment',
        'View:Vault',
        'View:Installment'
    ]);

    // 11. Parent A
    $parentAUser = User::updateOrCreate(
        ['email' => 'parent.a@aischool.test'],
        [
            'name' => 'ولي أمر الطالب أ (محمد سمير E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'phone' => '01012345678',
            'status' => 'active',
        ]
    );
    $parentAUser->syncRoles(['parent']);

    // 12. Student A (in Group A)
    $studentAUser = User::updateOrCreate(
        ['email' => 'student.a@aischool.test'],
        [
            'name' => 'الطالب حسام محمد سمير (E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'phone' => '01112345678',
            'status' => 'active',
        ]
    );
    $studentAUser->syncRoles(['student']);
    $studentA = Student::updateOrCreate(
        ['user_id' => $studentAUser->id],
        [
            'student_code' => 'STU-A-1001',
            'national_id' => '30101011234567',
            'birth_date' => '2005-01-01',
            'gender' => 'male',
            'tenant_id' => 1,
            'phone' => '01112345678',
            'status' => 'active'
        ]
    );
    // Link Parent A to Student A
    StudentParent::updateOrCreate(
        ['user_id' => $parentAUser->id, 'student_id' => $studentA->id],
        ['relation' => 'father', 'is_primary' => true]
    );
    // Enroll Student A in Group A
    Enrollment::updateOrCreate(
        ['student_id' => $studentA->id, 'group_id' => $groupA->id],
        [
            'tenant_id' => 1,
            'enrollment_code' => 'ENR-A-1001',
            'enrollment_date' => now(),
            'status' => 'active',
            'total_amount' => 1200,
            'paid_amount' => 1200,
        ]
    );

    // 13. Parent B (Multiple Children: Student B & Student C)
    $parentBUser = User::updateOrCreate(
        ['email' => 'parent.b@aischool.test'],
        [
            'name' => 'ولي أمر الطالبين ب وج (إبراهيم كمال E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'phone' => '01212345678',
            'status' => 'active',
        ]
    );
    $parentBUser->syncRoles(['parent']);

    // 14. Student B (in Group A)
    $studentBUser = User::updateOrCreate(
        ['email' => 'student.b@aischool.test'],
        [
            'name' => 'الطالب يوسف إبراهيم كمال (E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'phone' => '01512345678',
            'status' => 'active',
        ]
    );
    $studentBUser->syncRoles(['student']);
    $studentB = Student::updateOrCreate(
        ['user_id' => $studentBUser->id],
        [
            'student_code' => 'STU-B-1002',
            'national_id' => '30202021234567',
            'birth_date' => '2006-02-02',
            'gender' => 'male',
            'tenant_id' => 1,
            'phone' => '01512345678',
            'status' => 'active'
        ]
    );
    StudentParent::updateOrCreate(
        ['user_id' => $parentBUser->id, 'student_id' => $studentB->id],
        ['relation' => 'father', 'is_primary' => true]
    );
    Enrollment::updateOrCreate(
        ['student_id' => $studentB->id, 'group_id' => $groupA->id],
        [
            'tenant_id' => 1,
            'enrollment_code' => 'ENR-B-1002',
            'enrollment_date' => now(),
            'status' => 'active',
            'total_amount' => 1200,
            'paid_amount' => 600,
        ]
    );

    // 15. Student C (Multi-Course: Group A & Group B)
    $studentCUser = User::updateOrCreate(
        ['email' => 'student.c@aischool.test'],
        [
            'name' => 'الطالبة نور إبراهيم كمال (E2E)',
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'phone' => '01098765432',
            'status' => 'active',
        ]
    );
    $studentCUser->syncRoles(['student']);
    $studentC = Student::updateOrCreate(
        ['user_id' => $studentCUser->id],
        [
            'student_code' => 'STU-C-1003',
            'national_id' => '30303031234567',
            'birth_date' => '2007-03-03',
            'gender' => 'female',
            'tenant_id' => 1,
            'phone' => '01098765432',
            'status' => 'active'
        ]
    );
    StudentParent::updateOrCreate(
        ['user_id' => $parentBUser->id, 'student_id' => $studentC->id],
        ['relation' => 'father', 'is_primary' => true]
    );
    // Enrolled in Course 1 (Group A)
    Enrollment::updateOrCreate(
        ['student_id' => $studentC->id, 'group_id' => $groupA->id],
        [
            'tenant_id' => 1,
            'enrollment_code' => 'ENR-C-1003-A',
            'enrollment_date' => now(),
            'status' => 'active',
            'total_amount' => 1200,
            'paid_amount' => 1200,
        ]
    );
    // Enrolled in Course 2 (Group B)
    Enrollment::updateOrCreate(
        ['student_id' => $studentC->id, 'group_id' => $groupB->id],
        [
            'tenant_id' => 1,
            'enrollment_code' => 'ENR-C-1003-B',
            'enrollment_date' => now(),
            'status' => 'active',
            'total_amount' => 1500,
            'paid_amount' => 1500,
        ]
    );

    DB::commit();
    echo "SUCCESS: All synthetic E2E accounts, groups, enrollments and permissions configured successfully!\n";
} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

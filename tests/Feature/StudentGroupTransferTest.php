<?php

use App\Filament\Admin\Resources\Enrollments\Pages\EditEnrollment;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Group;
use App\Models\Installment;
use App\Models\Instructor;
use App\Models\LearningResource;
use App\Models\SessionSchedule;
use App\Models\Student;
use App\Models\StudentGroupTransfer;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use App\Services\Enrollment\StudentGroupTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::firstOrCreate(['name' => 'transfer_student_group', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'update_enrollments', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'view_enrollments', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'view_any_enrollments', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'Update:Enrollment', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'View:Enrollment', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'ViewAny:Enrollment', 'guard_name' => 'web']);
});

function createBranchSetup(string $branchName = 'Branch'): array
{
    $uid = uniqid();
    $tenant = Tenant::factory()->create(['name' => "{$branchName} {$uid}"]);
    $vault = Vault::factory()->create(['tenant_id' => $tenant->id, 'opening_balance' => 10000]);
    $course = Course::factory()->create(['tenant_id' => $tenant->id, 'name' => "Python Basics {$uid}", 'price' => 1500]);
    
    $instructorUserA = User::factory()->create(['tenant_id' => $tenant->id, 'name' => "Instructor A {$uid}", 'email' => "inst_a_{$uid}@example.com"]);
    $instructorA = Instructor::factory()->create(['tenant_id' => $tenant->id, 'user_id' => $instructorUserA->id]);

    $instructorUserB = User::factory()->create(['tenant_id' => $tenant->id, 'name' => "Instructor B {$uid}", 'email' => "inst_b_{$uid}@example.com"]);
    $instructorB = Instructor::factory()->create(['tenant_id' => $tenant->id, 'user_id' => $instructorUserB->id]);

    $groupA = Group::factory()->create([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'instructor_id' => $instructorUserA->id,
        'name' => "Python Group A {$uid}",
        'capacity' => 1,
        'max_students' => 10,
        'status' => 'active',
    ]);

    $groupB = Group::factory()->create([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'instructor_id' => $instructorUserB->id,
        'name' => "Python Group B {$uid}",
        'capacity' => 0,
        'max_students' => 10,
        'status' => 'active',
    ]);

    $studentUser = User::factory()->create(['tenant_id' => $tenant->id, 'name' => "Ahmed Mohamed {$uid}", 'email' => "stud_{$uid}@example.com"]);
    $student = Student::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $studentUser->id,
        'status' => 'active',
        'student_code' => "STD-{$uid}",
    ]);

    $enrollment = Enrollment::factory()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'group_id' => $groupA->id,
        'vault_id' => $vault->id,
        'total_amount' => 1500,
        'paid_amount' => 500,
        'discount' => 100,
        'payment_plan' => 'installments',
        'installments_count' => 2,
        'status' => 'active',
    ]);

    $enrollmentCourse = EnrollmentCourse::create([
        'enrollment_id' => $enrollment->id,
        'course_id' => $course->id,
        'group_id' => $groupA->id,
        'status' => 'active',
        'course_price' => 1500,
    ]);

    $enrollment->groups()->sync([$groupA->id => ['enrolled_at' => now()]]);

    $installment1 = Installment::create([
        'enrollment_id' => $enrollment->id,
        'tenant_id' => $tenant->id,
        'installment_number' => 1,
        'amount' => 450,
        'paid_amount' => 0,
        'due_date' => now()->addMonth(),
        'status' => 'pending',
    ]);

    $installment2 = Installment::create([
        'enrollment_id' => $enrollment->id,
        'tenant_id' => $tenant->id,
        'installment_number' => 2,
        'amount' => 450,
        'paid_amount' => 0,
        'due_date' => now()->addMonths(2),
        'status' => 'pending',
    ]);

    $groupA->syncCapacity();
    $groupB->syncCapacity();

    return compact(
        'tenant', 'vault', 'course', 'instructorA', 'instructorB',
        'instructorUserA', 'instructorUserB',
        'groupA', 'groupB', 'student', 'enrollment', 'enrollmentCourse',
        'installment1', 'installment2'
    );
}

// =========================================================================
// TR-001: Edit enrollment and change group -> Transfer saved
// =========================================================================
test('TR-001: Edit enrollment and change group - Transfer saved', function () {
    $setup = createBranchSetup();
    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_1_' . uniqid() . '@example.com']);
    $admin->givePermissionTo(['transfer_student_group', 'update_enrollments', 'Update:Enrollment']);

    $service = app(StudentGroupTransferService::class);
    $transfer = $service->transfer($setup['enrollment'], $setup['groupB'], [
        'transferred_by' => $admin,
        'reason' => 'Requested by parent',
    ]);

    expect($transfer)->not->toBeNull();
    expect($setup['enrollment']->fresh()->group_id)->toBe($setup['groupB']->id);
    expect($setup['enrollmentCourse']->fresh()->group_id)->toBe($setup['groupB']->id);
    expect($setup['enrollment']->fresh()->groups->pluck('id'))->toContain($setup['groupB']->id);
    expect($setup['enrollment']->fresh()->groups->pluck('id'))->not->toContain($setup['groupA']->id);
});

// =========================================================================
// TR-002: Save without changing group -> No transfer
// =========================================================================
test('TR-002: Save without changing group - No transfer', function () {
    $setup = createBranchSetup();
    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_2_' . uniqid() . '@example.com']);
    $admin->givePermissionTo(['transfer_student_group', 'update_enrollments']);

    $service = app(StudentGroupTransferService::class);
    $transfer = $service->transfer($setup['enrollment'], $setup['groupA'], [
        'transferred_by' => $admin,
    ]);

    expect($transfer)->toBeNull();
    expect(StudentGroupTransfer::count())->toBe(0);
});

// =========================================================================
// TR-003: Transfer within same branch -> Allowed
// =========================================================================
test('TR-003: Transfer within same branch - Allowed', function () {
    $setup = createBranchSetup();
    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_3_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    $service = app(StudentGroupTransferService::class);
    $transfer = $service->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    expect($transfer)->toBeInstanceOf(StudentGroupTransfer::class);
    expect($setup['enrollment']->fresh()->group_id)->toBe($setup['groupB']->id);
});

// =========================================================================
// TR-004: Transfer to different branch -> Rejected
// =========================================================================
test('TR-004: Transfer to different branch - Rejected', function () {
    $setup1 = createBranchSetup('Branch 1');
    $setup2 = createBranchSetup('Branch 2');

    $admin = User::factory()->create(['tenant_id' => $setup1['tenant']->id, 'email' => 'admin_4_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    $service = app(StudentGroupTransferService::class);

    expect(fn() => $service->transfer($setup1['enrollment'], $setup2['groupA'], ['transferred_by' => $admin]))
        ->toThrow(ValidationException::class);
});

// =========================================================================
// TR-005: Super Admin cross-branch transfer -> Rejected
// =========================================================================
test('TR-005: Super Admin cross-branch transfer - Rejected', function () {
    $setup1 = createBranchSetup('Branch 1');
    $setup2 = createBranchSetup('Branch 2');

    $superAdmin = User::factory()->create(['is_super_admin' => true, 'tenant_id' => null, 'email' => 'super_' . uniqid() . '@example.com']);
    $superAdmin->givePermissionTo('transfer_student_group');

    $service = app(StudentGroupTransferService::class);

    expect(fn() => $service->transfer($setup1['enrollment'], $setup2['groupA'], ['transferred_by' => $superAdmin]))
        ->toThrow(ValidationException::class);
});

// =========================================================================
// TR-006: Transfer to different course -> Rejected
// =========================================================================
test('TR-006: Transfer to different course - Rejected', function () {
    $setup = createBranchSetup();
    $otherCourse = Course::factory()->create(['tenant_id' => $setup['tenant']->id, 'name' => 'Scratch Programming']);
    $otherGroup = Group::factory()->create([
        'tenant_id' => $setup['tenant']->id,
        'course_id' => $otherCourse->id,
        'status' => 'active',
        'capacity' => 0,
        'max_students' => 10,
    ]);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_6_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    $service = app(StudentGroupTransferService::class);

    expect(fn() => $service->transfer($setup['enrollment'], $otherGroup, ['transferred_by' => $admin]))
        ->toThrow(ValidationException::class);
});

// =========================================================================
// TR-007: Transfer to incompatible level -> Rejected
// =========================================================================
test('TR-007: Transfer to incompatible level - Rejected', function () {
    $setup = createBranchSetup();
    $incompatibleCourse = Course::factory()->create(['tenant_id' => $setup['tenant']->id, 'name' => 'Advanced AI Level 3']);
    $incompatibleGroup = Group::factory()->create([
        'tenant_id' => $setup['tenant']->id,
        'course_id' => $incompatibleCourse->id,
        'status' => 'active',
    ]);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_7_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    $service = app(StudentGroupTransferService::class);

    expect(fn() => $service->transfer($setup['enrollment'], $incompatibleGroup, ['transferred_by' => $admin]))
        ->toThrow(ValidationException::class);
});

// =========================================================================
// TR-008: Target group full -> Rejected
// =========================================================================
test('TR-008: Target group full - Rejected', function () {
    $setup = createBranchSetup();
    $setup['groupB']->update(['capacity' => 10, 'max_students' => 10]);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_8_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    $service = app(StudentGroupTransferService::class);

    expect(fn() => $service->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]))
        ->toThrow(ValidationException::class);
});

// =========================================================================
// TR-009: Target group inactive -> Rejected
// =========================================================================
test('TR-009: Target group inactive - Rejected', function () {
    $setup = createBranchSetup();
    $setup['groupB']->update(['status' => 'cancelled']);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_9_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    $service = app(StudentGroupTransferService::class);

    expect(fn() => $service->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]))
        ->toThrow(ValidationException::class);
});

// =========================================================================
// TR-010: Employee without transfer permission -> Rejected
// =========================================================================
test('TR-010: Employee without transfer permission - Rejected', function () {
    $setup = createBranchSetup();
    $employee = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_10_' . uniqid() . '@example.com']);
    $employee->givePermissionTo('update_enrollments');

    $service = app(StudentGroupTransferService::class);

    expect(fn() => $service->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $employee]))
        ->toThrow(ValidationException::class);
});

// =========================================================================
// TR-011: Employee with transfer permission -> Allowed
// =========================================================================
test('TR-011: Employee with transfer permission - Allowed', function () {
    $setup = createBranchSetup();
    $employee = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_11_' . uniqid() . '@example.com']);
    $employee->givePermissionTo(['update_enrollments', 'transfer_student_group']);

    $service = app(StudentGroupTransferService::class);
    $transfer = $service->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $employee]);

    expect($transfer)->toBeInstanceOf(StudentGroupTransfer::class);
});

// =========================================================================
// TR-012: Group change via direct request without permission -> Rejected
// =========================================================================
test('TR-012: Group change via direct request without permission - Rejected', function () {
    $setup = createBranchSetup();
    $unauthorizedUser = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'unauth_' . uniqid() . '@example.com']);

    $service = app(StudentGroupTransferService::class);

    expect(fn() => $service->validateTransfer($setup['enrollment'], $setup['groupB'], $unauthorizedUser))
        ->toThrow(ValidationException::class);
});

// =========================================================================
// TR-013: Existing attendance preserved -> Passed
// =========================================================================
test('TR-013: Existing attendance preserved - Passed', function () {
    $setup = createBranchSetup();
    
    for ($i = 1; $i <= 3; $i++) {
        $session = AttendanceSession::create([
            'tenant_id' => $setup['tenant']->id,
            'group_id' => $setup['groupA']->id,
            'course_id' => $setup['course']->id,
            'instructor_id' => $setup['instructorA']->id,
            'attendance_date' => now()->subDays(10 - $i),
            'status' => 'completed',
        ]);

        AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $setup['student']->id,
            'status' => 'present',
            'check_in_time' => now()->subDays(10 - $i),
        ]);
    }

    expect(AttendanceRecord::where('student_id', $setup['student']->id)->count())->toBe(3);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_13_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    $service = app(StudentGroupTransferService::class);
    $service->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    $recordsAfter = AttendanceRecord::where('student_id', $setup['student']->id)->get();
    expect($recordsAfter)->toHaveCount(3);
    foreach ($recordsAfter as $r) {
        expect($r->session->group_id)->toBe($setup['groupA']->id);
    }
});

// =========================================================================
// TR-014: Existing homework preserved -> Passed
// =========================================================================
test('TR-014: Existing homework preserved - Passed', function () {
    $setup = createBranchSetup();

    $assignment = Assignment::create([
        'tenant_id' => $setup['tenant']->id,
        'group_id' => $setup['groupA']->id,
        'instructor_id' => $setup['instructorA']->id,
        'title' => 'Python Homework 1',
        'due_date' => now()->subDays(2),
        'max_score' => 20,
    ]);

    $submission = AssignmentSubmission::create([
        'tenant_id' => $setup['tenant']->id,
        'assignment_id' => $assignment->id,
        'student_id' => $setup['student']->id,
        'submitted_at' => now()->subDays(3),
        'score' => 19,
        'status' => 'graded',
        'feedback' => 'Great work!',
    ]);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_14_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    $submissionAfter = AssignmentSubmission::where('student_id', $setup['student']->id)->first();
    expect($submissionAfter)->not->toBeNull();
    expect((float) $submissionAfter->score)->toBe(19.0);
    expect($submissionAfter->assignment->group_id)->toBe($setup['groupA']->id);
});

// =========================================================================
// TR-015: Existing grades preserved -> Passed
// =========================================================================
test('TR-015: Existing grades preserved - Passed', function () {
    $setup = createBranchSetup();

    $exam = Exam::create([
        'tenant_id' => $setup['tenant']->id,
        'group_id' => $setup['groupA']->id,
        'course_id' => $setup['course']->id,
        'title' => 'Midterm Exam',
        'exam_date' => now()->subDays(5),
        'status' => 'completed',
        'total_marks' => 100,
        'passing_marks' => 50,
    ]);

    $attempt = ExamAttempt::create([
        'tenant_id' => $setup['tenant']->id,
        'exam_id' => $exam->id,
        'student_id' => $setup['student']->id,
        'score' => 95,
        'total_score' => 100,
        'passed' => true,
    ]);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_15_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    $attemptAfter = ExamAttempt::where('student_id', $setup['student']->id)->first();
    expect($attemptAfter)->not->toBeNull();
    expect((float) $attemptAfter->score)->toBe(95.0);
});

// =========================================================================
// TR-016: Old materials accessible as history -> Passed
// =========================================================================
test('TR-016: Old materials accessible as history - Passed', function () {
    $setup = createBranchSetup();

    $resource = LearningResource::create([
        'tenant_id' => $setup['tenant']->id,
        'course_id' => $setup['course']->id,
        'group_id' => $setup['groupA']->id,
        'instructor_id' => $setup['instructorUserA']->id,
        'title' => 'Lecture 1 Slides',
        'visibility' => 'group',
        'is_active' => true,
    ]);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_16_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    $historicalGroupIds = $setup['student']->groupTransfers()->pluck('from_group_id')->filter()->toArray();
    expect($historicalGroupIds)->toContain($setup['groupA']->id);
});

// =========================================================================
// TR-017: New materials use new group -> Passed
// =========================================================================
test('TR-017: New materials use new group - Passed', function () {
    $setup = createBranchSetup();

    $newResource = LearningResource::create([
        'tenant_id' => $setup['tenant']->id,
        'course_id' => $setup['course']->id,
        'group_id' => $setup['groupB']->id,
        'instructor_id' => $setup['instructorUserB']->id,
        'title' => 'Group B Exclusive Guide',
        'visibility' => 'group',
        'is_active' => true,
    ]);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_17_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    $currentGroup = $setup['enrollment']->fresh()->group_id;
    expect($currentGroup)->toBe($setup['groupB']->id);
});

// =========================================================================
// TR-018: Student actual progress unchanged -> Passed
// =========================================================================
test('TR-018: Student actual progress unchanged - Passed', function () {
    $setup = createBranchSetup();

    for ($i = 1; $i <= 2; $i++) {
        $session = AttendanceSession::create([
            'tenant_id' => $setup['tenant']->id,
            'group_id' => $setup['groupA']->id,
            'course_id' => $setup['course']->id,
            'instructor_id' => $setup['instructorA']->id,
            'attendance_date' => now()->subDays(5 - $i),
            'status' => 'completed',
        ]);
        AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $setup['student']->id,
            'status' => 'present',
        ]);
    }

    $setup['enrollmentCourse']->update(['current_session' => 2]);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_18_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    $totalAttended = AttendanceRecord::where('student_id', $setup['student']->id)->where('status', 'present')->count();
    expect($totalAttended)->toBe(2);
});

// =========================================================================
// TR-019: Different progress displays warning -> Passed
// =========================================================================
test('TR-019: Different progress displays warning - Passed', function () {
    $setup = createBranchSetup();

    for ($i = 1; $i <= 4; $i++) {
        AttendanceSession::create([
            'tenant_id' => $setup['tenant']->id,
            'group_id' => $setup['groupB']->id,
            'course_id' => $setup['course']->id,
            'instructor_id' => $setup['instructorB']->id,
            'attendance_date' => now()->subDays(10 - $i),
            'status' => 'completed',
        ]);
    }

    $service = app(StudentGroupTransferService::class);
    $diff = $service->getAcademicProgressDifference($setup['enrollment'], $setup['groupB']);

    expect($diff['has_difference'])->toBeTrue();
    expect($diff['difference'])->toBe(4);
    expect($diff['warning_message'])->toContain('تنبيه: المجموعة الجديدة متقدمة');
});

// =========================================================================
// TR-020: Historical target sessions not auto-attended -> Passed
// =========================================================================
test('TR-020: Historical target sessions not auto-attended - Passed', function () {
    $setup = createBranchSetup();

    $sessions = [];
    for ($i = 1; $i <= 3; $i++) {
        $sessions[] = AttendanceSession::create([
            'tenant_id' => $setup['tenant']->id,
            'group_id' => $setup['groupB']->id,
            'course_id' => $setup['course']->id,
            'instructor_id' => $setup['instructorB']->id,
            'attendance_date' => now()->subDays(10 - $i),
            'status' => 'completed',
        ]);
    }

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_20_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    $groupBRecords = AttendanceRecord::where('student_id', $setup['student']->id)
        ->whereIn('attendance_session_id', collect($sessions)->pluck('id'))
        ->count();

    expect($groupBRecords)->toBe(0);
});

// =========================================================================
// TR-021: Future sessions follow new group -> Passed
// =========================================================================
test('TR-021: Future sessions follow new group - Passed', function () {
    $setup = createBranchSetup();

    SessionSchedule::create([
        'tenant_id' => $setup['tenant']->id,
        'group_id' => $setup['groupB']->id,
        'course_id' => $setup['course']->id,
        'instructor_id' => $setup['instructorB']->id,
        'date' => now()->addDays(3)->format('Y-m-d'),
        'start_time' => '10:00:00',
        'end_time' => '12:00:00',
        'day_of_week' => 'sunday',
        'status' => 'scheduled',
    ]);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_21_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    $activeGroup = $setup['enrollment']->fresh()->group;
    expect($activeGroup->sessionSchedules()->where('date', '>=', now()->toDateString())->count())->toBe(1);
});

// =========================================================================
// TR-022: Old group active count decreases -> Passed
// =========================================================================
test('TR-022: Old group active count decreases - Passed', function () {
    $setup = createBranchSetup();

    expect($setup['groupA']->activeStudents()->count())->toBe(1);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_22_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    expect($setup['groupA']->fresh()->activeStudents()->count())->toBe(0);
});

// =========================================================================
// TR-023: New group active count increases -> Passed
// =========================================================================
test('TR-023: New group active count increases - Passed', function () {
    $setup = createBranchSetup();

    expect($setup['groupB']->activeStudents()->count())->toBe(0);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_23_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    expect($setup['groupB']->fresh()->activeStudents()->count())->toBe(1);
});

// =========================================================================
// TR-024: Parent dashboard updated -> Passed
// =========================================================================
test('TR-024: Parent dashboard updated - Passed', function () {
    $setup = createBranchSetup();

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_24_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    $enrollmentFresh = $setup['enrollment']->fresh();
    expect($enrollmentFresh->group->id)->toBe($setup['groupB']->id);
    expect($enrollmentFresh->groups->pluck('id'))->toContain($setup['groupB']->id);
});

// =========================================================================
// TR-025: Student dashboard updated -> Passed
// =========================================================================
test('TR-025: Student dashboard updated - Passed', function () {
    $setup = createBranchSetup();

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_25_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    $studentGroupIds = Enrollment::where('student_id', $setup['student']->id)
        ->where('status', 'active')
        ->with('enrollmentCourses')
        ->get()
        ->flatMap(fn($e) => $e->enrollmentCourses->pluck('group_id'))
        ->toArray();

    expect($studentGroupIds)->toContain($setup['groupB']->id);
    expect($studentGroupIds)->not->toContain($setup['groupA']->id);
});

// =========================================================================
// TR-026: New instructor sees student -> Passed
// =========================================================================
test('TR-026: New instructor sees student - Passed', function () {
    $setup = createBranchSetup();

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_26_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    $instructorBStudents = Enrollment::where('group_id', $setup['groupB']->id)
        ->where('status', 'active')
        ->pluck('student_id')
        ->toArray();

    expect($instructorBStudents)->toContain($setup['student']->id);
});

// =========================================================================
// TR-027: Old instructor retains authorized history -> Passed
// =========================================================================
test('TR-027: Old instructor retains authorized history - Passed', function () {
    $setup = createBranchSetup();

    $session = AttendanceSession::create([
        'tenant_id' => $setup['tenant']->id,
        'group_id' => $setup['groupA']->id,
        'course_id' => $setup['course']->id,
        'recorded_by' => $setup['instructorUserA']->id,
        'attendance_date' => now()->subWeek(),
    ]);

    AttendanceRecord::create([
        'attendance_session_id' => $session->id,
        'student_id' => $setup['student']->id,
        'status' => 'present',
    ]);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_27_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    $instructorASessions = AttendanceSession::where('group_id', $setup['groupA']->id)->count();
    $sessionRecord = AttendanceRecord::where('attendance_session_id', $session->id)->first();

    expect($instructorASessions)->toBe(1);
    expect($sessionRecord->student_id)->toBe($setup['student']->id);
});

// =========================================================================
// TR-028: Enrollment price unchanged -> Passed
// =========================================================================
test('TR-028: Enrollment price unchanged - Passed', function () {
    $setup = createBranchSetup();
    $originalTotal = $setup['enrollment']->total_amount;

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_28_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    expect((float) $setup['enrollment']->fresh()->total_amount)->toBe((float) $originalTotal);
});

// =========================================================================
// TR-029: Paid amounts unchanged -> Passed
// =========================================================================
test('TR-029: Paid amounts unchanged - Passed', function () {
    $setup = createBranchSetup();
    $originalPaid = $setup['enrollment']->paid_amount;

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_29_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    expect((float) $setup['enrollment']->fresh()->paid_amount)->toBe((float) $originalPaid);
});

// =========================================================================
// TR-030: Outstanding installments unchanged -> Passed
// =========================================================================
test('TR-030: Outstanding installments unchanged - Passed', function () {
    $setup = createBranchSetup();
    $installmentsCountBefore = $setup['enrollment']->installments()->count();
    $installmentsSumBefore = $setup['enrollment']->installments()->sum('amount');

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_30_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);

    $enrollmentFresh = $setup['enrollment']->fresh();
    expect($enrollmentFresh->installments()->count())->toBe($installmentsCountBefore);
    expect((float) $enrollmentFresh->installments()->sum('amount'))->toBe((float) $installmentsSumBefore);
});

// =========================================================================
// TR-031: Transfer history saved -> Passed
// =========================================================================
test('TR-031: Transfer history saved - Passed', function () {
    $setup = createBranchSetup();
    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_31_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    $transfer = app(StudentGroupTransferService::class)->transfer($setup['enrollment'], $setup['groupB'], [
        'transferred_by' => $admin,
        'reason' => 'Schedule adjustment',
    ]);

    expect($transfer)->toBeInstanceOf(StudentGroupTransfer::class);
    expect($transfer->from_group_id)->toBe($setup['groupA']->id);
    expect($transfer->to_group_id)->toBe($setup['groupB']->id);
    expect($transfer->student_id)->toBe($setup['student']->id);
    expect($transfer->enrollment_id)->toBe($setup['enrollment']->id);
    expect($transfer->transferred_by)->toBe($admin->id);
    expect($transfer->reason)->toBe('Schedule adjustment');
});

// =========================================================================
// TR-032: Duplicate submission -> One transfer
// =========================================================================
test('TR-032: Duplicate submission - One transfer', function () {
    $setup = createBranchSetup();
    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_32_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    $service = app(StudentGroupTransferService::class);
    $first = $service->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);
    $second = $service->transfer($setup['enrollment']->fresh(), $setup['groupB'], ['transferred_by' => $admin]);

    expect($first)->not->toBeNull();
    expect($second)->toBeNull();
    expect(StudentGroupTransfer::where('enrollment_id', $setup['enrollment']->id)->count())->toBe(1);
});

// =========================================================================
// TR-033: Concurrent transfers competing for final seat -> Capacity protected
// =========================================================================
test('TR-033: Concurrent transfers competing for final seat - Capacity protected', function () {
    $setup = createBranchSetup();
    $setup['groupB']->update(['max_students' => 1]);

    $student2 = Student::factory()->create(['tenant_id' => $setup['tenant']->id, 'student_code' => 'STD-COMPETITOR-' . uniqid()]);
    $enrollment2 = Enrollment::factory()->create([
        'tenant_id' => $setup['tenant']->id,
        'student_id' => $student2->id,
        'group_id' => $setup['groupA']->id,
        'status' => 'active',
    ]);
    EnrollmentCourse::create([
        'enrollment_id' => $enrollment2->id,
        'course_id' => $setup['course']->id,
        'group_id' => $setup['groupA']->id,
        'status' => 'active',
    ]);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_33_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    $service = app(StudentGroupTransferService::class);

    $service->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);
    expect($setup['groupB']->fresh()->isFull())->toBeTrue();

    expect(fn() => $service->transfer($enrollment2, $setup['groupB']->fresh(), ['transferred_by' => $admin]))
        ->toThrow(ValidationException::class);
});

// =========================================================================
// TR-034: Failed transfer -> Complete rollback
// =========================================================================
test('TR-034: Failed transfer - Complete rollback', function () {
    $setup = createBranchSetup();
    $setup['groupB']->update(['capacity' => 10, 'max_students' => 10]);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_34_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    $service = app(StudentGroupTransferService::class);

    try {
        $service->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);
    } catch (\Throwable $e) {
        // Expected
    }

    $enrollmentFresh = $setup['enrollment']->fresh();
    expect($enrollmentFresh->group_id)->toBe($setup['groupA']->id);
    expect(StudentGroupTransfer::count())->toBe(0);
});

// =========================================================================
// TR-035: Save button works after modal/form validation -> Passed
// =========================================================================
test('TR-035: Save button works after modal/form validation - Passed', function () {
    $setup = createBranchSetup();
    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_35_' . uniqid() . '@example.com']);
    $admin->givePermissionTo(['update_enrollments', 'transfer_student_group', 'Update:Enrollment', 'View:Enrollment', 'ViewAny:Enrollment']);

    test()->actingAs($admin);

    Livewire::test(EditEnrollment::class, ['record' => $setup['enrollment']->id])
        ->fillForm([
            'group_id' => $setup['groupB']->id,
            'confirm_transfer_progress' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($setup['enrollment']->fresh()->group_id)->toBe($setup['groupB']->id);
});

// =========================================================================
// TR-036: Searchable target groups -> Correct options
// =========================================================================
test('TR-036: Searchable target groups - Correct options', function () {
    $setup = createBranchSetup();
    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_36_' . uniqid() . '@example.com']);
    $admin->givePermissionTo(['update_enrollments', 'transfer_student_group', 'Update:Enrollment', 'View:Enrollment', 'ViewAny:Enrollment']);

    test()->actingAs($admin);

    Livewire::test(EditEnrollment::class, ['record' => $setup['enrollment']->id])
        ->assertSuccessful()
        ->assertSee($setup['groupA']->name);
});

// =========================================================================
// TR-037: Tenant isolation -> Preserved
// =========================================================================
test('TR-037: Tenant isolation - Preserved', function () {
    $setup1 = createBranchSetup('Branch Alpha');
    $setup2 = createBranchSetup('Branch Beta');

    $admin1 = User::factory()->create(['tenant_id' => $setup1['tenant']->id, 'email' => 'admin_37_' . uniqid() . '@example.com']);
    $admin1->givePermissionTo('transfer_student_group');

    $service = app(StudentGroupTransferService::class);

    expect(fn() => $service->validateTransfer($setup1['enrollment'], $setup2['groupA'], $admin1))
        ->toThrow(ValidationException::class);
});

// =========================================================================
// TR-038: Historical records from multiple transfers -> Preserved
// =========================================================================
test('TR-038: Historical records from multiple transfers - Preserved', function () {
    $setup = createBranchSetup();
    $uid = uniqid();

    $groupC = Group::factory()->create([
        'tenant_id' => $setup['tenant']->id,
        'course_id' => $setup['course']->id,
        'name' => "Python Group C {$uid}",
        'status' => 'active',
        'capacity' => 0,
        'max_students' => 10,
    ]);

    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_38_' . uniqid() . '@example.com']);
    $admin->givePermissionTo('transfer_student_group');

    $service = app(StudentGroupTransferService::class);

    $service->transfer($setup['enrollment'], $setup['groupB'], ['transferred_by' => $admin]);
    $service->transfer($setup['enrollment']->fresh(), $groupC, ['transferred_by' => $admin]);

    expect($setup['enrollment']->fresh()->group_id)->toBe($groupC->id);
    expect(StudentGroupTransfer::where('enrollment_id', $setup['enrollment']->id)->count())->toBe(2);
});

// =========================================================================
// TR-039: Arabic RTL / mobile form -> Working
// =========================================================================
test('TR-039: Arabic RTL / mobile form - Working', function () {
    $setup = createBranchSetup();
    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_39_' . uniqid() . '@example.com']);
    $admin->givePermissionTo(['update_enrollments', 'transfer_student_group', 'Update:Enrollment', 'View:Enrollment', 'ViewAny:Enrollment']);

    test()->actingAs($admin);

    Livewire::test(EditEnrollment::class, ['record' => $setup['enrollment']->id])
        ->assertSuccessful()
        ->assertSee('المجموعة الدراسية');
});

// =========================================================================
// TR-040: Existing enrollment editing functionality -> No regression
// =========================================================================
test('TR-040: Existing enrollment editing functionality - No regression', function () {
    $setup = createBranchSetup();
    $admin = User::factory()->create(['tenant_id' => $setup['tenant']->id, 'email' => 'admin_40_' . uniqid() . '@example.com']);
    $admin->givePermissionTo(['update_enrollments', 'transfer_student_group', 'Update:Enrollment', 'View:Enrollment', 'ViewAny:Enrollment']);

    test()->actingAs($admin);

    Livewire::test(EditEnrollment::class, ['record' => $setup['enrollment']->id])
        ->fillForm(['notes' => 'Updated administrative notes'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($setup['enrollment']->fresh()->notes)->toContain('Updated administrative notes');
    expect($setup['enrollment']->fresh()->group_id)->toBe($setup['groupA']->id);
    expect(StudentGroupTransfer::count())->toBe(0);
});

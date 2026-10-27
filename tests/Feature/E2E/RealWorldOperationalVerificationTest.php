<?php

namespace Tests\Feature\E2E;

use App\Enums\CaseStatusEnum;
use App\Filament\Admin\Pages\AcademicFollowUpCenter;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Group;
use App\Models\Installment;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\SalaryPayment;
use App\Models\SessionSchedule;
use App\Models\Student;
use App\Models\StudentFollowUpContact;
use App\Models\StudentFollowUpTask;
use App\Models\StudentFollowUpTaskReassignment;
use App\Models\StudentParent;
use App\Models\Supervisor;
use App\Models\SupervisionGroupAssignment;
use App\Models\SupervisionInstructorAssignment;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultTransfer;
use App\Services\AcademicFollowUpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RealWorldOperationalVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $branchA;
    protected Tenant $branchB;

    protected User $superAdmin;
    protected User $managerA;
    protected User $secretaryA;
    protected User $secretaryB;
    protected User $secretarySuper;
    protected User $supervisorA;
    protected User $accountantA;

    protected User $instructorUserA;
    protected Instructor $instructorA;
    protected User $instructorUserB;
    protected Instructor $instructorB;

    protected Course $coursePython;
    protected Course $courseScratch;

    protected Group $groupA;
    protected Group $groupB;

    protected User $studentUserA;
    protected Student $studentA;
    protected User $parentUserA;

    protected User $studentUserB;
    protected Student $studentB;

    protected User $studentUserC;
    protected Student $studentC;
    protected User $parentUserB;

    protected Vault $vaultCashA;
    protected Vault $vaultVodafoneA;
    protected Vault $vaultCashB;

    protected function setUp(): void
    {
        parent::setUp();

        $roles = [
            'super_admin', 'branch_manager', 'secretary', 'supervisor',
            'instructor', 'student', 'parent', 'accountant', 'registration'
        ];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        $perms = [
            'view_daily_follow_up_dashboard', 'view_student_contact_numbers',
            'create_student_followup_task', 'record_followup_contact',
            'reassign_follow_up_case', 'view_students', 'view_student_academic_monitoring',
            'view_educational_supervision', 'view_supervised_groups', 'view_supervised_instructors',
            'view_vault_balance', 'view_vault_transactions', 'create_payments',
            'cancel_subscription', 'settle_subscription', 'refund_subscription'
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Branches
        $this->branchA = Tenant::create(['name' => 'الفرع الرئيسي - القاهرة']);
        $this->branchB = Tenant::create(['name' => 'فرع دمنهور']);

        // Vaults
        $this->vaultCashA = Vault::create([
            'tenant_id' => $this->branchA->id,
            'name' => 'خزينة الكاش - القاهرة',
            'type' => 'cash',
            'current_balance' => 10000,
            'is_active' => true,
        ]);
        $this->vaultVodafoneA = Vault::create([
            'tenant_id' => $this->branchA->id,
            'name' => 'فودافون كاش - القاهرة',
            'type' => 'vodafone',
            'current_balance' => 5000,
            'is_active' => true,
        ]);
        $this->vaultCashB = Vault::create([
            'tenant_id' => $this->branchB->id,
            'name' => 'خزينة الكاش - دمنهور',
            'type' => 'cash',
            'current_balance' => 10000,
            'is_active' => true,
        ]);

        // Users
        $this->superAdmin = User::create([
            'name' => 'المدير العام التجريبي',
            'email' => 'superadmin.live@aischool.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'is_super_admin' => true,
            'status' => 'active',
        ]);
        $this->superAdmin->assignRole('super_admin');

        $this->managerA = User::create([
            'name' => 'مدير فرع القاهرة',
            'email' => 'manager.live.cairo@aischool.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $this->managerA->assignRole('branch_manager');

        // Instructors
        $this->instructorUserA = User::create([
            'name' => 'المدرب أحمد محمود',
            'email' => 'instructor.live.a@aischool.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $this->instructorUserA->assignRole('instructor');
        $this->instructorA = Instructor::create([
            'user_id' => $this->instructorUserA->id,
            'tenant_id' => $this->branchA->id,
            'employee_code' => 'INS-LIVE-001',
            'phone' => '01011112222',
            'status' => 'active',
        ]);

        $this->instructorUserB = User::create([
            'name' => 'المدرب خالد سليم',
            'email' => 'instructor.live.b@aischool.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $this->instructorUserB->assignRole('instructor');
        $this->instructorB = Instructor::create([
            'user_id' => $this->instructorUserB->id,
            'tenant_id' => $this->branchA->id,
            'employee_code' => 'INS-LIVE-002',
            'phone' => '01022223333',
            'status' => 'active',
        ]);

        // Courses
        $this->coursePython = Course::create(['tenant_id' => $this->branchA->id, 'name' => 'Python Programming', 'price' => 1200]);
        $this->courseScratch = Course::create(['tenant_id' => $this->branchA->id, 'name' => 'Scratch Programming', 'price' => 1000]);

        // Groups
        $this->groupA = Group::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $this->coursePython->id,
            'instructor_id' => $this->instructorA->id,
            'name' => 'Python - Group A',
            'status' => 'active',
        ]);
        $this->groupB = Group::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $this->courseScratch->id,
            'instructor_id' => $this->instructorB->id,
            'name' => 'Scratch - Group B',
            'status' => 'active',
        ]);

        // Secretaries
        $this->secretaryA = User::create([
            'name' => 'سارة سكرتيرة المجموعة أ',
            'email' => 'secretary.live.a@aischool.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $this->secretaryA->assignRole('secretary');
        $this->secretaryA->givePermissionTo([
            'view_daily_follow_up_dashboard', 'view_student_contact_numbers',
            'create_student_followup_task', 'record_followup_contact',
            'reassign_follow_up_case', 'view_students', 'view_student_academic_monitoring'
        ]);
        SupervisionGroupAssignment::create([
            'user_id' => $this->secretaryA->id,
            'group_id' => $this->groupA->id,
            'tenant_id' => $this->branchA->id,
            'assigned_by' => $this->superAdmin->id,
            'assigned_at' => now(),
        ]);

        $this->secretaryB = User::create([
            'name' => 'منى سكرتيرة المجموعة ب',
            'email' => 'secretary.live.b@aischool.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $this->secretaryB->assignRole('secretary');
        $this->secretaryB->givePermissionTo([
            'view_daily_follow_up_dashboard',
            'create_student_followup_task', 'record_followup_contact',
            'reassign_follow_up_case', 'view_students'
        ]);
        SupervisionGroupAssignment::create([
            'user_id' => $this->secretaryB->id,
            'group_id' => $this->groupB->id,
            'tenant_id' => $this->branchA->id,
            'assigned_by' => $this->superAdmin->id,
            'assigned_at' => now(),
        ]);

        $this->secretarySuper = User::create([
            'name' => 'سلوى سكرتيرة ومشرفة',
            'email' => 'secretary.live.super@aischool.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $this->secretarySuper->assignRole('secretary');
        $this->secretarySuper->givePermissionTo([
            'view_daily_follow_up_dashboard', 'view_educational_supervision',
            'view_supervised_groups', 'view_supervised_instructors',
        ]);
        SupervisionGroupAssignment::create([
            'user_id' => $this->secretarySuper->id,
            'group_id' => $this->groupA->id,
            'tenant_id' => $this->branchA->id,
            'assigned_by' => $this->superAdmin->id,
            'assigned_at' => now(),
        ]);
        SupervisionInstructorAssignment::create([
            'user_id' => $this->secretarySuper->id,
            'instructor_id' => $this->instructorA->id,
            'tenant_id' => $this->branchA->id,
            'assigned_by' => $this->superAdmin->id,
            'assigned_at' => now(),
        ]);

        $this->supervisorA = User::create([
            'name' => 'المشرف طارق',
            'email' => 'supervisor.live@aischool.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $this->supervisorA->assignRole('supervisor');
        Supervisor::create([
            'user_id' => $this->supervisorA->id,
            'tenant_id' => $this->branchA->id,
            'employee_number' => 'SUP-LIVE-001',
            'national_id' => '28001011234567',
            'date_of_birth' => '1985-01-01',
            'gender' => 'male',
            'hire_date' => '2020-01-01',
            'phone' => '01033334444',
            'status' => 'active',
        ]);
        SupervisionGroupAssignment::create([
            'user_id' => $this->supervisorA->id,
            'group_id' => $this->groupA->id,
            'tenant_id' => $this->branchA->id,
            'assigned_by' => $this->superAdmin->id,
            'assigned_at' => now(),
        ]);

        $this->accountantA = User::create([
            'name' => 'المحاسب عادل',
            'email' => 'accountant.live@aischool.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $this->accountantA->assignRole('accountant');
        $this->accountantA->givePermissionTo(['view_vault_balance', 'view_vault_transactions', 'create_payments']);

        // Student A & Parent A
        $this->parentUserA = User::create([
            'name' => 'ولي أمر الطالب أ (محمد سمير)',
            'email' => 'parent.live.a@aischool.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'phone' => '01012345678',
            'status' => 'active',
        ]);
        $this->parentUserA->assignRole('parent');

        $this->studentUserA = User::create([
            'name' => 'الطالب حسام محمد سمير',
            'email' => 'student.live.a@aischool.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'phone' => '01112345678',
            'status' => 'active',
        ]);
        $this->studentUserA->assignRole('student');
        $this->studentA = Student::create([
            'user_id' => $this->studentUserA->id,
            'student_code' => 'STU-LIVE-001',
            'national_id' => '30101011234567',
            'tenant_id' => $this->branchA->id,
            'gender' => 'male',
            'birth_date' => '2005-01-01',
            'phone' => '01112345678',
            'status' => 'active',
        ]);
        StudentParent::create([
            'user_id' => $this->parentUserA->id,
            'student_id' => $this->studentA->id,
            'relation' => 'father',
            'is_primary' => true,
        ]);
        Enrollment::create([
            'student_id' => $this->studentA->id,
            'group_id' => $this->groupA->id,
            'tenant_id' => $this->branchA->id,
            'enrollment_code' => 'ENR-LIVE-001',
            'enrollment_date' => now(),
            'status' => 'active',
            'vault_id' => $this->vaultCashA->id,
            'total_amount' => 1200,
            'paid_amount' => 1200,
        ]);

        // Student B & Parent B
        $this->parentUserB = User::create([
            'name' => 'ولي أمر الطالبين ب وج (إبراهيم كمال)',
            'email' => 'parent.live.b@aischool.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'phone' => '01212345678',
            'status' => 'active',
        ]);
        $this->parentUserB->assignRole('parent');

        $this->studentUserB = User::create([
            'name' => 'الطالب يوسف إبراهيم كمال',
            'email' => 'student.live.b@aischool.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'phone' => '01512345678',
            'status' => 'active',
        ]);
        $this->studentUserB->assignRole('student');
        $this->studentB = Student::create([
            'user_id' => $this->studentUserB->id,
            'student_code' => 'STU-LIVE-002',
            'national_id' => '30202021234567',
            'tenant_id' => $this->branchA->id,
            'gender' => 'male',
            'birth_date' => '2006-02-02',
            'phone' => '01512345678',
            'status' => 'active',
        ]);
        StudentParent::create([
            'user_id' => $this->parentUserB->id,
            'student_id' => $this->studentB->id,
            'relation' => 'father',
            'is_primary' => true,
        ]);
        Enrollment::create([
            'student_id' => $this->studentB->id,
            'group_id' => $this->groupA->id,
            'tenant_id' => $this->branchA->id,
            'enrollment_code' => 'ENR-LIVE-002',
            'enrollment_date' => now(),
            'status' => 'active',
            'vault_id' => $this->vaultCashA->id,
            'total_amount' => 1200,
            'paid_amount' => 600,
        ]);

        // Student C (in both Group A and Group B)
        $this->studentUserC = User::create([
            'name' => 'الطالبة نور إبراهيم كمال',
            'email' => 'student.live.c@aischool.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'phone' => '01098765432',
            'status' => 'active',
        ]);
        $this->studentUserC->assignRole('student');
        $this->studentC = Student::create([
            'user_id' => $this->studentUserC->id,
            'student_code' => 'STU-LIVE-003',
            'national_id' => '30303031234567',
            'tenant_id' => $this->branchA->id,
            'gender' => 'female',
            'birth_date' => '2007-03-03',
            'phone' => '01098765432',
            'status' => 'active',
        ]);
        StudentParent::create([
            'user_id' => $this->parentUserB->id,
            'student_id' => $this->studentC->id,
            'relation' => 'father',
            'is_primary' => true,
        ]);
        Enrollment::create([
            'student_id' => $this->studentC->id,
            'group_id' => $this->groupA->id,
            'tenant_id' => $this->branchA->id,
            'enrollment_code' => 'ENR-LIVE-003-A',
            'enrollment_date' => now(),
            'status' => 'active',
            'vault_id' => $this->vaultCashA->id,
            'total_amount' => 1200,
            'paid_amount' => 1200,
        ]);
        Enrollment::create([
            'student_id' => $this->studentC->id,
            'group_id' => $this->groupB->id,
            'tenant_id' => $this->branchA->id,
            'enrollment_code' => 'ENR-LIVE-003-B',
            'enrollment_date' => now(),
            'status' => 'active',
            'vault_id' => $this->vaultCashA->id,
            'total_amount' => 1000,
            'paid_amount' => 1000,
        ]);
    }

    /**
     * TEST 01: Complete Student Registration & Family Account Linking
     */
    public function test_01_student_registration_and_enrollment_workflow(): void
    {
        $newParent = User::create([
            'name' => 'أحمد حسني',
            'email' => 'new.parent@school.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $newParent->assignRole('parent');

        $newStudentUser = User::create([
            'name' => 'عمر أحمد حسني',
            'email' => 'new.student@school.test',
            'password' => Hash::make('password'),
            'tenant_id' => $this->branchA->id,
            'status' => 'active',
        ]);
        $newStudentUser->assignRole('student');

        $newStudent = Student::create([
            'user_id' => $newStudentUser->id,
            'student_code' => 'STU-GEN-9999',
            'national_id' => '31005151234567',
            'tenant_id' => $this->branchA->id,
            'gender' => 'male',
            'birth_date' => '2010-05-15',
            'phone' => '01019998888',
            'status' => 'active',
        ]);

        StudentParent::create([
            'user_id' => $newParent->id,
            'student_id' => $newStudent->id,
            'relation' => 'father',
            'is_primary' => true,
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $newStudent->id,
            'group_id' => $this->groupA->id,
            'tenant_id' => $this->branchA->id,
            'enrollment_code' => 'ENR-NEW-9999',
            'enrollment_date' => now(),
            'status' => 'active',
            'vault_id' => $this->vaultCashA->id,
            'total_amount' => 1200,
            'paid_amount' => 1200,
        ]);

        $this->assertDatabaseHas('students', ['student_code' => 'STU-GEN-9999']);
        $this->assertDatabaseHas('student_parents', ['user_id' => $newParent->id, 'student_id' => $newStudent->id]);
        $this->assertDatabaseHas('enrollments', ['enrollment_code' => 'ENR-NEW-9999', 'status' => 'active']);

        // Verify parent link
        $parentStudents = $newParent->students()->get();
        $this->assertTrue($parentStudents->contains($newStudent));

        // Verify student course enrollment
        $enrolledGroups = $newStudent->enrollments()->pluck('group_id')->toArray();
        $this->assertContains($this->groupA->id, $enrolledGroups);
    }

    /**
     * TEST 02: Instructor Group Isolation & Rosters
     */
    public function test_02_instructor_and_group_management(): void
    {
        $this->actingAs($this->instructorUserA);

        // Instructor A teaches Group A
        $instructorGroups = Group::where('instructor_id', $this->instructorA->id)->pluck('id')->toArray();
        $this->assertContains($this->groupA->id, $instructorGroups);
        $this->assertNotContains($this->groupB->id, $instructorGroups);

        // Group A student roster includes Student A, B, C
        $rosterIds = Enrollment::where('group_id', $this->groupA->id)->where('status', 'active')->pluck('student_id')->toArray();
        $this->assertContains($this->studentA->id, $rosterIds);
        $this->assertContains($this->studentB->id, $rosterIds);
        $this->assertContains($this->studentC->id, $rosterIds);
    }

    /**
     * TEST 03: Sessions and Attendance Recording (Not Auto-Absent)
     */
    public function test_03_sessions_and_attendance_lifecycle(): void
    {
        $session = AttendanceSession::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $this->coursePython->id,
            'group_id' => $this->groupA->id,
            'recorded_by' => $this->instructorUserA->id,
            'attendance_date' => now()->toDateString(),
        ]);

        // Student must NOT be marked absent before attendance is recorded
        $recordsBefore = AttendanceRecord::where('attendance_session_id', $session->id)->count();
        $this->assertEquals(0, $recordsBefore);

        // Record attendance
        AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $this->studentA->id,
            'status' => 'present',
        ]);
        AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $this->studentB->id,
            'status' => 'absent',
        ]);
        AttendanceRecord::create([
            'attendance_session_id' => $session->id,
            'student_id' => $this->studentC->id,
            'status' => 'late',
        ]);

        $this->assertDatabaseHas('attendance_records', ['student_id' => $this->studentA->id, 'status' => 'present']);
        $this->assertDatabaseHas('attendance_records', ['student_id' => $this->studentB->id, 'status' => 'absent']);
        $this->assertDatabaseHas('attendance_records', ['student_id' => $this->studentC->id, 'status' => 'late']);
    }

    /**
     * TEST 04: Homework Workflow (On-time, Missing, Late, 4/10 alert, 5/10 no alert, 0/10 alert)
     */
    public function test_04_homework_complete_workflow(): void
    {
        $assignment = Assignment::create([
            'tenant_id' => $this->branchA->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructorA->id,
            'title' => 'Python Task 1',
            'total_score' => 10,
            'due_date' => now()->addDays(2),
            'is_published' => true,
        ]);

        // Scenario B: Student A submits on time
        $subA = AssignmentSubmission::create([
            'tenant_id' => $this->branchA->id,
            'assignment_id' => $assignment->id,
            'student_id' => $this->studentA->id,
            'submitted_at' => now(),
        ]);
        $this->assertFalse($subA->isLate());

        // Scenario D: Student B submits late after deadline
        $assignmentOverdue = Assignment::create([
            'tenant_id' => $this->branchA->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructorA->id,
            'title' => 'Python Task 2 Overdue',
            'total_score' => 10,
            'due_date' => now()->subDay(),
            'is_published' => true,
        ]);

        $subB = AssignmentSubmission::create([
            'tenant_id' => $this->branchA->id,
            'assignment_id' => $assignmentOverdue->id,
            'student_id' => $this->studentB->id,
            'submitted_at' => now(),
        ]);
        $this->assertTrue($subB->isLate());

        // Scenario E: Grade 4/10 -> Alert
        $subA->update(['score' => 4, 'published_at' => now()]);
        $service = app(AcademicFollowUpService::class);
        $roster = $service->getHomeworkEligibleRoster($assignment);
        $rowA = $roster->firstWhere('student_id', $this->studentA->id);
        $this->assertTrue($rowA['is_low_grade']);

        // Scenario F: Grade 5/10 (Exactly 50%) -> No Alert
        $subA->update(['score' => 5, 'published_at' => now()]);
        $roster50 = $service->getHomeworkEligibleRoster($assignment);
        $rowA50 = $roster50->firstWhere('student_id', $this->studentA->id);
        $this->assertFalse($rowA50['is_low_grade']);

        // Scenario G: Grade 0/10 -> Alert
        $subA->update(['score' => 0, 'published_at' => now()]);
        $roster0 = $service->getHomeworkEligibleRoster($assignment);
        $rowA0 = $roster0->firstWhere('student_id', $this->studentA->id);
        $this->assertTrue($rowA0['is_low_grade']);
        $this->assertEquals('0 / 10', $rowA0['score_display']);
    }

    /**
     * TEST 05: Exam Workflow (9/20 alert, 10/20 no alert, 14/30 alert, 15/30 no alert)
     */
    public function test_05_complete_exam_workflow(): void
    {
        $exam20 = Exam::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $this->coursePython->id,
            'group_id' => $this->groupA->id,
            'title' => 'Python Midterm Exam',
            'exam_date' => now()->toDateString(),
            'total_marks' => 20,
            'passing_marks' => 10,
            'total_score' => 20,
            'is_published' => true,
        ]);

        // Student A: 9/20 -> Alert (9 * 2 = 18 < 20)
        ExamAttempt::create([
            'tenant_id' => $this->branchA->id,
            'exam_id' => $exam20->id,
            'student_id' => $this->studentA->id,
            'score' => 9,
            'total_score' => 20,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Student B: 10/20 -> No alert (10 * 2 = 20 not < 20)
        ExamAttempt::create([
            'tenant_id' => $this->branchA->id,
            'exam_id' => $exam20->id,
            'student_id' => $this->studentB->id,
            'score' => 10,
            'total_score' => 20,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $service = app(AcademicFollowUpService::class);
        $roster20 = $service->getExamEligibleRoster($exam20);
        $this->assertTrue($roster20->firstWhere('student_id', $this->studentA->id)['is_low_grade']);
        $this->assertFalse($roster20->firstWhere('student_id', $this->studentB->id)['is_low_grade']);

        $exam30 = Exam::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $this->coursePython->id,
            'group_id' => $this->groupA->id,
            'title' => 'Python Final Exam',
            'exam_date' => now()->toDateString(),
            'total_marks' => 30,
            'passing_marks' => 15,
            'total_score' => 30,
            'is_published' => true,
        ]);

        // 14/30 -> Alert (14 * 2 = 28 < 30)
        ExamAttempt::create([
            'tenant_id' => $this->branchA->id,
            'exam_id' => $exam30->id,
            'student_id' => $this->studentA->id,
            'score' => 14,
            'total_score' => 30,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // 15/30 -> No Alert (15 * 2 = 30 not < 30)
        ExamAttempt::create([
            'tenant_id' => $this->branchA->id,
            'exam_id' => $exam30->id,
            'student_id' => $this->studentB->id,
            'score' => 15,
            'total_score' => 30,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $roster30 = $service->getExamEligibleRoster($exam30);
        $this->assertTrue($roster30->firstWhere('student_id', $this->studentA->id)['is_low_grade']);
        $this->assertFalse($roster30->firstWhere('student_id', $this->studentB->id)['is_low_grade']);
    }

    /**
     * TEST 06: Secretary Follow-Up Center (Create task "جديدة", Unanswered call, Contacted, Reassignment)
     */
    public function test_06_secretary_daily_follow_up_center(): void
    {
        $this->actingAs($this->secretaryA);

        $component = Livewire::test(AcademicFollowUpCenter::class);

        // Open Follow-Up Modal for Student A
        $component->call('openFollowUpModal', $this->studentA->id, 'academic_low_exam', null, 'درجة منخفضة في الامتحان', 'سبب المتابعة', $this->groupA->id);
        $this->assertEquals($this->studentA->id, $component->get('activeStudentId'));

        // Save Follow-Up Task
        $component->call('saveFollowUpTask');

        $task = StudentFollowUpTask::where('student_id', $this->studentA->id)->latest()->first();
        $this->assertNotNull($task);
        $this->assertEquals(StudentFollowUpTask::STATUS_NEW, $task->status);
        $this->assertEquals($this->secretaryA->id, $task->assigned_to);

        // Record unanswered communication ("لم يرد") -> Status remains "جديدة"
        $component->call('openContactLogModal', $task->id);
        $component->set('contactMethod', 'phone_call');
        $component->set('contactResult', 'no_answer');
        $component->set('contactNotes', 'لم يتم الرد على الهاتف');
        $component->call('saveContactLog');

        $task->refresh();
        $this->assertEquals(StudentFollowUpTask::STATUS_NEW, $task->status);
        $this->assertDatabaseHas('student_follow_up_contacts', [
            'task_id' => $task->id,
            'contact_result' => 'no_answer',
            'recorded_by' => $this->secretaryA->id,
        ]);

        // Backdate first contact by 1 minute to prevent double-submission throttle in unit test
        StudentFollowUpContact::where('task_id', $task->id)->update(['contacted_at' => now()->subMinutes(1)]);

        // Record successful communication ("تم التواصل") -> Status becomes "تم التواصل"
        $component->call('openContactLogModal', $task->id);
        $component->set('contactMethod', 'phone_call');
        $component->set('contactResult', 'contacted');
        $component->set('contactNotes', 'تم التواصل بنجاح مع ولي الأمر');
        $component->call('saveContactLog');

        $task->refresh();
        $this->assertEquals(StudentFollowUpTask::STATUS_CONTACTED, $task->status);

        // Reassign Task to Supervisor
        $component->call('openReassignModal', $task->id);
        $component->set('reassignToUserId', $this->supervisorA->id);
        $component->set('reassignReason', 'متابعة من قبل المشرف الأكاديمي');
        $component->call('saveReassignment');

        $task->refresh();
        $this->assertEquals($this->supervisorA->id, $task->assigned_to);
        $this->assertDatabaseHas('student_follow_up_task_reassignments', [
            'task_id' => $task->id,
            'previous_assigned_to' => $this->secretaryA->id,
            'new_assigned_to' => $this->supervisorA->id,
        ]);
    }

    /**
     * TEST 07: Critical Same-Branch Group Authorization Security
     */
    public function test_07_critical_group_authorization_security(): void
    {
        // Secretary A is assigned Group A only.
        // Group B student is Student C's second enrollment or a separate Group B student.
        $studentGroupB = Student::create([
            'user_id' => User::factory()->create(['tenant_id' => $this->branchA->id])->id,
            'student_code' => 'STU-GRP-B-99',
            'national_id' => '30808081234567',
            'tenant_id' => $this->branchA->id,
            'gender' => 'male',
            'birth_date' => '2008-08-08',
            'phone' => '01055554444',
            'status' => 'active',
        ]);
        Enrollment::create([
            'student_id' => $studentGroupB->id,
            'group_id' => $this->groupB->id,
            'tenant_id' => $this->branchA->id,
            'enrollment_code' => 'ENR-GRP-B-99',
            'enrollment_date' => now(),
            'status' => 'active',
            'vault_id' => $this->vaultCashA->id,
            'total_amount' => 1000,
            'paid_amount' => 1000,
        ]);

        $taskGroupB = StudentFollowUpTask::create([
            'tenant_id' => $this->branchA->id,
            'student_id' => $studentGroupB->id,
            'group_id' => $this->groupB->id,
            'assigned_to' => $this->secretaryB->id,
            'title' => 'متابعة درجة امتحان منخفضة',
            'trigger_type' => 'academic_low_exam',
            'status' => StudentFollowUpTask::STATUS_NEW,
        ]);

        $this->actingAs($this->secretaryA);

        $component = Livewire::test(AcademicFollowUpCenter::class);

        // Direct method call on Group B student profile -> Access Denied (Modal closed & profile null)
        $component->call('openStudentProfile', $studentGroupB->id);
        $component->assertSet('showStudentProfileModal', false);
        $component->assertSet('activeStudentProfile', null);

        // Direct method call on Group B task -> Access Denied (Modal closed)
        $component->call('openContactLogModal', $taskGroupB->id);
        $component->assertSet('showContactLogModal', false);

        // Direct method call on Group B task history -> Access Denied (Modal closed & empty contacts)
        $component->call('openHistoryModal', $taskGroupB->id);
        $component->assertSet('showHistoryModal', false);
        $component->assertSet('timelineContacts', []);

        // Direct method call on Group B task reassignment -> Access Denied (Modal closed)
        $component->call('openReassignModal', $taskGroupB->id);
        $component->assertSet('showReassignModal', false);

        // Direct unauthorized mutation on Group B task -> 403 Forbidden
        $component->set('activeTaskId', $taskGroupB->id);
        $component->set('contactMethod', 'phone_call');
        $component->set('contactResult', 'contacted');
        $component->call('saveContactLog');
        $component->assertForbidden();
    }

    /**
     * TEST 08: Secretary with Combined Educational Supervision Role
     */
    public function test_08_secretary_with_educational_supervision(): void
    {
        $this->actingAs($this->secretarySuper);

        $service = app(AcademicFollowUpService::class);

        // Can view Group A
        $this->assertTrue($service->isGroupAuthorized($this->secretarySuper, $this->groupA->id));

        // CANNOT view Group B
        $this->assertFalse($service->isGroupAuthorized($this->secretarySuper, $this->groupB->id));
    }

    /**
     * TEST 09: Student with Multiple Courses Context Scoping
     */
    public function test_09_student_with_multiple_courses(): void
    {
        $service = app(AcademicFollowUpService::class);

        // Student C is in Group A (Python) and Group B (Scratch).
        // Secretary A is only assigned Group A.
        $profile = $service->getStudentProfile($this->studentC, $this->secretaryA);

        $courses = collect($profile['courses'])->pluck('course_name')->toArray();
        $this->assertContains('Python Programming', $courses);
        $this->assertNotContains('Scratch Programming', $courses);
    }

    /**
     * TEST 10: Parent with Multiple Children Portal Switching
     */
    public function test_10_parent_with_multiple_children(): void
    {
        $this->actingAs($this->parentUserB);

        $children = $this->parentUserB->students()->get();
        $this->assertEquals(2, $children->count());
        $this->assertTrue($children->contains($this->studentB));
        $this->assertTrue($children->contains($this->studentC));
        $this->assertFalse($children->contains($this->studentA));
    }

    /**
     * TEST 11: Installments and Payment Collection
     */
    public function test_11_installments_and_payment_collection(): void
    {
        $this->actingAs($this->accountantA);

        $enrollment = Enrollment::where('student_id', $this->studentB->id)->first();
        $initialBalance = $this->vaultCashA->getCurrentBalance();

        $installment = Installment::create([
            'tenant_id' => $this->branchA->id,
            'student_id' => $this->studentB->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 1,
            'amount' => 300,
            'due_date' => now()->addMonth(),
            'status' => 'pending',
            'paid_amount' => 0,
        ]);

        // Collect payment into cash vault
        Payment::create([
            'tenant_id' => $this->branchA->id,
            'student_id' => $this->studentB->id,
            'enrollment_id' => $enrollment->id,
            'vault_id' => $this->vaultCashA->id,
            'amount' => 300,
            'payment_date' => now(),
            'payment_method' => 'cash',
        ]);
        $installment->update(['paid_amount' => 300, 'status' => 'paid']);

        $this->vaultCashA->refresh();
        $this->assertEquals($initialBalance + 300, $this->vaultCashA->getCurrentBalance());
        $this->assertEquals('paid', $installment->status);
    }

    /**
     * TEST 12: Enrollment Cancellation and Refunds
     */
    public function test_12_enrollment_cancellation_and_refunds(): void
    {
        $enrollment = Enrollment::where('student_id', $this->studentA->id)->first();
        $this->assertEquals('active', $enrollment->status);

        $enrollment->update([
            'status' => 'cancelled',
            'cancellation_reason' => 'ظروف عائلية',
            'cancelled_at' => now(),
            'cancelled_by' => $this->superAdmin->id,
        ]);

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'status' => 'cancelled',
        ]);
    }

    /**
     * TEST 13: Instructor Salaries and Session Accounting
     */
    public function test_13_instructor_salaries_and_session_accounting(): void
    {
        $this->vaultCashA->update(['opening_balance' => 15000]);

        $salary = SalaryPayment::create([
            'tenant_id' => $this->branchA->id,
            'instructor_id' => $this->instructorA->id,
            'employee_type' => 'instructor',
            'amount' => 2500,
            'payment_date' => now(),
            'vault_id' => $this->vaultCashA->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('salary_payments', [
            'id' => $salary->id,
            'instructor_id' => $this->instructorA->id,
            'amount' => 2500,
            'status' => 'paid',
        ]);
    }

    /**
     * TEST 14: Vault and Financial Integrity (Opening 10,000 + 1,000 - 300 - 2,000 = 8,700)
     */
    public function test_14_vault_and_financial_integrity(): void
    {
        $vault = Vault::create([
            'tenant_id' => $this->branchA->id,
            'name' => 'خزينة التدقيق المحاسبي',
            'type' => 'cash',
            'opening_balance' => 10000,
            'is_active' => true,
        ]);

        $revCategory = RevenueCategory::create([
            'tenant_id' => $this->branchA->id,
            'name' => 'رسوم دراسية',
            'code' => 'CAT-REV-01',
        ]);

        // Tuition inflow: 1000
        Revenue::create([
            'tenant_id' => $this->branchA->id,
            'vault_id' => $vault->id,
            'revenue_category_id' => $revCategory->id,
            'amount' => 1000,
            'revenue_date' => now()->toDateString(),
            'title' => 'تحصيل رسوم دراسية تجريبية',
            'created_by' => $this->superAdmin->id,
        ]);

        // Expense outflow: 300
        $category = ExpenseCategory::create([
            'tenant_id' => $this->branchA->id,
            'name' => 'أدوات مكتبية',
            'code' => 'CAT-EXP-01',
        ]);
        Expense::create([
            'tenant_id' => $this->branchA->id,
            'vault_id' => $vault->id,
            'expense_category_id' => $category->id,
            'amount' => 300,
            'expense_date' => now()->toDateString(),
            'title' => 'شراء أدوات مكتبية',
            'status' => 'paid',
            'created_by' => $this->superAdmin->id,
        ]);

        // Transfer out: 2000
        VaultTransfer::create([
            'tenant_id' => $this->branchA->id,
            'from_vault_id' => $vault->id,
            'to_vault_id' => $this->vaultVodafoneA->id,
            'amount' => 2000,
            'transfer_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $vault->refresh();
        $this->assertEquals(8700, $vault->getCurrentBalance());
    }

    /**
     * TEST 15: Daily Reports and Dashboard Counters
     */
    public function test_15_daily_reports_and_counters(): void
    {
        $service = app(AcademicFollowUpService::class);
        $data = $service->getDailyReportData($this->secretaryA, now(), $this->branchA->id, $this->groupA->id);

        $this->assertArrayHasKey('created_today_count', $data);
        $this->assertArrayHasKey('new_cases_count', $data);
        $this->assertArrayHasKey('contacted_cases_count', $data);
        $this->assertArrayHasKey('low_exam_count', $data);
        $this->assertArrayHasKey('low_homework_count', $data);
    }

    /**
     * TEST 16: Mobile and UI Panel Accessibility
     */
    public function test_16_mobile_and_ui_panel_accessibility(): void
    {
        // Super Admin access admin dashboard -> HTTP 200
        $this->actingAs($this->superAdmin);
        $resAdmin = $this->get('/admin');
        $this->assertTrue($resAdmin->isSuccessful());

        // Supervisor widget execution via Livewire -> Successful
        Livewire::actingAs($this->supervisorA)
            ->test(\App\Filament\Supervisor\Widgets\SupervisorStatsWidget::class)
            ->assertSuccessful();

        // Parent panel widget execution via Livewire -> Successful
        Livewire::actingAs($this->parentUserA)
            ->test(\App\Filament\Parent\Widgets\ChildrenSelectorWidget::class)
            ->assertSuccessful();

        // Student access student panel -> HTTP 200 or redirect to dashboard
        $this->actingAs($this->studentUserA);
        $resStudent = $this->get('/student/dashboard');
        $this->assertTrue($resStudent->isSuccessful() || $resStudent->isRedirect());
    }

    /**
     * TEST 17: Real Performance Benchmark
     */
    public function test_17_real_performance_benchmark(): void
    {
        $start = microtime(true);
        DB::enableQueryLog();

        $service = app(AcademicFollowUpService::class);
        $report = $service->getDailyReportData($this->secretaryA, now(), $this->branchA->id, $this->groupA->id);

        $durationMs = (microtime(true) - $start) * 1000;
        $queryCount = count(DB::getQueryLog());

        $this->assertLessThan(500, $durationMs, "Daily report query took {$durationMs}ms which exceeds 500ms");
        $this->assertLessThan(30, $queryCount, "Query count {$queryCount} exceeds 30");
        $this->assertIsArray($report);
    }
}

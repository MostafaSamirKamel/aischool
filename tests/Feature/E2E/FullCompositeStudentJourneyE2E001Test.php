<?php

namespace Tests\Feature\E2E;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCancellation;
use App\Models\EnrollmentCourse;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\Group;
use App\Models\Installment;
use App\Models\Instructor;
use App\Models\InstructorPayment;
use App\Models\Payment;
use App\Models\SessionSchedule;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use App\Services\Enrollment\EnrollmentCancellationService;
use App\Services\Enrollment\StudentGroupTransferService;
use App\Services\HomeworkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FullCompositeStudentJourneyE2E001Test extends TestCase
{
    use RefreshDatabase;

    protected Tenant $branch;
    protected User $superAdmin;
    protected User $registrationStaff;
    protected User $accountant;
    protected User $instructorUserA;
    protected Instructor $instructorA;
    protected User $instructorUserB;
    protected Instructor $instructorB;
    protected Classroom $classroomA;
    protected Classroom $classroomB;
    protected Course $coursePython;
    protected Group $groupA;
    protected Group $groupB;
    protected Vault $cashVault;
    protected Vault $vodafoneVault;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        // Setup Roles and Permissions
        $roles = ['super_admin', 'branch_manager', 'secretary', 'instructor', 'student', 'parent', 'accountant'];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        $permissions = [
            'transfer_student_group',
            'cancel_subscription',
            'settle_subscription',
            'execute_refunds',
            'manage_enrollments',
            'collect_payments',
            'update_enrollments',
            'view_enrollments',
        ];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Branch Setup
        $this->branch = Tenant::create([
            'name' => 'Main Nasr City Branch',
            'domain' => 'nasrcity.aischool.test',
        ]);

        // Super Admin
        $this->superAdmin = User::factory()->create([
            'name' => 'System Super Admin',
            'email' => 'superadmin@aischool.test',
            'tenant_id' => null,
            'is_super_admin' => true,
            'status' => 'active',
        ]);
        $this->superAdmin->assignRole('super_admin');

        // Registration Staff
        $this->registrationStaff = User::factory()->create([
            'name' => 'Sarah Registration Officer',
            'email' => 'sarah.reg@aischool.test',
            'tenant_id' => $this->branch->id,
            'status' => 'active',
        ]);
        $this->registrationStaff->assignRole('secretary');
        $this->registrationStaff->givePermissionTo([
            'manage_enrollments',
            'transfer_student_group',
            'cancel_subscription',
            'collect_payments',
            'update_enrollments',
            'view_enrollments',
        ]);

        // Accountant
        $this->accountant = User::factory()->create([
            'name' => 'Khaled Financial Auditor',
            'email' => 'khaled.acc@aischool.test',
            'tenant_id' => $this->branch->id,
            'status' => 'active',
        ]);
        $this->accountant->assignRole('accountant');
        $this->accountant->givePermissionTo([
            'settle_subscription',
            'execute_refunds',
            'collect_payments',
        ]);

        // Instructors
        $this->instructorUserA = User::factory()->create([
            'name' => 'Eng. Amr Tarek (Python Lead)',
            'email' => 'amr.instructor@aischool.test',
            'tenant_id' => $this->branch->id,
            'status' => 'active',
        ]);
        $this->instructorUserA->assignRole('instructor');
        $this->instructorA = Instructor::create([
            'tenant_id' => $this->branch->id,
            'user_id' => $this->instructorUserA->id,
            'employee_code' => 'INS-NC-001',
            'specialization' => 'Python & Artificial Intelligence',
            'hourly_rate' => 150.00,
            'status' => 'active',
        ]);

        $this->instructorUserB = User::factory()->create([
            'name' => 'Eng. Mona Salem (Python Senior)',
            'email' => 'mona.instructor@aischool.test',
            'tenant_id' => $this->branch->id,
            'status' => 'active',
        ]);
        $this->instructorUserB->assignRole('instructor');
        $this->instructorB = Instructor::create([
            'tenant_id' => $this->branch->id,
            'user_id' => $this->instructorUserB->id,
            'employee_code' => 'INS-NC-002',
            'specialization' => 'Python & Data Science',
            'hourly_rate' => 150.00,
            'status' => 'active',
        ]);

        // Classrooms
        $this->classroomA = Classroom::create([
            'tenant_id' => $this->branch->id,
            'name' => 'Lab 1 - Turing Room',
            'capacity' => 15,
            'status' => 'active',
        ]);

        $this->classroomB = Classroom::create([
            'tenant_id' => $this->branch->id,
            'name' => 'Lab 2 - Lovelace Room',
            'capacity' => 15,
            'status' => 'active',
        ]);

        // Course
        $this->coursePython = Course::create([
            'tenant_id' => $this->branch->id,
            'name' => 'Python Fundamentals',
            'code' => 'CRS-PY-2026',
            'price' => 2000.00,
            'total_sessions' => 12,
            'status' => 'active',
        ]);

        // Groups
        $this->groupA = Group::create([
            'tenant_id' => $this->branch->id,
            'course_id' => $this->coursePython->id,
            'instructor_id' => $this->instructorA->id,
            'classroom_id' => $this->classroomA->id,
            'name' => 'Python Group A (Sun/Tue)',
            'total_sessions' => 12,
            'max_students' => 10,
            'capacity' => 0,
            'status' => 'active',
            'start_date' => now()->subWeeks(3),
            'end_date' => now()->addWeeks(6),
        ]);

        $this->groupB = Group::create([
            'tenant_id' => $this->branch->id,
            'course_id' => $this->coursePython->id,
            'instructor_id' => $this->instructorB->id,
            'classroom_id' => $this->classroomB->id,
            'name' => 'Python Group B (Mon/Wed)',
            'total_sessions' => 12,
            'max_students' => 10,
            'capacity' => 0,
            'status' => 'active',
            'start_date' => now()->subWeeks(2),
            'end_date' => now()->addWeeks(7),
        ]);

        // Vaults
        $this->cashVault = Vault::create([
            'tenant_id' => $this->branch->id,
            'name' => 'Branch Cash Vault',
            'type' => 'cash',
            'opening_balance' => 10000.00,
            'is_active' => true,
        ]);

        $this->vodafoneVault = Vault::create([
            'tenant_id' => $this->branch->id,
            'name' => 'Vodafone Cash Safe',
            'type' => 'vodafone',
            'opening_balance' => 5000.00,
            'is_active' => true,
        ]);
    }

    /**
     * Complete Student Journey E2E-001 (Stages A through H)
     */
    public function test_e2e_001_full_composite_student_journey(): void
    {
        // =========================================================================
        // STAGE A — Registration
        // =========================================================================
        $studentUser = User::create([
            'tenant_id' => $this->branch->id,
            'name' => 'Ahmed Mohamed',
            'email' => 'ahmed.mohamed@aischool.test',
            'password' => bcrypt('Secret123!'),
            'status' => 'active',
        ]);
        $studentUser->assignRole('student');

        $student = Student::create([
            'tenant_id' => $this->branch->id,
            'user_id' => $studentUser->id,
            'student_code' => 'STU-2026-001',
            'gender' => 'male',
            'birth_date' => '2012-05-10',
            'phone' => '01012345678',
            'status' => 'active',
        ]);

        $parentUser = User::create([
            'tenant_id' => $this->branch->id,
            'name' => 'Mohamed Hassan (Father)',
            'email' => 'parent.mohamed@aischool.test',
            'password' => bcrypt('Secret123!'),
            'status' => 'active',
        ]);
        $parentUser->assignRole('parent');

        StudentParent::create([
            'tenant_id' => $this->branch->id,
            'user_id' => $parentUser->id,
            'student_id' => $student->id,
            'relation' => 'father',
            'is_primary' => true,
        ]);

        // Book a Python trial session
        $trialSchedule = SessionSchedule::create([
            'tenant_id' => $this->branch->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructorA->id,
            'course_id' => $this->coursePython->id,
            'classroom_id' => $this->classroomA->id,
            'title' => 'Python Trial Workshop',
            'date' => now()->subDays(20)->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:00:00',
            'status' => 'completed',
        ]);

        // Attend trial
        $trialSession = AttendanceSession::create([
            'tenant_id' => $this->branch->id,
            'group_id' => $this->groupA->id,
            'course_id' => $this->coursePython->id,
            'attendance_date' => now()->subDays(20)->toDateString(),
            'notes' => 'Python Trial Attended',
        ]);

        AttendanceRecord::create([
            'attendance_session_id' => $trialSession->id,
            'student_id' => $student->id,
            'status' => 'present',
            'notes' => 'Trial attended successfully',
        ]);

        // Enroll in Python Fundamentals (Group A, 2,000 EGP)
        $enrollment = Enrollment::create([
            'tenant_id' => $this->branch->id,
            'student_id' => $student->id,
            'group_id' => $this->groupA->id,
            'vault_id' => null,
            'total_amount' => 2000.00,
            'paid_amount' => 0.00,
            'discount' => 0.00,
            'payment_plan' => 'installments',
            'installments_count' => 3,
            'status' => 'active',
            'enrollment_code' => 'ENR-PY-001',
            'enrollment_date' => now()->subDays(18)->toDateString(),
        ]);

        $enrollmentCourse = EnrollmentCourse::create([
            'tenant_id' => $this->branch->id,
            'enrollment_id' => $enrollment->id,
            'course_id' => $this->coursePython->id,
            'group_id' => $this->groupA->id,
            'status' => 'active',
            'course_price' => 2000.00,
            'total_sessions' => 12,
            'current_session' => 0,
        ]);

        $enrollment->groups()->syncWithoutDetaching([$this->groupA->id => ['enrolled_at' => now()]]);
        $this->groupA->syncCapacity();

        // 3 Installments: 500, 750, 750
        $inst1 = Installment::create([
            'tenant_id' => $this->branch->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 1,
            'amount' => 500.00,
            'paid_amount' => 0.00,
            'due_date' => now()->subDays(15)->toDateString(),
            'status' => 'pending',
        ]);

        $inst2 = Installment::create([
            'tenant_id' => $this->branch->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 2,
            'amount' => 750.00,
            'paid_amount' => 0.00,
            'due_date' => now()->addDays(15)->toDateString(),
            'status' => 'pending',
        ]);

        $inst3 = Installment::create([
            'tenant_id' => $this->branch->id,
            'enrollment_id' => $enrollment->id,
            'installment_number' => 3,
            'amount' => 750.00,
            'paid_amount' => 0.00,
            'due_date' => now()->addDays(45)->toDateString(),
            'status' => 'pending',
        ]);

        expect($enrollment->getRemainingBalance())->toBe(2000.0);
        expect($this->groupA->activeStudents()->count())->toBe(1);

        // =========================================================================
        // STAGE B — Partial Payment (500 EGP into Cash Vault)
        // =========================================================================
        $cashVaultInitial = $this->cashVault->fresh()->getCurrentBalance();

        $payment1 = Payment::create([
            'tenant_id' => $this->branch->id,
            'enrollment_id' => $enrollment->id,
            'installment_id' => $inst1->id,
            'vault_id' => $this->cashVault->id,
            'payment_code' => Payment::generatePaymentCode(),
            'amount' => 500.00,
            'payment_method' => 'cash',
            'payment_date' => now()->subDays(15)->toDateString(),
            'status' => 'completed',
            'received_by' => $this->registrationStaff->id,
        ]);

        $inst1->update([
            'paid_amount' => 500.00,
            'status' => 'paid',
            'paid_at' => now()->subDays(15),
        ]);

        $enrollment->update([
            'paid_amount' => 500.00,
        ]);

        expect($enrollment->fresh()->getRemainingBalance())->toBe(1500.0);
        expect((float) $enrollment->fresh()->paid_amount)->toBe(500.0);
        expect($this->cashVault->fresh()->getCurrentBalance())->toBe($cashVaultInitial + 500.0);

        // =========================================================================
        // STAGE C — Learning (4 sessions in Group A, 2 homeworks, 1 exam)
        // =========================================================================
        $groupASessions = [];
        for ($i = 1; $i <= 4; $i++) {
            $sess = AttendanceSession::create([
                'tenant_id' => $this->branch->id,
                'group_id' => $this->groupA->id,
                'course_id' => $this->coursePython->id,
                'attendance_date' => now()->subDays(14 - $i * 2)->toDateString(),
                'notes' => "Group A Session $i",
            ]);

            AttendanceRecord::create([
                'attendance_session_id' => $sess->id,
                'student_id' => $student->id,
                'status' => 'present',
                'notes' => 'Attended and actively participated',
            ]);

            $groupASessions[] = $sess;
        }

        $enrollmentCourse->update(['current_session' => 4]);

        // Publish 2 homework assignments
        $hw1 = Assignment::create([
            'tenant_id' => $this->branch->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructorUserA->id,
            'title' => 'Python Basics Assignment 1: Variables & Loops',
            'description' => 'Write a program calculating Fibonacci series',
            'due_date' => now()->subDays(10)->toDateString(),
            'max_score' => 100,
            'status' => 'active',
        ]);

        $hw2 = Assignment::create([
            'tenant_id' => $this->branch->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructorUserA->id,
            'title' => 'Python Basics Assignment 2: Functions & Dictionaries',
            'description' => 'Implement student grade directory',
            'due_date' => now()->subDays(7)->toDateString(),
            'max_score' => 100,
            'status' => 'active',
        ]);

        // Submit & grade homework 1
        $submission1 = AssignmentSubmission::create([
            'tenant_id' => $this->branch->id,
            'assignment_id' => $hw1->id,
            'student_id' => $student->id,
            'submission_notes' => 'def fib(n): return [0, 1]...',
            'submitted_at' => now()->subDays(11),
            'status' => 'submitted',
        ]);

        $submission1->update([
            'score' => 95,
            'instructor_feedback' => 'Excellent algorithm efficiency!',
            'status' => 'graded',
            'graded_at' => now()->subDays(10),
        ]);

        // Submit & grade homework 2
        $submission2 = AssignmentSubmission::create([
            'tenant_id' => $this->branch->id,
            'assignment_id' => $hw2->id,
            'student_id' => $student->id,
            'submission_notes' => 'students = {"Ahmed": 95}...',
            'submitted_at' => now()->subDays(8),
            'status' => 'submitted',
        ]);

        $submission2->update([
            'score' => 98,
            'instructor_feedback' => 'Clean pythonic code!',
            'status' => 'graded',
            'graded_at' => now()->subDays(7),
        ]);

        // Create an exam
        $exam = Exam::create([
            'tenant_id' => $this->branch->id,
            'course_id' => $this->coursePython->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructorA->id,
            'title' => 'Python Midterm Exam',
            'type' => 'midterm',
            'exam_date' => now()->subDays(6),
            'duration' => 60,
            'total_marks' => 100,
            'passing_marks' => 50,
            'status' => 'completed',
        ]);

        $attempt = ExamAttempt::create([
            'tenant_id' => $this->branch->id,
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'score' => 100,
            'total_score' => 100,
            'percentage' => 100.0,
            'passed' => true,
            'started_at' => now()->subDays(6),
            'completed_at' => now()->subDays(6)->addMinutes(45),
            'time_taken' => 2700,
        ]);

        // Verify Instructor A earned compensation (4 sessions * 150 EGP = 600 EGP)
        $instructorASessions = AttendanceSession::whereHas('group', fn($q) => $q->where('instructor_id', $this->instructorA->id))
            ->where('tenant_id', $this->branch->id)
            ->whereNull('salary_payment_id')
            ->count();
        expect($instructorASessions)->toBe(5); // 1 trial + 4 regular = 5 sessions

        // =========================================================================
        // STAGE D — Transfer to Group B
        // =========================================================================
        $transferService = app(StudentGroupTransferService::class);

        // Pre-transfer verification: Progress difference warning check
        $progressDiff = $transferService->getAcademicProgressDifference($enrollment, $this->groupB);
        expect($progressDiff)->toHaveKey('difference');

        // Execute transfer through authorized service
        $transfer = $transferService->transfer($enrollment, $this->groupB, [
            'transferred_by' => $this->registrationStaff,
            'reason' => 'Schedule adjustment requested by parent',
            'progress_warning_acknowledged' => true,
        ]);

        expect($transfer)->not->toBeNull();
        expect($enrollment->fresh()->group_id)->toBe($this->groupB->id);
        expect($this->groupA->activeStudents()->count())->toBe(0);
        expect($this->groupB->activeStudents()->count())->toBe(1);

        // Verify historical 4 attendance records in Group A are preserved unchanged
        $studentRecordsInGroupA = AttendanceRecord::where('student_id', $student->id)
            ->whereHas('session', fn($q) => $q->where('group_id', $this->groupA->id))
            ->count();
        expect($studentRecordsInGroupA)->toBe(5); // 1 trial + 4 regular

        // Verify homework & exams are preserved
        expect(AssignmentSubmission::where('student_id', $student->id)->count())->toBe(2);
        expect(ExamAttempt::where('student_id', $student->id)->count())->toBe(1);

        // Verify financial invariance
        expect((float) $enrollment->fresh()->total_amount)->toBe(2000.0);
        expect((float) $enrollment->fresh()->paid_amount)->toBe(500.0);
        expect($enrollment->fresh()->getRemainingBalance())->toBe(1500.0);
        expect($this->cashVault->fresh()->getCurrentBalance())->toBe($cashVaultInitial + 500.0);

        // =========================================================================
        // STAGE E — Continue Learning in Group B (2 sessions)
        // =========================================================================
        for ($j = 1; $j <= 2; $j++) {
            $sessB = AttendanceSession::create([
                'tenant_id' => $this->branch->id,
                'group_id' => $this->groupB->id,
                'course_id' => $this->coursePython->id,
                'attendance_date' => now()->subDays(4 - $j * 2)->toDateString(),
                'notes' => "Group B Session $j",
            ]);

            AttendanceRecord::create([
                'attendance_session_id' => $sessB->id,
                'student_id' => $student->id,
                'status' => 'present',
                'notes' => 'Attended in Group B',
            ]);
        }

        // Total attended sessions for Ahmed is now 6 (4 in A + 2 in B) + 1 trial = 7
        $totalAttended = AttendanceRecord::where('student_id', $student->id)
            ->where('status', 'present')
            ->count();
        expect($totalAttended)->toBe(7);

        // Instructor B has delivered 2 sessions
        $instructorBSessions = AttendanceSession::whereHas('group', fn($q) => $q->where('instructor_id', $this->instructorB->id))
            ->where('tenant_id', $this->branch->id)
            ->whereNull('salary_payment_id')
            ->count();
        expect($instructorBSessions)->toBe(2);

        // =========================================================================
        // STAGE F — Second Payment (300 EGP into Vodafone Cash)
        // =========================================================================
        $vodafoneVaultInitial = $this->vodafoneVault->fresh()->getCurrentBalance();

        $payment2 = Payment::create([
            'tenant_id' => $this->branch->id,
            'enrollment_id' => $enrollment->id,
            'installment_id' => $inst2->id,
            'vault_id' => $this->vodafoneVault->id,
            'payment_code' => Payment::generatePaymentCode(),
            'amount' => 300.00,
            'payment_method' => 'mobile_wallet',
            'payment_date' => now()->subDays(2)->toDateString(),
            'status' => 'completed',
            'received_by' => $this->accountant->id,
        ]);

        $inst2->update([
            'paid_amount' => 300.00,
            'status' => 'partial',
        ]);

        $enrollment->update([
            'paid_amount' => 800.00,
        ]);

        expect((float) $enrollment->fresh()->paid_amount)->toBe(800.0);
        expect($enrollment->fresh()->getRemainingBalance())->toBe(1200.0);
        expect($this->vodafoneVault->fresh()->getCurrentBalance())->toBe($vodafoneVaultInitial + 300.0);
        expect($this->cashVault->fresh()->getCurrentBalance())->toBe($cashVaultInitial + 500.0);

        // =========================================================================
        // STAGE G — Cancellation (Cancel with 200 EGP refund from Cash Vault)
        // =========================================================================
        $cancelService = app(EnrollmentCancellationService::class);

        $cancelData = [
            'reason' => 'Family relocated to another governorate',
            'refund_amount' => 200.00,
            'vault_id' => $this->cashVault->id,
            'payment_method' => 'cash',
            'notes' => '200 EGP refund approved and remaining 1200 EGP debt waived',
        ];

        // Step 48: Verify refund permission (registration staff lacks execute_refunds)
        expect(fn() => $cancelService->cancelSimplified($enrollment, $this->registrationStaff, $cancelData))
            ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);

        // Authorize cancellation with refund through officer possessing both permissions
        $this->accountant->givePermissionTo(['cancel_subscription', 'execute_refunds']);
        $cancellation = $cancelService->cancelSimplified(
            $enrollment,
            $this->accountant,
            $cancelData
        );

        expect($cancellation)->toBeInstanceOf(EnrollmentCancellation::class);
        expect($enrollment->fresh()->status)->toBe('cancelled');

        // Remaining 1,200 EGP debt is waived
        expect((float) $cancellation->waived_amount)->toBe(1200.0);

        // Cash Vault deducted by 200 EGP refund
        expect($this->cashVault->fresh()->getCurrentBalance())->toBe($cashVaultInitial + 500.0 - 200.0);

        // Vodafone vault remains unchanged
        expect($this->vodafoneVault->fresh()->getCurrentBalance())->toBe($vodafoneVaultInitial + 300.0);

        // Original 800 EGP collected payments remain intact in DB
        expect((float) Payment::where('enrollment_id', $enrollment->id)->where('status', 'completed')->sum('amount'))->toBe(800.0);

        // Student active membership in Group B is removed
        expect($this->groupB->activeStudents()->count())->toBe(0);

        // Historical 7 attendance records remain intact (1 trial + 4 Group A + 2 Group B)
        expect(AttendanceRecord::where('student_id', $student->id)->count())->toBe(7);

        // Grades and homework remain intact
        expect(AssignmentSubmission::where('student_id', $student->id)->count())->toBe(2);
        expect(ExamAttempt::where('student_id', $student->id)->count())->toBe(1);

        // Instructor salaries remain intact
        expect(AttendanceSession::whereHas('group', fn($q) => $q->where('instructor_id', $this->instructorA->id))->count())->toBe(5);
        expect(AttendanceSession::whereHas('group', fn($q) => $q->where('instructor_id', $this->instructorB->id))->count())->toBe(2);

        // =========================================================================
        // STAGE H — Security & Negative Invariants
        // =========================================================================

        // 1. Attempt transfer on cancelled enrollment -> REJECTED
        expect(fn() => $transferService->transfer($enrollment->fresh(), $this->groupA, ['transferred_by' => $this->registrationStaff]))
            ->toThrow(ValidationException::class);

        // 2. Attempt second cancellation on already cancelled enrollment -> REJECTED
        expect(fn() => $cancelService->cancelSimplified($enrollment->fresh(), $this->accountant, $cancelData))
            ->toThrow(ValidationException::class);

        // 3. Attempt refund exceeding total paid -> REJECTED
        $badRefundData = [
            'reason' => 'Fraudulent duplicate refund attempt',
            'refund_amount' => 9999.00,
            'vault_id' => $this->cashVault->id,
            'payment_method' => 'cash',
        ];
        expect(fn() => $cancelService->cancelSimplified($enrollment->fresh(), $this->accountant, $badRefundData))
            ->toThrow(ValidationException::class);

        // Final verification: No data corruption
        expect($enrollment->fresh()->status)->toBe('cancelled');
        expect(Student::find($student->id)->exists())->toBeTrue();
    }
}

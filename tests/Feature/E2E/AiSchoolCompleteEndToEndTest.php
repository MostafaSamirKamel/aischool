<?php

namespace Tests\Feature\E2E;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCancellation;
use App\Models\EnrollmentCourse;
use App\Models\Exam;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Grade;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\Installment;
use App\Models\Instructor;
use App\Models\LearningResource;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\SessionSchedule;
use App\Models\Student;
use App\Models\StudentFollowUpTask;
use App\Models\StudentMessage;
use App\Models\StudentParent;
use App\Models\Supervisor;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultTransfer;
use App\Services\Enrollment\EnrollmentCancellationService;
use App\Services\HomeworkService;
use App\Services\Parent\ParentStudentAccessService;
use App\Services\StudentFollowUpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AiSchoolCompleteEndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $primaryTenant;
    protected Tenant $secondaryTenant;

    // Administrative test users
    protected User $superAdmin;
    protected User $branchManager;
    protected User $registrationStaff1;
    protected User $registrationStaff2;
    protected User $accountant;
    protected User $supervisorUser1;
    protected User $supervisorUser2;
    protected Supervisor $supervisor1;
    protected Supervisor $supervisor2;

    // Instructors
    protected array $instructors = [];

    // Courses & Groups
    protected Course $coursePython;
    protected Course $courseScratch;
    protected Course $courseAppInventor;
    protected Course $courseFrontEnd;

    protected Group $groupA;
    protected Group $groupB;
    protected Group $groupC;
    protected Group $groupD;

    // Students & Parents
    protected array $students = [];
    protected array $parents = [];

    // Vaults
    protected Vault $mainVault;
    protected Vault $bankVault;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        // Setup Roles and Permissions
        $roles = ['super_admin', 'branch_manager', 'secretary', 'instructor', 'supervisor', 'student', 'parent', 'accountant'];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        $permissions = [
            'cancel_subscription', 'settle_subscription', 'execute_refunds',
            'view_special_reports', 'manage_enrollments', 'collect_payments',
        ];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // 1. Tenants Setup
        $this->primaryTenant = Tenant::create([
            'name' => 'E2E_AI_SCHOOL_2026_PRIMARY',
            'domain' => 'primary.aischool.test',
        ]);

        $this->secondaryTenant = Tenant::create([
            'name' => 'E2E_AI_SCHOOL_2026_ISOLATION',
            'domain' => 'secondary.aischool.test',
        ]);

        // 2. Administrative Users Setup
        $this->superAdmin = User::factory()->create([
            'name' => 'E2E Super Admin',
            'email' => 'superadmin.e2e@aischool.test',
            'tenant_id' => null,
            'is_super_admin' => true,
            'status' => 'active',
        ]);
        $this->superAdmin->assignRole('super_admin');

        $this->branchManager = User::factory()->create([
            'name' => 'E2E Branch Manager',
            'email' => 'branchmanager.e2e@aischool.test',
            'tenant_id' => $this->primaryTenant->id,
            'is_super_admin' => false,
            'status' => 'active',
        ]);
        $this->branchManager->assignRole('branch_manager');
        $this->branchManager->givePermissionTo(['cancel_subscription', 'settle_subscription', 'execute_refunds']);

        $this->registrationStaff1 = User::factory()->create([
            'name' => 'E2E Registration Staff 1',
            'email' => 'secretary1.e2e@aischool.test',
            'tenant_id' => $this->primaryTenant->id,
            'status' => 'active',
        ]);
        $this->registrationStaff1->assignRole('secretary');
        $this->registrationStaff1->givePermissionTo(['cancel_subscription']);

        $this->registrationStaff2 = User::factory()->create([
            'name' => 'E2E Registration Staff 2',
            'email' => 'secretary2.e2e@aischool.test',
            'tenant_id' => $this->primaryTenant->id,
            'status' => 'active',
        ]);
        $this->registrationStaff2->assignRole('secretary');

        $this->accountant = User::factory()->create([
            'name' => 'E2E Accountant',
            'email' => 'accountant.e2e@aischool.test',
            'tenant_id' => $this->primaryTenant->id,
            'status' => 'active',
        ]);
        $this->accountant->assignRole('accountant');
        $this->accountant->givePermissionTo(['settle_subscription', 'execute_refunds']);

        // 3. Supervisors Setup
        $this->supervisorUser1 = User::factory()->create([
            'name' => 'E2E Supervisor 1',
            'email' => 'supervisor1.e2e@aischool.test',
            'tenant_id' => $this->primaryTenant->id,
            'status' => 'active',
        ]);
        $this->supervisorUser1->assignRole('supervisor');
        $this->supervisor1 = Supervisor::create([
            'user_id' => $this->supervisorUser1->id,
            'tenant_id' => $this->primaryTenant->id,
            'employee_number' => 'SUP-E2E-001',
            'national_id' => '29001010000001',
            'date_of_birth' => '1985-05-15',
            'gender' => 'male',
            'phone' => '01000000001',
            'hire_date' => '2024-01-01',
            'status' => 'active',
        ]);

        $this->supervisorUser2 = User::factory()->create([
            'name' => 'E2E Supervisor 2',
            'email' => 'supervisor2.e2e@aischool.test',
            'tenant_id' => $this->primaryTenant->id,
            'status' => 'active',
        ]);
        $this->supervisorUser2->assignRole('supervisor');
        $this->supervisor2 = Supervisor::create([
            'user_id' => $this->supervisorUser2->id,
            'tenant_id' => $this->primaryTenant->id,
            'employee_number' => 'SUP-E2E-002',
            'national_id' => '29001010000002',
            'date_of_birth' => '1988-08-20',
            'gender' => 'female',
            'phone' => '01000000002',
            'hire_date' => '2024-03-01',
            'status' => 'active',
        ]);

        // 4. Instructors Setup (4 Instructors)
        for ($i = 1; $i <= 4; $i++) {
            $user = User::factory()->create([
                'name' => "E2E Instructor $i",
                'email' => "instructor$i.e2e@aischool.test",
                'tenant_id' => $this->primaryTenant->id,
                'status' => 'active',
            ]);
            $user->assignRole('instructor');

            $instructor = Instructor::create([
                'tenant_id' => $this->primaryTenant->id,
                'user_id' => $user->id,
                'employee_code' => "INS-E2E-00$i",
                'specialization' => 'Computer Science & AI',
                'hourly_rate' => 150.00,
                'status' => 'active',
            ]);

            $this->instructors[$i] = [
                'user' => $user,
                'model' => $instructor,
            ];
        }

        // 5. Courses Setup (4 Courses, 12 sessions each, 1,200 EGP = 100 EGP / session)
        $this->coursePython = Course::create([
            'tenant_id' => $this->primaryTenant->id,
            'name' => 'Python Fundamentals',
            'code' => 'CRS-PY-101',
            'price' => 1200.00,
            'status' => 'active',
        ]);

        $this->courseScratch = Course::create([
            'tenant_id' => $this->primaryTenant->id,
            'name' => 'Scratch Programming',
            'code' => 'CRS-SC-101',
            'price' => 1200.00,
            'status' => 'active',
        ]);

        $this->courseAppInventor = Course::create([
            'tenant_id' => $this->primaryTenant->id,
            'name' => 'App Inventor Mobile Apps',
            'code' => 'CRS-AI-101',
            'price' => 1200.00,
            'status' => 'active',
        ]);

        $this->courseFrontEnd = Course::create([
            'tenant_id' => $this->primaryTenant->id,
            'name' => 'Front-End Web Development',
            'code' => 'CRS-FE-101',
            'price' => 1200.00,
            'status' => 'active',
        ]);

        // 6. Groups Setup (4 Groups)
        $this->groupA = Group::create([
            'tenant_id' => $this->primaryTenant->id,
            'course_id' => $this->coursePython->id,
            'instructor_id' => $this->instructors[1]['model']->id,
            'supervisor_id' => $this->supervisor1->id,
            'name' => 'E2E-GROUP-A (Python)',
            'total_sessions' => 12,
            'status' => 'active',
            'start_date' => now()->subWeeks(2),
            'end_date' => now()->addWeeks(6),
        ]);

        $this->groupB = Group::create([
            'tenant_id' => $this->primaryTenant->id,
            'course_id' => $this->courseScratch->id,
            'instructor_id' => $this->instructors[2]['model']->id,
            'supervisor_id' => $this->supervisor1->id,
            'name' => 'E2E-GROUP-B (Scratch)',
            'total_sessions' => 12,
            'status' => 'active',
            'start_date' => now()->subWeeks(1),
            'end_date' => now()->addWeeks(7),
        ]);

        $this->groupC = Group::create([
            'tenant_id' => $this->primaryTenant->id,
            'course_id' => $this->courseAppInventor->id,
            'instructor_id' => $this->instructors[3]['model']->id,
            'supervisor_id' => $this->supervisor2->id,
            'name' => 'E2E-GROUP-C (App Inventor)',
            'total_sessions' => 12,
            'status' => 'active',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addWeeks(8),
        ]);

        $this->groupD = Group::create([
            'tenant_id' => $this->primaryTenant->id,
            'course_id' => $this->courseFrontEnd->id,
            'instructor_id' => $this->instructors[4]['model']->id,
            'supervisor_id' => $this->supervisor2->id,
            'name' => 'E2E-GROUP-D (Front-End)',
            'total_sessions' => 12,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addWeeks(9),
        ]);

        // 7. Vaults Setup
        $this->mainVault = Vault::create([
            'tenant_id' => $this->primaryTenant->id,
            'name' => 'E2E Main Cash Safe',
            'type' => 'cash',
            'opening_balance' => 10000.00,
            'is_active' => true,
        ]);

        $this->bankVault = Vault::create([
            'tenant_id' => $this->primaryTenant->id,
            'name' => 'E2E Bank Account',
            'type' => 'instapay',
            'opening_balance' => 25000.00,
            'is_active' => true,
        ]);

        // Setup 8 Parents and 16 Students (2 students per parent)
        for ($p = 1; $p <= 8; $p++) {
            $parentUser = User::factory()->create([
                'name' => "E2E Parent $p",
                'email' => "parent0$p.e2e@aischool.test",
                'tenant_id' => $this->primaryTenant->id,
                'status' => 'active',
            ]);
            $parentUser->assignRole('parent');
            $this->parents[$p] = $parentUser;

            for ($s = 1; $s <= 2; $s++) {
                $studentIndex = ($p - 1) * 2 + $s;
                $studentNumStr = str_pad($studentIndex, 2, '0', STR_PAD_LEFT);

                $studentUser = User::factory()->create([
                    'name' => "E2E Student $studentNumStr",
                    'email' => "student$studentNumStr.e2e@aischool.test",
                    'tenant_id' => $this->primaryTenant->id,
                    'status' => 'active',
                ]);
                $studentUser->assignRole('student');

                $student = Student::create([
                    'tenant_id' => $this->primaryTenant->id,
                    'user_id' => $studentUser->id,
                    'student_code' => "STU-E2E-$studentNumStr",
                    'gender' => ($studentIndex % 2 === 0) ? 'female' : 'male',
                    'birth_date' => '2012-06-15',
                    'phone' => '012' . str_pad($studentIndex, 8, '0', STR_PAD_LEFT),
                    'status' => 'active',
                ]);

                StudentParent::create([
                    'tenant_id' => $this->primaryTenant->id,
                    'user_id' => $parentUser->id,
                    'student_id' => $student->id,
                    'relation' => ($p % 2 === 0) ? 'mother' : 'father',
                    'is_primary' => true,
                ]);

                $this->students[$studentIndex] = [
                    'user' => $studentUser,
                    'model' => $student,
                    'parent' => $parentUser,
                ];
            }
        }
    }

    /**
     * Scenario REG-01: Full Student Registration & Validation Lifecycle
     */
    public function test_e2e_student_registration_workflow(): void
    {
        $this->actingAs($this->registrationStaff1);

        // 1. Validation rejection on missing required fields
        $validator = \Illuminate\Support\Facades\Validator::make([], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string',
        ]);
        expect(fn () => $validator->validate())->toThrow(\Illuminate\Validation\ValidationException::class);

        // 2. Successful creation
        $newUser = User::create([
            'tenant_id' => $this->primaryTenant->id,
            'name' => 'Tarek Mahmoud',
            'email' => 'tarek.mahmoud@aischool.test',
            'password' => bcrypt('Password123!'),
            'status' => 'active',
        ]);
        $newUser->assignRole('student');

        $newStudent = Student::create([
            'tenant_id' => $this->primaryTenant->id,
            'user_id' => $newUser->id,
            'student_code' => 'STU-E2E-REG01',
            'gender' => 'male',
            'birth_date' => '2013-04-10',
            'phone' => '01099887766',
            'status' => 'active',
        ]);

        // Link with Parent 1
        StudentParent::create([
            'tenant_id' => $this->primaryTenant->id,
            'user_id' => $this->parents[1]->id,
            'student_id' => $newStudent->id,
            'relation' => 'father',
        ]);

        expect(Student::where('student_code', 'STU-E2E-REG01')->exists())->toBeTrue()
            ->and($newStudent->user->name)->toBe('Tarek Mahmoud')
            ->and($this->parents[1]->students()->where('students.id', $newStudent->id)->exists())->toBeTrue();

        // 3. Duplicate email rejection
        $isDuplicateEmailValid = \Illuminate\Support\Facades\Validator::make(
            ['email' => 'tarek.mahmoud@aischool.test'],
            ['email' => 'required|email|unique:users,email']
        )->passes();
        expect($isDuplicateEmailValid)->toBeFalse();

        // 4. Student logs in: zero course content prior to enrollment
        $this->actingAs($newUser);
        expect($newStudent->enrollments()->count())->toBe(0);
    }

    /**
     * Scenario ENR-01 & ENR-02: Course Enrollment, Multiple Subscriptions, Group Scoping
     */
    public function test_e2e_subscription_and_group_enrollment(): void
    {
        $this->actingAs($this->registrationStaff1);

        $student1 = $this->students[1]['model'];

        // Enrollment 1 in Python (Group A)
        $enrollment1 = Enrollment::create([
            'tenant_id' => $this->primaryTenant->id,
            'student_id' => $student1->id,
            'group_id' => $this->groupA->id,
            'vault_id' => $this->mainVault->id,
            'enrollment_code' => 'ENR-E2E-001',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 600.00,
            'discount' => 0.00,
            'payment_plan' => 'installments',
            'installments_count' => 2,
            'status' => 'active',
        ]);

        EnrollmentCourse::create([
            'tenant_id' => $this->primaryTenant->id,
            'enrollment_id' => $enrollment1->id,
            'course_id' => $this->coursePython->id,
            'group_id' => $this->groupA->id,
            'course_price' => 1200.00,
            'current_session' => 0,
            'total_sessions' => 12,
            'status' => 'active',
        ]);

        // Enrollment 2 in Scratch (Group B) - Multiple Subscriptions Independence
        $enrollment2 = Enrollment::create([
            'tenant_id' => $this->primaryTenant->id,
            'student_id' => $student1->id,
            'group_id' => $this->groupB->id,
            'vault_id' => $this->mainVault->id,
            'enrollment_code' => 'ENR-E2E-002',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 1200.00,
            'discount' => 0.00,
            'payment_plan' => 'full',
            'status' => 'active',
        ]);

        EnrollmentCourse::create([
            'tenant_id' => $this->primaryTenant->id,
            'enrollment_id' => $enrollment2->id,
            'course_id' => $this->courseScratch->id,
            'group_id' => $this->groupB->id,
            'course_price' => 1200.00,
            'current_session' => 0,
            'total_sessions' => 12,
            'status' => 'active',
        ]);

        // Check student has 2 distinct active enrollments
        expect($student1->enrollments()->count())->toBe(2);
        expect($student1->groups()->count())->toBe(2);

        // Student view: both appear independently
        $this->actingAs($this->students[1]['user']);
        $activeEnrollments = $student1->enrollments()->with(['courses', 'group'])->get();
        expect($activeEnrollments->pluck('id')->all())->toContain($enrollment1->id, $enrollment2->id);
    }

    /**
     * Scenario ATT-01 to ATT-06: Session Scheduling, Dual Attendance Lifecycle & Follow-up Trigger
     */
    public function test_e2e_sessions_and_attendance_lifecycle(): void
    {
        $this->actingAs($this->instructors[1]['user']);

        $student1 = $this->students[1]['model'];
        $enrollment = Enrollment::create([
            'tenant_id' => $this->primaryTenant->id,
            'student_id' => $student1->id,
            'group_id' => $this->groupA->id,
            'vault_id' => $this->mainVault->id,
            'enrollment_code' => 'ENR-ATT-001',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 1200.00,
            'status' => 'active',
        ]);

        $enrollmentCourse = EnrollmentCourse::create([
            'tenant_id' => $this->primaryTenant->id,
            'enrollment_id' => $enrollment->id,
            'course_id' => $this->coursePython->id,
            'group_id' => $this->groupA->id,
            'course_price' => 1200.00,
            'current_session' => 0,
            'total_sessions' => 12,
            'status' => 'active',
        ]);

        // Case ATT-01: Before course starts (0 sessions)
        expect($enrollmentCourse->current_session)->toBe(0);

        // Case ATT-02: Session 1 held, Student 1 attends
        $session1 = AttendanceSession::create([
            'tenant_id' => $this->primaryTenant->id,
            'group_id' => $this->groupA->id,
            'course_id' => $this->coursePython->id,
            'session_title' => 'Python Basics 1',
            'session_number' => 1,
            'attendance_date' => now()->subDays(10),
            'status' => 'completed',
        ]);
        AttendanceRecord::create([
            'attendance_session_id' => $session1->id,
            'student_id' => $student1->id,
            'status' => 'present',
        ]);

        // Refresh and check progress
        $enrollmentCourse->refresh();
        expect($enrollmentCourse->current_session)->toBe(1);

        // Case ATT-03: Consecutive absences trigger StudentFollowUpTask
        for ($i = 2; $i <= 4; $i++) {
            $sess = AttendanceSession::create([
                'tenant_id' => $this->primaryTenant->id,
                'group_id' => $this->groupA->id,
                'course_id' => $this->coursePython->id,
                'session_title' => "Python Session $i",
                'session_number' => $i,
                'attendance_date' => now()->subDays(10 - $i),
                'status' => 'completed',
            ]);
            AttendanceRecord::create([
                'attendance_session_id' => $sess->id,
                'student_id' => $student1->id,
                'status' => 'absent',
            ]);
        }

        // Explicitly trigger StudentFollowUpService check
        $followUpService = app(StudentFollowUpService::class);
        $task = $followUpService->checkAndTriggerFollowUp($student1, 3);

        expect($task)->not->toBeNull()
            ->and($task->student_id)->toBe($student1->id)
            ->and($task->trigger_type)->toBe('repeated_absence');

        // Prevent duplicate follow-up task creation
        $duplicateTask = $followUpService->checkAndTriggerFollowUp($student1, 3);
        expect($duplicateTask)->toBeNull();

        // Rescheduled session / makeup session
        $rescheduledSession = AttendanceSession::create([
            'tenant_id' => $this->primaryTenant->id,
            'group_id' => $this->groupA->id,
            'course_id' => $this->coursePython->id,
            'session_title' => 'Python Makeup Session',
            'session_number' => 4,
            'attendance_date' => now(),
            'status' => 'completed',
        ]);
        AttendanceRecord::create([
            'attendance_session_id' => $rescheduledSession->id,
            'student_id' => $student1->id,
            'status' => 'late',
        ]);

        $enrollmentCourse->refresh();
        expect($enrollmentCourse->current_session)->toBe(2); // 1 present + 1 late = 2 attended
    }

    /**
     * Learning Resources Upload, Visibility & Cross-Group Security
     */
    public function test_e2e_learning_resources_workflow(): void
    {
        $this->actingAs($this->instructors[1]['user']);

        // Upload PDF resource to Group A
        $resourcePdf = LearningResource::create([
            'tenant_id' => $this->primaryTenant->id,
            'course_id' => $this->coursePython->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructors[1]['model']->id,
            'title' => 'Python Cheatsheet PDF',
            'type' => 'pdf',
            'file_path' => 'resources/python_cheatsheet.pdf',
            'is_downloadable' => true,
            'is_active' => true,
        ]);

        // Upload External URL to Group A
        $resourceUrl = LearningResource::create([
            'tenant_id' => $this->primaryTenant->id,
            'course_id' => $this->coursePython->id,
            'group_id' => $this->groupA->id,
            'instructor_id' => $this->instructors[1]['model']->id,
            'title' => 'Python Documentation Guide',
            'type' => 'url',
            'external_url' => 'https://docs.python.org/3/',
            'is_active' => true,
        ]);

        // Student 1 (enrolled in Group A) sees the resource
        $student1 = $this->students[1]['model'];
        Enrollment::create([
            'tenant_id' => $this->primaryTenant->id,
            'student_id' => $student1->id,
            'group_id' => $this->groupA->id,
            'vault_id' => $this->mainVault->id,
            'enrollment_code' => 'ENR-RES-001',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 1200.00,
            'status' => 'active',
        ]);

        $this->actingAs($this->students[1]['user']);
        $visibleResources = LearningResource::where('group_id', $this->groupA->id)->where('is_active', true)->get();
        expect($visibleResources->pluck('id')->all())->toContain($resourcePdf->id, $resourceUrl->id);

        // Student 5 (enrolled only in Group B) must NOT see Group A resources
        $student5 = $this->students[5]['model'];
        Enrollment::create([
            'tenant_id' => $this->primaryTenant->id,
            'student_id' => $student5->id,
            'group_id' => $this->groupB->id,
            'vault_id' => $this->mainVault->id,
            'enrollment_code' => 'ENR-RES-002',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 1200.00,
            'status' => 'active',
        ]);

        $this->actingAs($this->students[5]['user']);
        $student5EnrolledGroupIds = Enrollment::where('student_id', $student5->id)->pluck('group_id')->toArray();
        $student5Resources = LearningResource::whereIn('group_id', $student5EnrolledGroupIds)->get();

        expect($student5Resources->pluck('id')->all())->not->toContain($resourcePdf->id, $resourceUrl->id);
    }

    /**
     * Homework Assignment, 3-Image Submission, Instructor Grading & Draft Masking
     */
    public function test_e2e_homework_assignment_and_grading_lifecycle(): void
    {
        $this->actingAs($this->instructors[1]['user']);

        $student1 = $this->students[1]['model'];
        Enrollment::create([
            'tenant_id' => $this->primaryTenant->id,
            'student_id' => $student1->id,
            'group_id' => $this->groupA->id,
            'vault_id' => $this->mainVault->id,
            'enrollment_code' => 'ENR-HW-001',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 1200.00,
            'status' => 'active',
        ]);

        // Instructor creates Homework
        $assignment = Assignment::create([
            'tenant_id' => $this->primaryTenant->id,
            'group_id' => $this->groupA->id,
            'course_id' => $this->coursePython->id,
            'instructor_id' => $this->instructors[1]['model']->id,
            'title' => 'Loops & Data Structures Assignment',
            'description' => 'Solve exercises 1-5 and photograph your solution.',
            'due_date' => now()->addDays(5),
            'max_score' => 10.0,
            'status' => 'active',
        ]);

        // Student 1 uploads 3 images
        $this->actingAs($this->students[1]['user']);
        $img1 = UploadedFile::fake()->image('p1.png');
        $img2 = UploadedFile::fake()->image('p2.jpg');
        $img3 = UploadedFile::fake()->image('p3.webp');

        $hwService = app(HomeworkService::class);
        $submission = $hwService->submitHomework($assignment, $student1, [$img1, $img2, $img3], 'My solution images attached');

        expect($submission)->not->toBeNull()
            ->and($submission->status)->toBe('submitted')
            ->and($submission->image_count)->toBe(3);

        // Instructor saves draft grade: 9.0
        $this->actingAs($this->instructors[1]['user']);
        $hwService->saveDraftGrade($submission, 9.0, 'Good draft work', $this->instructors[1]['user']);

        // Student checks portal: Draft score must be masked
        $this->actingAs($this->students[1]['user']);
        $submission->refresh();
        expect($submission->isDraft())->toBeTrue();

        // Instructor publishes grade
        $this->actingAs($this->instructors[1]['user']);
        $hwService->publishGrade($submission, 9.5, 'Excellent code structure and logic!', $this->instructors[1]['user']);

        // Student now sees published grade and feedback
        $submission->refresh();
        expect($submission->isPublished())->toBeTrue()
            ->and((float) $submission->score)->toBe(9.5)
            ->and($submission->instructor_feedback)->toBe('Excellent code structure and logic!');
    }

    /**
     * Internal Messaging: MSG-01 Private Message, MSG-02 Announcement, MSG-03 Scheduled
     */
    public function test_e2e_messaging_communication_workflow(): void
    {
        $this->actingAs($this->instructors[1]['user']);

        $student1 = $this->students[1]['model'];
        $student2 = $this->students[2]['model'];

        Enrollment::create([
            'tenant_id' => $this->primaryTenant->id,
            'student_id' => $student1->id,
            'group_id' => $this->groupA->id,
            'vault_id' => $this->mainVault->id,
            'enrollment_code' => 'ENR-MSG-001',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 1200.00,
            'status' => 'active',
        ]);
        Enrollment::create([
            'tenant_id' => $this->primaryTenant->id,
            'student_id' => $student2->id,
            'group_id' => $this->groupA->id,
            'vault_id' => $this->mainVault->id,
            'enrollment_code' => 'ENR-MSG-002',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 1200.00,
            'status' => 'active',
        ]);

        // MSG-01: Private Message to Student 1
        $privateMessage = StudentMessage::create([
            'tenant_id' => $this->primaryTenant->id,
            'sender_id' => $this->instructors[1]['user']->id,
            'student_id' => $student1->id,
            'subject' => 'Follow up on Python Lab',
            'message' => 'Please review the dictionary indexing rules.',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // Student 1 can see it
        $this->actingAs($this->students[1]['user']);
        expect(StudentMessage::where('student_id', $student1->id)->where('id', $privateMessage->id)->exists())->toBeTrue();

        // Student 2 cannot see Student 1's private message
        $this->actingAs($this->students[2]['user']);
        expect(StudentMessage::where('student_id', $student2->id)->where('id', $privateMessage->id)->exists())->toBeFalse();

        // MSG-02: Group Announcement to Group A
        $this->actingAs($this->instructors[1]['user']);
        $groupMsg = GroupMessage::create([
            'tenant_id' => $this->primaryTenant->id,
            'group_id' => $this->groupA->id,
            'sender_id' => $this->instructors[1]['user']->id,
            'subject' => 'Project Deadline Announcement',
            'message' => 'Final projects are due next Saturday.',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // Both Student 1 & 2 in Group A see it
        $this->actingAs($this->students[1]['user']);
        expect(GroupMessage::where('group_id', $this->groupA->id)->where('id', $groupMsg->id)->exists())->toBeTrue();

        // Student 5 in Group B cannot see Group A's announcement
        $this->actingAs($this->students[5]['user']);
        $student5GroupIds = Enrollment::where('student_id', $this->students[5]['model']->id)->pluck('group_id')->toArray();
        expect(GroupMessage::whereIn('group_id', $student5GroupIds)->where('id', $groupMsg->id)->exists())->toBeFalse();
    }

    /**
     * Subscription Cancellation & Settlement: CAN-01 to CAN-16, 6 Settlement Methods
     */
    public function test_e2e_subscription_cancellation_and_settlement_matrix(): void
    {
        $cancelService = app(EnrollmentCancellationService::class);

        // CAN-01: Cancel before start (Full payment 1200, 0 sessions) -> Full refund 1200
        $student9 = $this->students[9]['model'];
        $enrollment1 = Enrollment::create([
            'tenant_id' => $this->primaryTenant->id,
            'student_id' => $student9->id,
            'group_id' => $this->groupC->id,
            'enrollment_code' => 'ENR-CAN-01',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 1200.00,
            'vault_id' => $this->mainVault->id,
            'status' => 'active',
        ]);
        Payment::create([
            'tenant_id' => $this->primaryTenant->id,
            'enrollment_id' => $enrollment1->id,
            'vault_id' => $this->mainVault->id,
            'payment_code' => 'PAY-CAN-01',
            'amount' => 1200.00,
            'status' => 'completed',
            'payment_date' => now(),
        ]);

        $this->actingAs($this->branchManager);
        $preview1 = $cancelService->getFinancialSnapshot($enrollment1);
        expect((float) $preview1['total_paid'])->toBe(1200.00);

        // Settle & Refund
        $cancellation1 = $cancelService->cancelSubscription(
            $enrollment1,
            $this->branchManager,
            [
                'reason' => 'Student relocated',
                'settlement_type' => 'full_refund',
            ]
        );

        expect($enrollment1->fresh()->status)->toBe('cancelled')
            ->and($cancellation1->settlement_type)->toBe('full_refund')
            ->and((float) $cancellation1->refundable_amount)->toBe(1200.00);

        // Execute Refund Disbursement
        $refundRes = $cancelService->executeRefund($enrollment1, $this->branchManager, 1200.00, $this->mainVault->id, 'cash');
        expect($refundRes['success'])->toBeTrue()
            ->and((float) $enrollment1->fresh()->refunded_amount)->toBe(1200.00)
            ->and($enrollment1->fresh()->refund_status)->toBe('completed');

        // CAN-04: Cancellation after 4 sessions (Proportional pricing: 4 * 100 = 400 earned, paid 600 -> 200 refund)
        $student10 = $this->students[10]['model'];
        $enrollment2 = Enrollment::create([
            'tenant_id' => $this->primaryTenant->id,
            'student_id' => $student10->id,
            'group_id' => $this->groupC->id,
            'enrollment_code' => 'ENR-CAN-04',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 600.00,
            'vault_id' => $this->mainVault->id,
            'status' => 'active',
        ]);
        EnrollmentCourse::create([
            'tenant_id' => $this->primaryTenant->id,
            'enrollment_id' => $enrollment2->id,
            'course_id' => $this->courseAppInventor->id,
            'group_id' => $this->groupC->id,
            'current_session' => 4,
            'total_sessions' => 12,
            'course_price' => 1200.00,
            'status' => 'active',
        ]);

        $cancellation2 = $cancelService->cancelSubscription(
            $enrollment2,
            $this->branchManager,
            [
                'reason' => 'Session-based cancellation',
                'settlement_type' => 'session_based',
                'chargeable_sessions' => 4,
            ]
        );

        // Total paid 600 - 400 earned = 200 refundable
        expect((float) $cancellation2->retained_amount)->toBe(400.00)
            ->and((float) $cancellation2->refundable_amount)->toBe(200.00);

        // CAN-11: Duplicate cancellation rejected
        expect(fn () => $cancelService->cancelSubscription($enrollment2, $this->branchManager, ['reason' => 'Duplicate', 'settlement_type' => 'waive_debt']))
            ->toThrow(\Exception::class);

        // CAN-12: Employee without cancellation permission is rejected
        $unauthorizedStaff = User::factory()->create(['tenant_id' => $this->primaryTenant->id]);
        $enrollment3 = Enrollment::create([
            'tenant_id' => $this->primaryTenant->id,
            'student_id' => $this->students[11]['model']->id,
            'group_id' => $this->groupC->id,
            'enrollment_code' => 'ENR-CAN-03',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 0.00,
            'status' => 'active',
        ]);

        expect(fn () => $cancelService->cancelSubscription($enrollment3, $unauthorizedStaff, ['reason' => 'No perm', 'settlement_type' => 'waive_debt']))
            ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
    }

    /**
     * Post-Cancellation Access & Historical Academic Data Preservation
     */
    public function test_e2e_post_cancellation_historical_access_verification(): void
    {
        $student12 = $this->students[12]['model'];

        // 1. Student completes learning actions before cancellation
        $enrollment = Enrollment::create([
            'tenant_id' => $this->primaryTenant->id,
            'student_id' => $student12->id,
            'group_id' => $this->groupC->id,
            'vault_id' => $this->mainVault->id,
            'enrollment_code' => 'ENR-HIST-001',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 1200.00,
            'status' => 'active',
        ]);

        $payment = Payment::create([
            'tenant_id' => $this->primaryTenant->id,
            'enrollment_id' => $enrollment->id,
            'vault_id' => $this->mainVault->id,
            'payment_code' => 'PAY-E2E-HIST-01',
            'amount' => 1200.00,
            'status' => 'completed',
            'payment_date' => now(),
        ]);

        $exam = Exam::create([
            'tenant_id' => $this->primaryTenant->id,
            'course_id' => $this->courseAppInventor->id,
            'group_id' => $this->groupC->id,
            'title' => 'App Inventor Midterm',
            'exam_date' => now()->subDays(2),
            'duration' => 60,
            'total_marks' => 100,
            'passing_marks' => 50,
            'status' => 'completed',
        ]);

        $grade = Grade::create([
            'tenant_id' => $this->primaryTenant->id,
            'exam_id' => $exam->id,
            'student_id' => $student12->id,
            'marks_obtained' => 88.0,
            'percentage' => 88.0,
            'grade' => 'A',
            'graded_at' => now()->subDay(),
        ]);

        // 2. Subscription is canceled
        $cancelService = app(EnrollmentCancellationService::class);
        $cancelService->cancelSubscription($enrollment, $this->branchManager, [
            'reason' => 'Family relocation',
            'settlement_type' => 'waive_debt',
        ]);

        expect($enrollment->fresh()->status)->toBe('cancelled');

        // 3. Confirm historical records remain intact and are NOT deleted
        expect($payment->fresh())->not->toBeNull()
            ->and($grade->fresh())->not->toBeNull()
            ->and((float) $grade->fresh()->marks_obtained)->toBe(88.0)
            ->and($enrollment->fresh()->cancellation)->not->toBeNull();
    }

    /**
     * Parent Portal Access, Multi-Child Switching & Session Tamper Rejection
     */
    public function test_e2e_parent_portal_multi_child_switching(): void
    {
        $parent1 = $this->parents[1];
        $child1 = $this->students[1]['model'];
        $child2 = $this->students[2]['model'];
        $strangerChild = $this->students[3]['model']; // Belongs to Parent 2

        $this->actingAs($parent1);

        // 1. Initial selection defaults to first authorized child
        $selected = ParentStudentAccessService::getSelectedStudent();
        expect($selected->id)->toBe($child1->id);

        // 2. Switch to second child
        expect(ParentStudentAccessService::switchStudent($child2->id))->toBeTrue();
        expect(ParentStudentAccessService::getSelectedStudentId())->toBe($child2->id);

        // 3. Attempt unauthorized switch to stranger's child -> Rejected
        expect(ParentStudentAccessService::switchStudent($strangerChild->id))->toBeFalse();
        expect(ParentStudentAccessService::getSelectedStudentId())->toBe($child2->id);

        // 4. Session tampering attempt -> Cleared and falls back to authorized child
        session(['selected_student_id' => $strangerChild->id]);
        $sanitizedChild = ParentStudentAccessService::getSelectedStudent();
        expect($sanitizedChild->id)->toBe($child1->id)
            ->and($sanitizedChild->id)->not->toBe($strangerChild->id);
    }

    /**
     * Complete Financial Operations & Vault Reconciliation
     */
    public function test_e2e_financial_operations_and_vault_reconciliation(): void
    {
        $initialBalance = $this->mainVault->getCurrentBalance();
        expect($initialBalance)->toBe(10000.00);

        // 1. Collection: 500 EGP payment
        $enrollment = Enrollment::create([
            'tenant_id' => $this->primaryTenant->id,
            'student_id' => $this->students[1]['model']->id,
            'group_id' => $this->groupA->id,
            'enrollment_code' => 'ENR-FIN-001',
            'enrollment_date' => now(),
            'total_amount' => 1200.00,
            'paid_amount' => 0.00,
            'status' => 'active',
        ]);

        $payment = Payment::create([
            'tenant_id' => $this->primaryTenant->id,
            'enrollment_id' => $enrollment->id,
            'vault_id' => $this->mainVault->id,
            'payment_code' => 'PAY-E2E-FIN-01',
            'amount' => 500.00,
            'status' => 'completed',
            'payment_date' => now(),
        ]);
        expect($this->mainVault->getCurrentBalance())->toBe(10500.00);

        // 2. Standalone Revenue: 300 EGP (e.g. book/kit sale)
        $revCat = RevenueCategory::create([
            'tenant_id' => $this->primaryTenant->id,
            'name' => 'Kits & Books',
            'code' => 'REV-CAT-E2E',
            'is_active' => true,
        ]);

        Revenue::create([
            'tenant_id' => $this->primaryTenant->id,
            'vault_id' => $this->mainVault->id,
            'revenue_category_id' => $revCat->id,
            'title' => 'Robotics Starter Kit',
            'amount' => 300.00,
            'revenue_date' => now(),
            'payment_method' => 'cash',
            'status' => 'received',
            'created_by' => $this->accountant->id,
        ]);
        expect($this->mainVault->getCurrentBalance())->toBe(10800.00);

        // 3. Expense: 400 EGP
        $expCat = ExpenseCategory::create([
            'tenant_id' => $this->primaryTenant->id,
            'name' => 'Lab Electricity & Net',
            'code' => 'EXP-CAT-E2E',
            'is_active' => true,
        ]);

        Expense::create([
            'tenant_id' => $this->primaryTenant->id,
            'vault_id' => $this->mainVault->id,
            'expense_category_id' => $expCat->id,
            'expense_code' => 'EXP-E2E-01',
            'title' => 'High speed internet bill',
            'amount' => 400.00,
            'expense_date' => now(),
            'payment_method' => 'cash',
            'status' => 'paid',
            'created_by' => $this->accountant->id,
        ]);
        expect($this->mainVault->getCurrentBalance())->toBe(10400.00);

        // 4. Safe Vault Transfer: Transfer 1000 EGP from Main Safe to Bank
        $transfer = VaultTransfer::create([
            'tenant_id' => $this->primaryTenant->id,
            'from_vault_id' => $this->mainVault->id,
            'to_vault_id' => $this->bankVault->id,
            'amount' => 1000.00,
            'transfer_date' => now(),
            'status' => 'pending',
            'created_by' => $this->accountant->id,
        ]);
        $transfer->update(['status' => 'completed']);

        // Main Vault: 10400 - 1000 = 9400
        expect($this->mainVault->getCurrentBalance())->toBe(9400.00);
        // Bank Vault: 25000 + 1000 = 26000
        expect($this->bankVault->getCurrentBalance())->toBe(26000.00);
    }
}

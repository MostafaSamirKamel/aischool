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
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Vault;
use App\Models\SessionSchedule;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\ExamRetakeGrant;
use App\Models\InstructorPayment;
use App\Models\SalaryPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

echo "================================================================" . PHP_EOL;
echo "  AI SCHOOL — MASTER REAL-WORLD OPERATIONAL SIMULATION SETUP    " . PHP_EOL;
echo "================================================================" . PHP_EOL;

$manifest = [
    'run_id' => 'SIM-MATAMIR-20261027',
    'timestamp' => now()->toIso8601String(),
    'branch_matamir_id' => 1,
    'branch_online_id' => 2,
    'classrooms' => [],
    'courses' => [],
    'groups' => [],
    'students' => [],
    'sessions' => [],
    'homeworks' => [],
    'submissions' => [],
    'exams' => [],
    'attempts' => [],
    'salaries' => [],
    'cancellations' => [],
    'vaults' => [],
];

DB::beginTransaction();

try {
    // ---------------------------------------------------------
    // 1. TENANTS / BRANCHES
    // ---------------------------------------------------------
    echo "[1/10] Verifying Branches..." . PHP_EOL;
    $b1 = Tenant::updateOrCreate(['id' => 1], [
        'name' => 'أبو المطامير',
        'slug' => 'abu-el-matamir',
        'is_active' => true,
    ]);
    $b2 = Tenant::updateOrCreate(['id' => 2], [
        'name' => 'الأونلاين',
        'slug' => 'online',
        'is_active' => true,
    ]);
    echo "  - Branch 1: {$b1->name} (ID: {$b1->id})" . PHP_EOL;
    echo "  - Branch 2: {$b2->name} (ID: {$b2->id})" . PHP_EOL;

    // ---------------------------------------------------------
    // 2. REQUIRED ACCOUNTS
    // ---------------------------------------------------------
    echo "[2/10] Verifying Core Staging Accounts..." . PHP_EOL;
    // Abu El Matamir Manager: 01070801308
    $mgrMatamir = User::updateOrCreate(['username' => '01070801308'], [
        'name' => 'مدير فرع أبو المطامير',
        'phone' => '01070801308',
        'email' => 'manager.matamir@aischool.test',
        'password' => Hash::make('password'),
        'tenant_id' => 1,
        'status' => 'active',
        'is_super_admin' => false,
    ]);
    $mgrMatamir->syncRoles(['branch_manager']);

    // Online Manager: 01501101213
    $mgrOnline = User::updateOrCreate(['username' => '01501101213'], [
        'name' => 'مدير فرع الأونلاين',
        'phone' => '01501101213',
        'email' => 'manager.online@aischool.test',
        'password' => Hash::make('password'),
        'tenant_id' => 2,
        'status' => 'active',
        'is_super_admin' => false,
    ]);
    $mgrOnline->syncRoles(['branch_manager']);

    // Instructor: 01091812925
    $instUser = User::updateOrCreate(['username' => '01091812925'], [
        'name' => 'أحمد حسن - مدرب أبو المطامير',
        'phone' => '01091812925',
        'email' => 'instructor.matamir@aischool.test',
        'password' => Hash::make('password'),
        'tenant_id' => 1,
        'status' => 'active',
        'is_super_admin' => false,
    ]);
    $instUser->syncRoles(['instructor']);

    // Sync Instructor ID to match instUser->id
    $existingProfile = Instructor::where('user_id', $instUser->id)->first();
    if ($existingProfile && $existingProfile->id !== $instUser->id) {
        DB::table('instructors')->where('id', $existingProfile->id)->update(['id' => $instUser->id]);
    }
    $instProfile = Instructor::updateOrCreate(['user_id' => $instUser->id], [
        'id' => $instUser->id,
        'tenant_id' => 1,
        'employee_code' => 'INS-MAT-01',
        'phone' => '01091812925',
        'specialization' => 'علوم الحاسب والبرمجة',
        'hourly_rate' => 150.00,
        'base_salary' => 2000.00,
        'status' => 'active',
    ]);

    // Student: 01007287273
    $stdUser = User::updateOrCreate(['username' => '01007287273'], [
        'name' => 'زياد طارق - طالب أبو المطامير',
        'phone' => '01007287273',
        'email' => 'student.matamir@aischool.test',
        'password' => Hash::make('password'),
        'tenant_id' => 1,
        'status' => 'active',
        'is_super_admin' => false,
    ]);
    $stdUser->syncRoles(['student']);

    $stdProfile = Student::updateOrCreate(['user_id' => $stdUser->id], [
        'tenant_id' => 1,
        'student_code' => 'STD-MAT-001',
        'phone' => '01007287273',
        'gender' => 'male',
        'birth_date' => '2012-05-15',
        'national_id' => '31205151801234',
        'school_name' => 'مدرسة أبو المطامير التجريبية',
        'education_level' => 'primary',
        'status' => 'active',
    ]);

    echo "  - Manager Matamir: User ID {$mgrMatamir->id}" . PHP_EOL;
    echo "  - Manager Online: User ID {$mgrOnline->id}" . PHP_EOL;
    echo "  - Instructor: User ID {$instUser->id}, Profile ID {$instProfile->id}" . PHP_EOL;
    echo "  - Student: User ID {$stdUser->id}, Profile ID {$stdProfile->id}" . PHP_EOL;

    // ---------------------------------------------------------
    // 3. CLASSROOMS (EXACTLY TWO IN ABU EL MATAMIR)
    // ---------------------------------------------------------
    echo "[3/10] Configuring Exactly Two Classrooms in Abu El Matamir..." . PHP_EOL;
    // Archive other classrooms in tenant 1 to avoid ambiguity
    Classroom::where('tenant_id', 1)->whereNotIn('name', ['قاعة 1', 'قاعة 2'])->update(['status' => 'maintenance']);

    $room1 = Classroom::updateOrCreate(
        ['tenant_id' => 1, 'name' => 'قاعة 1'],
        ['capacity' => 17, 'status' => 'active', 'location' => 'الدور الأول - معمل 1']
    );
    $room2 = Classroom::updateOrCreate(
        ['tenant_id' => 1, 'name' => 'قاعة 2'],
        ['capacity' => 12, 'status' => 'active', 'location' => 'الدور الأول - معمل 2']
    );

    $manifest['classrooms'] = [
        ['id' => $room1->id, 'name' => $room1->name, 'capacity' => $room1->capacity, 'status' => $room1->status],
        ['id' => $room2->id, 'name' => $room2->name, 'capacity' => $room2->capacity, 'status' => $room2->status],
    ];
    echo "  - Classroom 1: ID {$room1->id}, Capacity: {$room1->capacity}" . PHP_EOL;
    echo "  - Classroom 2: ID {$room2->id}, Capacity: {$room2->capacity}" . PHP_EOL;

    // ---------------------------------------------------------
    // 4. COURSES
    // ---------------------------------------------------------
    echo "[4/10] Configuring Courses..." . PHP_EOL;
    $coursePython = Course::updateOrCreate(
        ['tenant_id' => 1, 'name' => 'Python Junior'],
        ['price' => 1500.00, 'sessions_count' => 8, 'duration_months' => 2, 'status' => 'active', 'description' => 'كورس بايثون للناشئين']
    );
    $courseHtml = Course::updateOrCreate(
        ['tenant_id' => 1, 'name' => 'HTML & CSS'],
        ['price' => 1500.00, 'sessions_count' => 8, 'duration_months' => 2, 'status' => 'active', 'description' => 'تصميم وتطوير صفحات الويب']
    );
    $courseScratch = Course::updateOrCreate(
        ['tenant_id' => 1, 'name' => 'Scratch'],
        ['price' => 1200.00, 'sessions_count' => 8, 'duration_months' => 2, 'status' => 'active', 'description' => 'برمجة الألعاب التفاعلية للأطفال']
    );

    $manifest['courses'] = [
        ['id' => $coursePython->id, 'name' => $coursePython->name, 'price' => $coursePython->price],
        ['id' => $courseHtml->id, 'name' => $courseHtml->name, 'price' => $courseHtml->price],
        ['id' => $courseScratch->id, 'name' => $courseScratch->name, 'price' => $courseScratch->price],
    ];
    echo "  - Course: Python Junior (ID: {$coursePython->id})" . PHP_EOL;
    echo "  - Course: HTML & CSS (ID: {$courseHtml->id})" . PHP_EOL;
    echo "  - Course: Scratch (ID: {$courseScratch->id})" . PHP_EOL;

    // ---------------------------------------------------------
    // 5. GROUPS
    // ---------------------------------------------------------
    echo "[5/10] Configuring Three Operational Groups..." . PHP_EOL;
    // GROUP A — Python Junior
    $groupA = Group::updateOrCreate(
        ['tenant_id' => 1, 'name' => 'Group A — Python Junior'],
        [
            'course_id' => $coursePython->id,
            'classroom_id' => $room1->id,
            'instructor_id' => $instUser->id,
            'max_students' => 15,
            'capacity' => 15,
            'status' => 'active',
            'start_date' => Carbon::now()->subWeeks(3)->startOfWeek()->toDateString(),
            'end_date' => Carbon::now()->addWeeks(5)->endOfWeek()->toDateString(),
            'schedule' => [
                [
                    'day' => 'saturday',
                    'start_time' => '10:00',
                    'end_time' => '12:00',
                ]
            ],
        ]
    );

    // GROUP B — HTML & CSS (Shares Room 1 with non-overlapping time slot 13:00 - 15:00)
    $groupB = Group::updateOrCreate(
        ['tenant_id' => 1, 'name' => 'Group B — HTML & CSS'],
        [
            'course_id' => $courseHtml->id,
            'classroom_id' => $room1->id,
            'instructor_id' => $instUser->id,
            'max_students' => 15,
            'capacity' => 15,
            'status' => 'active',
            'start_date' => Carbon::now()->subWeeks(3)->startOfWeek()->toDateString(),
            'end_date' => Carbon::now()->addWeeks(5)->endOfWeek()->toDateString(),
            'schedule' => [
                [
                    'day' => 'saturday',
                    'start_time' => '13:00',
                    'end_time' => '15:00',
                ]
            ],
        ]
    );

    // GROUP C — Scratch (Uses Room 2 on Sunday 10:00 - 12:00)
    $groupC = Group::updateOrCreate(
        ['tenant_id' => 1, 'name' => 'Group C — Scratch'],
        [
            'course_id' => $courseScratch->id,
            'classroom_id' => $room2->id,
            'instructor_id' => $instUser->id,
            'max_students' => 15,
            'capacity' => 15,
            'status' => 'active',
            'start_date' => Carbon::now()->subWeeks(3)->startOfWeek()->toDateString(),
            'end_date' => Carbon::now()->addWeeks(5)->endOfWeek()->toDateString(),
            'schedule' => [
                [
                    'day' => 'sunday',
                    'start_time' => '10:00',
                    'end_time' => '12:00',
                ]
            ],
        ]
    );

    $manifest['groups'] = [
        ['id' => $groupA->id, 'name' => $groupA->name, 'course' => 'Python Junior', 'classroom' => 'قاعة 1', 'target_students' => 12],
        ['id' => $groupB->id, 'name' => $groupB->name, 'course' => 'HTML & CSS', 'classroom' => 'قاعة 1', 'target_students' => 5],
        ['id' => $groupC->id, 'name' => $groupC->name, 'course' => 'Scratch', 'classroom' => 'قاعة 2', 'target_students' => 12],
    ];
    echo "  - Group A: ID {$groupA->id} | Course: Python Junior | Room: قاعة 1 (10:00-12:00)" . PHP_EOL;
    echo "  - Group B: ID {$groupB->id} | Course: HTML & CSS | Room: قاعة 1 (13:00-15:00)" . PHP_EOL;
    echo "  - Group C: ID {$groupC->id} | Course: Scratch | Room: قاعة 2 (Sun 10:00-12:00)" . PHP_EOL;

    // ---------------------------------------------------------
    // 6. SYNTHETIC STUDENTS PREPARATION (EXACTLY 29)
    // ---------------------------------------------------------
    echo "[6/10] Preparing Exactly 29 Test Students & Enrollments..." . PHP_EOL;

    // Staging Vault for Cash Collections
    $vaultMain = Vault::where('tenant_id', 1)->where('type', 'cash')->first();
    if (!$vaultMain) {
        $vaultMain = Vault::create([
            'tenant_id' => 1,
            'name' => 'خزينة كاش أبو المطامير',
            'type' => 'cash',
            'opening_balance' => 50000.00,
            'is_active' => true,
        ]);
    }

    $allStudentsDefs = [
        // GROUP A (12 Students)
        [
            'group' => $groupA,
            'course' => $coursePython,
            'phone' => '01007287273', // Approved existing test student
            'name' => 'زياد طارق - طالب أبو المطامير',
            'code' => 'STD-MAT-001',
            'gender' => 'male',
            'user' => $stdUser,
            'student' => $stdProfile,
            'plan' => 'full', // Fully paid
            'down_payment' => 1500.00,
        ],
        ['group' => $groupA, 'course' => $coursePython, 'phone' => '01007287002', 'name' => 'يوسف أحمد محمود', 'code' => 'STD-MAT-002', 'gender' => 'male', 'plan' => 'installments', 'down_payment' => 500.00],
        ['group' => $groupA, 'course' => $coursePython, 'phone' => '01007287003', 'name' => 'عمر خالد عبد الرحمن', 'code' => 'STD-MAT-003', 'gender' => 'male', 'plan' => 'installments', 'down_payment' => 500.00],
        ['group' => $groupA, 'course' => $coursePython, 'phone' => '01007287004', 'name' => 'مصطفى إبراهيم علي', 'code' => 'STD-MAT-004', 'gender' => 'male', 'plan' => 'full', 'down_payment' => 1500.00],
        ['group' => $groupA, 'course' => $coursePython, 'phone' => '01007287005', 'name' => 'مريم محمد سعيد', 'code' => 'STD-MAT-005', 'gender' => 'female', 'plan' => 'installments', 'down_payment' => 500.00],
        ['group' => $groupA, 'course' => $coursePython, 'phone' => '01007287006', 'name' => 'سلمى شريف يوسف', 'code' => 'STD-MAT-006', 'gender' => 'female', 'plan' => 'full', 'down_payment' => 1500.00],
        ['group' => $groupA, 'course' => $coursePython, 'phone' => '01007287007', 'name' => 'حمزة طارق كمال', 'code' => 'STD-MAT-007', 'gender' => 'male', 'plan' => 'installments', 'down_payment' => 500.00],
        ['group' => $groupA, 'course' => $coursePython, 'phone' => '01007287008', 'name' => 'عبد الله ياسر حسن', 'code' => 'STD-MAT-008', 'gender' => 'male', 'plan' => 'full', 'down_payment' => 1500.00],
        ['group' => $groupA, 'course' => $coursePython, 'phone' => '01007287009', 'name' => 'جنى رامي فاروق', 'code' => 'STD-MAT-009', 'gender' => 'female', 'plan' => 'installments', 'down_payment' => 500.00],
        ['group' => $groupA, 'course' => $coursePython, 'phone' => '01007287010', 'name' => 'كريم هيثم عادل', 'code' => 'STD-MAT-010', 'gender' => 'male', 'plan' => 'installments', 'down_payment' => 500.00],
        ['group' => $groupA, 'course' => $coursePython, 'phone' => '01007287011', 'name' => 'هنا وائل سامح', 'code' => 'STD-MAT-011', 'gender' => 'female', 'plan' => 'full', 'down_payment' => 1500.00],
        ['group' => $groupA, 'course' => $coursePython, 'phone' => '01007287012', 'name' => 'نور أسامة فوزي', 'code' => 'STD-MAT-012', 'gender' => 'female', 'plan' => 'installments', 'down_payment' => 500.00],

        // GROUP B (5 Students)
        ['group' => $groupB, 'course' => $courseHtml, 'phone' => '01007287013', 'name' => 'بلال حسام متولي', 'code' => 'STD-MAT-013', 'gender' => 'male', 'plan' => 'full', 'down_payment' => 1500.00],
        ['group' => $groupB, 'course' => $courseHtml, 'phone' => '01007287014', 'name' => 'رقية ممدوح فؤاد', 'code' => 'STD-MAT-014', 'gender' => 'female', 'plan' => 'installments', 'down_payment' => 500.00],
        ['group' => $groupB, 'course' => $courseHtml, 'phone' => '01007287015', 'name' => 'فارس علاء نبيل', 'code' => 'STD-MAT-015', 'gender' => 'male', 'plan' => 'full', 'down_payment' => 1500.00],
        ['group' => $groupB, 'course' => $courseHtml, 'phone' => '01007287016', 'name' => 'ليلى حازم شوقي', 'code' => 'STD-MAT-016', 'gender' => 'female', 'plan' => 'installments', 'down_payment' => 500.00],
        // Student 17 will be used for the cancellation, settlement and refund lifecycle test
        ['group' => $groupB, 'course' => $courseHtml, 'phone' => '01007287017', 'name' => 'يحيى زكريا سليمان', 'code' => 'STD-MAT-017', 'gender' => 'male', 'plan' => 'installments', 'down_payment' => 500.00, 'for_cancellation' => true],

        // GROUP C (12 Students)
        ['group' => $groupC, 'course' => $courseScratch, 'phone' => '01007287018', 'name' => 'مالك شريف عبد الله', 'code' => 'STD-MAT-018', 'gender' => 'male', 'plan' => 'full', 'down_payment' => 1200.00],
        ['group' => $groupC, 'course' => $courseScratch, 'phone' => '01007287019', 'name' => 'فريدة أحمد مختار', 'code' => 'STD-MAT-019', 'gender' => 'female', 'plan' => 'installments', 'down_payment' => 400.00],
        ['group' => $groupC, 'course' => $courseScratch, 'phone' => '01007287020', 'name' => 'آدم محمد توفيق', 'code' => 'STD-MAT-020', 'gender' => 'male', 'plan' => 'installments', 'down_payment' => 400.00],
        ['group' => $groupC, 'course' => $courseScratch, 'phone' => '01007287021', 'name' => 'تاليا وليد رشاد', 'code' => 'STD-MAT-021', 'gender' => 'female', 'plan' => 'full', 'down_payment' => 1200.00],
        ['group' => $groupC, 'course' => $courseScratch, 'phone' => '01007287022', 'name' => 'سليم هاني ماهر', 'code' => 'STD-MAT-022', 'gender' => 'male', 'plan' => 'installments', 'down_payment' => 400.00],
        ['group' => $groupC, 'course' => $courseScratch, 'phone' => '01007287023', 'name' => 'خديجة أشرف إسماعيل', 'code' => 'STD-MAT-023', 'gender' => 'female', 'plan' => 'full', 'down_payment' => 1200.00],
        ['group' => $groupC, 'course' => $courseScratch, 'phone' => '01007287024', 'name' => 'ياسين تامر صبري', 'code' => 'STD-MAT-024', 'gender' => 'male', 'plan' => 'installments', 'down_payment' => 400.00],
        ['group' => $groupC, 'course' => $courseScratch, 'phone' => '01007287025', 'name' => 'كرمة إيهاب شكري', 'code' => 'STD-MAT-025', 'gender' => 'female', 'plan' => 'installments', 'down_payment' => 400.00],
        ['group' => $groupC, 'course' => $courseScratch, 'phone' => '01007287026', 'name' => 'إياد عمرو نجيب', 'code' => 'STD-MAT-026', 'gender' => 'male', 'plan' => 'full', 'down_payment' => 1200.00],
        ['group' => $groupC, 'course' => $courseScratch, 'phone' => '01007287027', 'name' => 'دارين حسام شلبي', 'code' => 'STD-MAT-027', 'gender' => 'female', 'plan' => 'installments', 'down_payment' => 400.00],
        ['group' => $groupC, 'course' => $courseScratch, 'phone' => '01007287028', 'name' => 'أنس ماجد وفيق', 'code' => 'STD-MAT-028', 'gender' => 'male', 'plan' => 'full', 'down_payment' => 1200.00],
        ['group' => $groupC, 'course' => $courseScratch, 'phone' => '01007287029', 'name' => 'صوفيا شادي رفعت', 'code' => 'STD-MAT-029', 'gender' => 'female', 'plan' => 'installments', 'down_payment' => 400.00],
    ];

    $createdStudents = [];

    // Also prepare a sample Parent User for Testing Parent Portal
    $parentUser = User::updateOrCreate(['username' => '01009988771'], [
        'name' => 'طارق كمال - ولي أمر زياد وحمزة',
        'phone' => '01009988771',
        'email' => 'parent.matamir@aischool.test',
        'password' => Hash::make('password'),
        'tenant_id' => 1,
        'status' => 'active',
        'is_super_admin' => false,
    ]);
    $parentUser->syncRoles(['parent']);

    foreach ($allStudentsDefs as $idx => $sDef) {
        $u = $sDef['user'] ?? User::updateOrCreate(['username' => $sDef['phone']], [
            'name' => $sDef['name'],
            'phone' => $sDef['phone'],
            'email' => "student.{$sDef['phone']}@aischool.test",
            'password' => Hash::make('password'),
            'tenant_id' => 1,
            'status' => 'active',
            'is_super_admin' => false,
        ]);
        $u->syncRoles(['student']);

        $s = $sDef['student'] ?? Student::updateOrCreate(['user_id' => $u->id], [
            'tenant_id' => 1,
            'student_code' => $sDef['code'],
            'phone' => $sDef['phone'],
            'gender' => $sDef['gender'],
            'birth_date' => Carbon::now()->subYears(12)->subMonths($idx)->toDateString(),
            'national_id' => '312' . str_pad($idx + 10, 11, '0', STR_PAD_LEFT),
            'school_name' => 'مدرسة أبو المطامير للتعليم الأساسي',
            'education_level' => 'prep',
            'status' => 'active',
        ]);

        // Link student 1 and student 7 to Parent for parent tests
        if ($idx === 0 || $idx === 6) {
            DB::table('student_parents')->updateOrInsert(
                ['user_id' => $parentUser->id, 'student_id' => $s->id],
                [
                    'relation' => 'father',
                    'is_primary' => true,
                    'can_pickup' => true,
                    'receive_notifications' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Clean any existing enrollment in this group
        Enrollment::where('student_id', $s->id)->where('group_id', $sDef['group']->id)->forceDelete();

        $coursePrice = (float) $sDef['course']->price;
        $downPayment = (float) $sDef['down_payment'];
        $remaining = $coursePrice - $downPayment;
        $isFull = ($remaining <= 0);

        // Create Enrollment
        $enrollment = Enrollment::create([
            'tenant_id' => 1,
            'student_id' => $s->id,
            'group_id' => $sDef['group']->id,
            'enrollment_code' => 'ENR-' . $sDef['code'],
            'enrollment_date' => Carbon::now()->subWeeks(3)->toDateString(),
            'total_amount' => $coursePrice,
            'paid_amount' => $downPayment,
            'vault_id' => $vaultMain->id,
            'discount' => 0.00,
            'payment_plan' => $isFull ? 'full' : 'installments',
            'installments_count' => $isFull ? 0 : 2,
            'status' => 'active',
            'start_date' => Carbon::now()->subWeeks(3)->toDateString(),
        ]);

        // Enrollment Course
        EnrollmentCourse::create([
            'enrollment_id' => $enrollment->id,
            'course_id' => $sDef['course']->id,
            'group_id' => $sDef['group']->id,
            'course_price' => $coursePrice,
            'status' => 'active',
            'current_session' => 3, // completed 3 sessions so far
            'total_sessions' => 8,
        ]);

        // Upfront Payment Receipt
        if ($downPayment > 0) {
            Payment::create([
                'tenant_id' => 1,
                'enrollment_id' => $enrollment->id,
                'student_id' => $s->id,
                'amount' => $downPayment,
                'payment_date' => Carbon::now()->subWeeks(3)->toDateString(),
                'payment_method' => 'cash',
                'vault_id' => $vaultMain->id,
                'receipt_number' => 'REC-' . $enrollment->id . '-01',
                'notes' => 'الدفعة الأولى / السداد عند التسجيل',
            ]);
        }

        // Generate Installments if not full
        if (!$isFull) {
            $instAmount = round($remaining / 2, 2);
            $inst1 = Installment::create([
                'tenant_id' => 1,
                'enrollment_id' => $enrollment->id,
                'installment_number' => 1,
                'amount' => $instAmount,
                'paid_amount' => ($idx % 3 === 0) ? $instAmount : 0, // Some paid, some pending
                'due_date' => Carbon::now()->subDays(5)->toDateString(),
                'status' => ($idx % 3 === 0) ? 'paid' : 'overdue',
            ]);

            $inst2 = Installment::create([
                'tenant_id' => 1,
                'enrollment_id' => $enrollment->id,
                'installment_number' => 2,
                'amount' => $remaining - $instAmount,
                'paid_amount' => 0,
                'due_date' => Carbon::now()->addWeeks(2)->toDateString(),
                'status' => 'pending',
            ]);
        }

        $createdStudents[] = [
            'index' => $idx + 1,
            'student_id' => $s->id,
            'user_id' => $u->id,
            'name' => $sDef['name'],
            'phone' => $sDef['phone'],
            'group' => $sDef['group']->name,
            'enrollment_id' => $enrollment->id,
            'paid_amount' => $downPayment,
            'for_cancellation' => $sDef['for_cancellation'] ?? false,
        ];
    }

    $manifest['students'] = $createdStudents;
    echo "  - Total Students Created/Configured: " . count($createdStudents) . PHP_EOL;

    // Verify Group Counts
    $cntA = Enrollment::where('group_id', $groupA->id)->where('status', 'active')->count();
    $cntB = Enrollment::where('group_id', $groupB->id)->where('status', 'active')->count();
    $cntC = Enrollment::where('group_id', $groupC->id)->where('status', 'active')->count();
    echo "  - Group A Active Count: {$cntA} (Expected: 12)" . PHP_EOL;
    echo "  - Group B Active Count: {$cntB} (Expected: 5)" . PHP_EOL;
    echo "  - Group C Active Count: {$cntC} (Expected: 12)" . PHP_EOL;

    // ---------------------------------------------------------
    // 7. SESSIONS SCHEDULE & ATTENDANCE (12 SESSIONS)
    // ---------------------------------------------------------
    echo "[7/10] Scheduling 12 Sessions (4 per group) & Recording Attendance..." . PHP_EOL;
    SessionSchedule::whereIn('group_id', [$groupA->id, $groupB->id, $groupC->id])->forceDelete();
    AttendanceSession::whereIn('group_id', [$groupA->id, $groupB->id, $groupC->id])->forceDelete();

    $groupsList = [
        ['group' => $groupA, 'day' => 'Saturday', 'time_start' => '10:00:00', 'time_end' => '12:00:00', 'room' => $room1],
        ['group' => $groupB, 'day' => 'Saturday', 'time_start' => '13:00:00', 'time_end' => '15:00:00', 'room' => $room1],
        ['group' => $groupC, 'day' => 'Sunday',   'time_start' => '10:00:00', 'time_end' => '12:00:00', 'room' => $room2],
    ];

    $createdSessions = [];

    foreach ($groupsList as $gInfo) {
        $grp = $gInfo['group'];
        $rm = $gInfo['room'];

        // Dates for the 4 sessions: 3 in the past, 1 in future
        $dates = [
            Carbon::now()->subWeeks(3)->startOfWeek()->addDays($gInfo['day'] === 'Saturday' ? 5 : 6)->toDateString(),
            Carbon::now()->subWeeks(2)->startOfWeek()->addDays($gInfo['day'] === 'Saturday' ? 5 : 6)->toDateString(),
            Carbon::now()->subWeeks(1)->startOfWeek()->addDays($gInfo['day'] === 'Saturday' ? 5 : 6)->toDateString(),
            Carbon::now()->addWeeks(1)->startOfWeek()->addDays($gInfo['day'] === 'Saturday' ? 5 : 6)->toDateString(),
        ];

        foreach ($dates as $sNum => $sDate) {
            $isPast = ($sNum < 3);

            $schedule = SessionSchedule::create([
                'tenant_id' => 1,
                'group_id' => $grp->id,
                'course_id' => $grp->course_id,
                'classroom_id' => $rm->id,
                'instructor_id' => $instProfile->id,
                'date' => $sDate,
                'day_of_week' => $gInfo['day'],
                'start_time' => $gInfo['time_start'],
                'end_time' => $gInfo['time_end'],
                'title' => "الحصة رقم " . ($sNum + 1) . " - " . $grp->name,
                'status' => $isPast ? 'completed' : 'scheduled',
                'attendance_taken' => $isPast,
                'attendance_taken_at' => $isPast ? Carbon::parse($sDate)->addHours(2) : null,
            ]);

            $attSession = null;
            if ($isPast) {
                $attSession = AttendanceSession::create([
                    'tenant_id' => 1,
                    'group_id' => $grp->id,
                    'course_id' => $grp->course_id,
                    'instructor_id' => $instProfile->id,
                    'session_schedule_id' => $schedule->id,
                    'attendance_date' => $sDate,
                    'session_notes' => "ملاحظات الحصة رقم " . ($sNum + 1),
                ]);

                // Record student attendances
                $enrolledStudents = Student::whereHas('enrollments', function ($q) use ($grp) {
                    $q->where('group_id', $grp->id);
                })->get();

                foreach ($enrolledStudents as $sIdx => $st) {
                    // Mark mostly present, a few absent, and in session 3 leave 1 student unrecorded to test the rule
                    if ($sNum === 2 && $sIdx === 0) {
                        // Genuinely unrecorded for student 0
                        continue;
                    }
                    $status = ($sIdx % 5 === 0) ? 'absent' : 'present';
                    AttendanceRecord::create([
                        'tenant_id' => 1,
                        'attendance_session_id' => $attSession->id,
                        'student_id' => $st->id,
                        'status' => $status,
                        'notes' => $status === 'absent' ? 'غياب بعذر' : 'حضور والتزام كامل',
                    ]);
                }
            }

            $createdSessions[] = [
                'group' => $grp->name,
                'session_num' => $sNum + 1,
                'schedule_id' => $schedule->id,
                'attendance_session_id' => $attSession?->id,
                'date' => $sDate,
                'status' => $schedule->status,
            ];
        }
    }

    $manifest['sessions'] = $createdSessions;
    echo "  - Total Sessions Scheduled: " . count($createdSessions) . " (12 total, 9 completed with attendance, 3 scheduled)" . PHP_EOL;

    // ---------------------------------------------------------
    // 8. NINE HOMEWORK ASSIGNMENTS & REAL SUBMISSIONS
    // ---------------------------------------------------------
    echo "[8/10] Creating 9 Homework Assignments & Realistic Submissions..." . PHP_EOL;

    // Create a real test homework submission image file
    $subDir = 'assignments/submissions';
    Storage::disk('public')->makeDirectory($subDir);
    $dummyImagePath = $subDir . '/test_homework_evidence_' . uniqid() . '.png';
    // Generate a minimal valid 1x1 PNG image
    $pngContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
    Storage::disk('public')->put($dummyImagePath, $pngContent);

    $hwDefs = [
        ['group' => $groupA, 'title' => 'واجب 1: المتغيرات والأنواع في بايثون', 'session_num' => 1, 'max_score' => 10],
        ['group' => $groupA, 'title' => 'واجب 2: الشروط والعمليات المنطقية', 'session_num' => 2, 'max_score' => 10],
        ['group' => $groupA, 'title' => 'واجب 3: حلقات التكرار For & While', 'session_num' => 3, 'max_score' => 10],

        ['group' => $groupB, 'title' => 'واجب 1: عناصر ونصوص صفحة HTML', 'session_num' => 1, 'max_score' => 10],
        ['group' => $groupB, 'title' => 'واجب 2: محددات CSS وتنسيق الألوان', 'session_num' => 2, 'max_score' => 10],
        ['group' => $groupB, 'title' => 'واجب 3: تخطيط الصفحة ونموذج الصندوق', 'session_num' => 3, 'max_score' => 10],

        ['group' => $groupC, 'title' => 'واجب 1: أحداث سكراتش وبدء الحركة', 'session_num' => 1, 'max_score' => 10],
        ['group' => $groupC, 'title' => 'واجب 2: المظاهر والأصوات التفاعلية', 'session_num' => 2, 'max_score' => 10],
        ['group' => $groupC, 'title' => 'واجب 3: تصميم لعبة المتاهة البسيطة', 'session_num' => 3, 'max_score' => 10],
    ];

    $createdHWs = [];
    $createdSubs = [];

    foreach ($hwDefs as $hIdx => $hDef) {
        $grp = $hDef['group'];
        $hw = Assignment::create([
            'tenant_id' => 1,
            'group_id' => $grp->id,
            'session_number' => $hDef['session_num'],
            'instructor_id' => $instProfile->id,
            'title' => $hDef['title'],
            'description' => "يرجى حل التمارين المرفقة ورفع صورة الكود أو لقطة الشاشة مع كتابة شرح مختصر.",
            'due_date' => Carbon::now()->addDays(3)->toDateString(),
            'max_score' => $hDef['max_score'],
            'status' => 'published',
        ]);
        $createdHWs[] = ['id' => $hw->id, 'title' => $hw->title, 'group' => $grp->name];

        // Populate student submissions for this assignment
        $studentsInGrp = Student::whereHas('enrollments', function ($q) use ($grp) {
            $q->where('group_id', $grp->id);
        })->get();

        foreach ($studentsInGrp as $sIdx => $st) {
            // Provide diverse test scenarios:
            // Student 0: Grade 9/10 (>50%), with image attachment, fully graded & published
            // Student 1: Grade 5/10 (=50%), graded & published
            // Student 2: Grade 3/10 (<50%), graded & published (triggers academic alert test)
            // Student 3: Pending grading
            // Student 4+: Graded
            $score = null;
            $graded = false;
            $publishedAt = null;

            if ($sIdx === 0) {
                $score = 9.0;
                $graded = true;
                $publishedAt = now();
            } elseif ($sIdx === 1) {
                $score = 5.0;
                $graded = true;
                $publishedAt = now();
            } elseif ($sIdx === 2) {
                $score = 3.0;
                $graded = true;
                $publishedAt = now();
            } elseif ($sIdx === 3) {
                $score = null;
                $graded = false;
                $publishedAt = null;
            } else {
                $score = 8.0;
                $graded = true;
                $publishedAt = now();
            }

            $sub = AssignmentSubmission::create([
                'tenant_id' => 1,
                'assignment_id' => $hw->id,
                'student_id' => $st->id,
                'attempt_number' => 1,
                'submission_notes' => "حل الطالب " . ($st->user->name ?? $st->student_code) . " للواجب العملي.",
                'file_path' => $dummyImagePath,
                'images' => [$dummyImagePath],
                'submitted_at' => Carbon::now()->subDays(1),
                'score' => $score,
                'graded' => $graded,
                'graded_at' => $graded ? now() : null,
                'published_at' => $publishedAt,
                'graded_by' => $graded ? $instUser->id : null,
                'instructor_feedback' => $graded ? ($score < 5 ? 'مجهود بحاجة لمراجعة الدرس' : 'أحسنت، إجابة متميزة!') : null,
                'is_official' => true,
                'status' => $graded ? 'graded' : 'submitted',
            ]);

            $createdSubs[] = [
                'sub_id' => $sub->id,
                'hw_id' => $hw->id,
                'student_id' => $st->id,
                'score' => $score,
                'status' => $sub->status,
            ];
        }
    }

    $manifest['homeworks'] = $createdHWs;
    $manifest['submissions_count'] = count($createdSubs);
    echo "  - Total Homeworks Created: " . count($createdHWs) . PHP_EOL;
    echo "  - Total Real Submissions Created: " . count($createdSubs) . PHP_EOL;

    // ---------------------------------------------------------
    // 9. SIX EXAMINATIONS (2 PER GROUP: 30-MIN & 45-MIN)
    // ---------------------------------------------------------
    echo "[9/10] Creating 6 Functional Examinations & Questions..." . PHP_EOL;
    $examConfigs = [
        ['group' => $groupA, 'course' => $coursePython, 'title' => 'اختبار قصير 1 — أساسيات بايثون (30 دقيقة)', 'duration' => 30, 'total' => 20, 'pass' => 10],
        ['group' => $groupA, 'course' => $coursePython, 'title' => 'الامتحان الشامل — بايثون المتقدم (45 دقيقة)', 'duration' => 45, 'total' => 50, 'pass' => 25],

        ['group' => $groupB, 'course' => $courseHtml, 'title' => 'اختبار قصير 1 — وسوم HTML (30 دقيقة)', 'duration' => 30, 'total' => 20, 'pass' => 10],
        ['group' => $groupB, 'course' => $courseHtml, 'title' => 'الامتحان الشامل — تصميم الويب الكامل (45 دقيقة)', 'duration' => 45, 'total' => 50, 'pass' => 25],

        ['group' => $groupC, 'course' => $courseScratch, 'title' => 'اختبار قصير 1 — لبنات سكراتش (30 دقيقة)', 'duration' => 30, 'total' => 20, 'pass' => 10],
        ['group' => $groupC, 'course' => $courseScratch, 'title' => 'الامتحان الشامل — مشاريع سكراتش المتقدمة (45 دقيقة)', 'duration' => 45, 'total' => 50, 'pass' => 25],
    ];

    $createdExams = [];
    $createdAttempts = [];

    foreach ($examConfigs as $eConf) {
        $grp = $eConf['group'];
        $crs = $eConf['course'];

        $exam = Exam::create([
            'tenant_id' => 1,
            'course_id' => $crs->id,
            'group_id' => $grp->id,
            'instructor_id' => $instProfile->id,
            'title' => $eConf['title'],
            'description' => "امتحان تقييمي إلكتروني محدد بـ {$eConf['duration']} دقيقة.",
            'type' => $eConf['duration'] == 30 ? 'quiz' : 'final',
            'duration' => $eConf['duration'],
            'total_marks' => $eConf['total'],
            'passing_marks' => $eConf['pass'],
            'exam_date' => Carbon::now()->subDays(2),
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Questions for the exam:
        // 5 questions with 4 options each (for quiz) or 10 questions (for final)
        $qCount = $eConf['duration'] == 30 ? 5 : 10;
        $ptsPerQ = $eConf['total'] / $qCount;

        $questionsList = [];
        for ($q = 1; $q <= $qCount; $q++) {
            $question = ExamQuestion::create([
                'exam_id' => $exam->id,
                'question_text' => "سؤال {$q}: ما هي النتيجة المتوقعة للعملية البرمجية في {$crs->name}؟",
                'points' => $ptsPerQ,
                'order' => $q,
            ]);

            // Options: A (correct), B, C, D
            $optA = ExamQuestionOption::create([
                'exam_question_id' => $question->id,
                'option_text' => "الخيار أ (صحيح) - النتيجة المتوقعة بدقة",
                'is_correct' => true,
                'order' => 1,
            ]);
            $optB = ExamQuestionOption::create([
                'exam_question_id' => $question->id,
                'option_text' => "الخيار ب (خطأ)",
                'is_correct' => false,
                'order' => 2,
            ]);
            $optC = ExamQuestionOption::create([
                'exam_question_id' => $question->id,
                'option_text' => "الخيار ج (خطأ)",
                'is_correct' => false,
                'order' => 3,
            ]);
            $optD = ExamQuestionOption::create([
                'exam_question_id' => $question->id,
                'option_text' => "الخيار د (خطأ)",
                'is_correct' => false,
                'order' => 4,
            ]);

            $questionsList[] = ['question' => $question, 'correct_opt' => $optA, 'wrong_opt' => $optB];
        }

        $createdExams[] = ['id' => $exam->id, 'title' => $exam->title, 'duration' => $exam->duration, 'total' => $exam->total_marks];

        // Create Real Attempts for all enrolled students in this group
        $studentsInGrp = Student::whereHas('enrollments', function ($q) use ($grp) {
            $q->where('group_id', $grp->id);
        })->get();

        foreach ($studentsInGrp as $sIdx => $st) {
            // Create completed attempt
            $attempt = ExamAttempt::create([
                'tenant_id' => 1,
                'exam_id' => $exam->id,
                'student_id' => $st->id,
                'attempt_number' => 1,
                'started_at' => Carbon::now()->subHours(5),
                'completed_at' => Carbon::now()->subHours(4)->addMinutes(15),
                'published_at' => now(),
                'time_taken' => ($eConf['duration'] - 10) * 60,
                'is_official' => true,
                'score' => 0.0,
                'total_score' => $eConf['total'],
                'percentage' => 0.0,
                'passed' => false,
            ]);

            // Fill answers
            $earnedScore = 0;
            foreach ($questionsList as $qIdx => $qData) {
                // Determine if student answers correctly
                $isCorrect = ($sIdx % 3 !== 0) || ($qIdx < ($qCount / 2));
                $selectedOpt = $isCorrect ? $qData['correct_opt'] : $qData['wrong_opt'];
                $marks = $isCorrect ? $ptsPerQ : 0;
                $earnedScore += $marks;

                ExamAnswer::create([
                    'exam_attempt_id' => $attempt->id,
                    'exam_question_id' => $qData['question']->id,
                    'exam_question_option_id' => $selectedOpt->id,
                    'is_correct' => $isCorrect,
                    'marks_obtained' => $marks,
                ]);
            }

            $attempt->score = $earnedScore;
            $attempt->total_score = $eConf['total'];
            $attempt->percentage = ($earnedScore / $eConf['total']) * 100;
            $attempt->passed = ($earnedScore >= $eConf['pass']);
            $attempt->save();

            $createdAttempts[] = [
                'exam_id' => $exam->id,
                'student_id' => $st->id,
                'score' => $attempt->score,
                'total' => $attempt->total_score,
                'passed' => $attempt->passed,
            ];

            // For student 0 (Ziyad 01007287273) on the first exam: create Retake Grant scenario
            if ($sIdx === 0 && count($createdExams) === 1) {
                // Grant a retake
                $grant = ExamRetakeGrant::create([
                    'tenant_id' => 1,
                    'exam_id' => $exam->id,
                    'student_id' => $st->id,
                    'granted_by' => $instUser->id,
                    'reason' => 'إتاحة فرصة تحسين الدرجة بناءً على طلب الطالب والمشرف',
                    'expires_at' => Carbon::now()->addDays(7),
                    'status' => 'consumed',
                    'consumed_at' => now(),
                ]);

                // Retake Attempt #2 with higher score
                $retakeAttempt = ExamAttempt::create([
                    'tenant_id' => 1,
                    'exam_id' => $exam->id,
                    'student_id' => $st->id,
                    'attempt_number' => 2,
                    'started_at' => Carbon::now()->subHours(2),
                    'completed_at' => Carbon::now()->subHours(1),
                    'published_at' => now(),
                    'time_taken' => 20 * 60,
                    'score' => 18.0,
                    'total_score' => $eConf['total'],
                    'percentage' => (18.0 / $eConf['total']) * 100,
                    'passed' => true,
                    'is_official' => true, // Highest score rule
                ]);

                // Previous attempt is marked as non-official
                $attempt->is_official = false;
                $attempt->save();

                $grant->consumed_attempt_id = $retakeAttempt->id;
                $grant->save();
            }
        }
    }

    $manifest['exams'] = $createdExams;
    $manifest['attempts_count'] = count($createdAttempts);
    echo "  - Total Exams Created: " . count($createdExams) . " (3 quizzes @ 30 min, 3 comprehensive @ 45 min)" . PHP_EOL;
    echo "  - Total Real Attempts Completed: " . count($createdAttempts) . PHP_EOL;

    // ---------------------------------------------------------
    // 10. INSTRUCTOR SALARY & DISBURSEMENT
    // ---------------------------------------------------------
    echo "[10/10] Verifying Instructor Entitlement & Executing Salary Disbursement..." . PHP_EOL;
    $completedSessionsCount = AttendanceSession::whereHas('group', function ($q) use ($instProfile) {
        $q->where('instructor_id', $instProfile->id);
    })->where('tenant_id', 1)->whereNull('salary_payment_id')->count();

    $ratePerSession = (float) $instProfile->hourly_rate; // 150 EGP
    $entitledAmount = $completedSessionsCount * $ratePerSession; // 9 * 150 = 1350 EGP

    echo "  - Completed Sessions for Instructor: {$completedSessionsCount}" . PHP_EOL;
    echo "  - Calculated Entitlement Amount: {$entitledAmount} EGP" . PHP_EOL;

    $openingVaultBalance = (float) $vaultMain->getCurrentBalance();

    // Disburse salary through SalaryPayment
    $salary = SalaryPayment::create([
        'tenant_id' => 1,
        'employee_type' => 'instructor',
        'instructor_id' => $instProfile->id,
        'amount' => $entitledAmount,
        'payment_date' => now()->toDateString(),
        'vault_id' => $vaultMain->id,
        'period_start' => Carbon::now()->subMonth()->startOfMonth()->toDateString(),
        'period_end' => Carbon::now()->endOfMonth()->toDateString(),
        'sessions_count' => $completedSessionsCount,
        'status' => 'paid',
        'notes' => "صرف مستحقات عدد {$completedSessionsCount} حصص منفذة فعلياً",
        'created_by' => $mgrMatamir->id,
        'paid_by' => $mgrMatamir->id,
    ]);

    // Mark sessions as linked to this salary payment
    AttendanceSession::whereHas('group', function ($q) use ($instProfile) {
        $q->where('instructor_id', $instProfile->id);
    })->where('tenant_id', 1)->whereNull('salary_payment_id')->update(['salary_payment_id' => $salary->id]);

    // Vault cash is automatically recalculated via getCurrentBalance()
    $closingVaultBalance = (float) $vaultMain->fresh()->getCurrentBalance();

    $manifest['salaries'] = [
        'salary_payment_id' => $salary->id,
        'instructor_id' => $instProfile->id,
        'sessions_count' => $completedSessionsCount,
        'amount' => $entitledAmount,
        'vault_id' => $vaultMain->id,
        'opening_vault_balance' => $openingVaultBalance,
        'closing_vault_balance' => $closingVaultBalance,
    ];
    echo "  - Salary Paid Successfully! ID: {$salary->id}, Vault Balance: {$openingVaultBalance} -> {$closingVaultBalance} EGP" . PHP_EOL;

    DB::commit();
    echo PHP_EOL . ">>> MASTER SIMULATION DATA SETUP COMPLETED SUCCESSFULLY! <<<" . PHP_EOL;

    file_put_contents(
        __DIR__ . '/simulation_manifest.json',
        json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR IN SIMULATION SETUP: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

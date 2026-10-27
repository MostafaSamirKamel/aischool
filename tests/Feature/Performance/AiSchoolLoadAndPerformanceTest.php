<?php

namespace Tests\Feature\Performance;

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
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AiSchoolLoadAndPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_load_and_performance_benchmark_with_600_students(): void
    {
        ini_set('memory_limit', '1024M');

        $startMemory = memory_get_usage(true);
        $overallStartTime = microtime(true);

        // =========================================================================
        // 1. SETUP 2 BRANCHES, VAULTS, INSTRUCTORS, COURSES & GROUPS
        // =========================================================================
        $branch1 = Tenant::create(['name' => 'Load Branch East', 'domain' => 'east.load.test']);
        $branch2 = Tenant::create(['name' => 'Load Branch West', 'domain' => 'west.load.test']);
        $branches = [$branch1, $branch2];

        $vaults = [];
        foreach ($branches as $branch) {
            $vaults[] = Vault::create(['tenant_id' => $branch->id, 'name' => 'Main Cash ' . $branch->id, 'type' => 'cash', 'opening_balance' => 50000.00, 'is_active' => true]);
            $vaults[] = Vault::create(['tenant_id' => $branch->id, 'name' => 'Instapay ' . $branch->id, 'type' => 'instapay', 'opening_balance' => 100000.00, 'is_active' => true]);
            $vaults[] = Vault::create(['tenant_id' => $branch->id, 'name' => 'Vodafone ' . $branch->id, 'type' => 'vodafone', 'opening_balance' => 30000.00, 'is_active' => true]);
        }

        // Instructors (6 per branch)
        $instructors = [];
        foreach ($branches as $branch) {
            for ($ins = 1; $ins <= 6; $ins++) {
                $user = User::factory()->create(['tenant_id' => $branch->id, 'name' => "Instructor {$branch->id}-{$ins}"]);
                $instructors[] = Instructor::create([
                    'tenant_id' => $branch->id,
                    'user_id' => $user->id,
                    'employee_code' => "INS-{$branch->id}-{$ins}",
                    'specialization' => 'Computer Science',
                    'hourly_rate' => 150.00,
                    'status' => 'active',
                ]);
            }
        }

        // Courses (5 per branch)
        $courses = [];
        $groups = [];
        foreach ($branches as $branch) {
            for ($c = 1; $c <= 5; $c++) {
                $course = Course::create([
                    'tenant_id' => $branch->id,
                    'name' => "Course {$branch->id}-{$c}",
                    'code' => "CRS-{$branch->id}-{$c}",
                    'price' => 1500.00,
                    'total_sessions' => 12,
                    'status' => 'active',
                ]);
                $courses[] = $course;

                // 2 Groups per course (Total 20 groups across 10 courses)
                for ($g = 1; $g <= 2; $g++) {
                    $assignedInstructor = $instructors[array_rand($instructors)];
                    $group = Group::create([
                        'tenant_id' => $branch->id,
                        'course_id' => $course->id,
                        'instructor_id' => $assignedInstructor->id,
                        'name' => "Group {$course->id}-{$g}",
                        'total_sessions' => 12,
                        'max_students' => 35,
                        'capacity' => 0,
                        'status' => 'active',
                        'start_date' => now()->subWeeks(4),
                        'end_date' => now()->addWeeks(8),
                    ]);
                    $groups[] = $group;
                }
            }
        }

        // =========================================================================
        // 2. SYNTHETIC BATCH SEEDING: 600 STUDENTS, 300 PARENTS, ENROLLMENTS & INSTALLMENTS
        // =========================================================================
        $seedStartTime = microtime(true);

        $students = [];
        $studentParents = [];
        $enrollments = [];
        $enrollmentCourses = [];
        $installments = [];
        $payments = [];

        $studentCount = 600;
        $parentCount = 300;

        // Create Parent Users in bulk
        $parentData = [];
        for ($p = 1; $p <= $parentCount; $p++) {
            $branchId = ($p % 2 === 0) ? $branch1->id : $branch2->id;
            $parentData[] = [
                'name' => "Parent $p",
                'email' => "parent_load_{$p}@aischool.test",
                'password' => bcrypt('Password123!'),
                'tenant_id' => $branchId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        User::insert($parentData);
        $parentUsers = User::where('email', 'like', 'parent_load_%@aischool.test')->get();

        // Create Student Users in bulk
        $studentUserData = [];
        for ($s = 1; $s <= $studentCount; $s++) {
            $branchId = ($s % 2 === 0) ? $branch1->id : $branch2->id;
            $studentUserData[] = [
                'name' => "Student $s",
                'email' => "student_load_{$s}@aischool.test",
                'password' => bcrypt('Password123!'),
                'tenant_id' => $branchId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        User::insert($studentUserData);
        $studentUsers = User::where('email', 'like', 'student_load_%@aischool.test')->get();

        // Create Student Profiles in bulk
        $studentProfilesData = [];
        foreach ($studentUsers as $idx => $sUser) {
            $num = $idx + 1;
            $studentProfilesData[] = [
                'tenant_id' => $sUser->tenant_id,
                'user_id' => $sUser->id,
                'student_code' => 'STU-LOAD-' . str_pad($num, 4, '0', STR_PAD_LEFT),
                'gender' => ($num % 2 === 0) ? 'female' : 'male',
                'birth_date' => '2012-04-15',
                'phone' => '010' . str_pad($num, 8, '0', STR_PAD_LEFT),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Student::insert($studentProfilesData);
        $studentsList = Student::all();

        // Link Parents to Students (2 students per parent)
        $parentLinkData = [];
        foreach ($studentsList as $idx => $student) {
            $parentIndex = intdiv($idx, 2);
            $parent = $parentUsers[$parentIndex];
            $parentLinkData[] = [
                'tenant_id' => $student->tenant_id,
                'user_id' => $parent->id,
                'student_id' => $student->id,
                'relation' => ($idx % 2 === 0) ? 'father' : 'mother',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        StudentParent::insert($parentLinkData);

        // Enrollments, Courses & Installments
        $enrollmentData = [];
        foreach ($studentsList as $idx => $student) {
            $matchingGroups = array_values(array_filter($groups, fn($g) => $g->tenant_id === $student->tenant_id));
            $group = $matchingGroups[$idx % count($matchingGroups)];

            $enrollmentData[] = [
                'tenant_id' => $student->tenant_id,
                'student_id' => $student->id,
                'group_id' => $group->id,
                'vault_id' => null,
                'total_amount' => 1500.00,
                'paid_amount' => 500.00,
                'discount' => 0.00,
                'payment_plan' => 'installments',
                'installments_count' => 2,
                'status' => 'active',
                'enrollment_code' => 'ENR-LOAD-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                'enrollment_date' => now()->subDays(25)->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        Enrollment::insert($enrollmentData);
        $enrollmentsList = Enrollment::all();

        // Enrollment Courses & Installments
        $enrollmentCoursesData = [];
        $installmentsData = [];
        $paymentsData = [];

        foreach ($enrollmentsList as $enrollment) {
            $matchingGroup = Group::find($enrollment->group_id);

            $enrollmentCoursesData[] = [
                'enrollment_id' => $enrollment->id,
                'course_id' => $matchingGroup->course_id,
                'group_id' => $matchingGroup->id,
                'status' => 'active',
                'course_price' => 1500.00,
                'total_sessions' => 12,
                'current_session' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Installment 1 (Paid 500)
            $installmentsData[] = [
                'tenant_id' => $enrollment->tenant_id,
                'enrollment_id' => $enrollment->id,
                'installment_number' => 1,
                'amount' => 500.00,
                'paid_amount' => 500.00,
                'due_date' => now()->subDays(20)->toDateString(),
                'paid_date' => now()->subDays(20)->toDateString(),
                'status' => 'paid',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Installment 2 (Pending 1,000)
            $installmentsData[] = [
                'tenant_id' => $enrollment->tenant_id,
                'enrollment_id' => $enrollment->id,
                'installment_number' => 2,
                'amount' => 1000.00,
                'paid_amount' => 0.00,
                'due_date' => now()->addDays(20)->toDateString(),
                'paid_date' => null,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Payment of 500 EGP into vault
            $branchVaults = array_values(array_filter($vaults, fn($v) => $v->tenant_id === $enrollment->tenant_id));
            $payingVault = $branchVaults[array_rand($branchVaults)];

            $paymentsData[] = [
                'tenant_id' => $enrollment->tenant_id,
                'enrollment_id' => $enrollment->id,
                'vault_id' => $payingVault->id,
                'payment_code' => 'PAY-LOAD-' . uniqid(),
                'amount' => 500.00,
                'payment_method' => 'cash',
                'payment_date' => now()->subDays(20)->toDateString(),
                'status' => 'completed',
                'received_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        EnrollmentCourse::insert($enrollmentCoursesData);
        Installment::insert($installmentsData);
        Payment::insert($paymentsData);

        // Attendance Sessions & Records (4 sessions per group)
        $attendanceSessionsData = [];
        foreach ($groups as $grp) {
            for ($s = 1; $s <= 4; $s++) {
                $attendanceSessionsData[] = [
                    'tenant_id' => $grp->tenant_id,
                    'group_id' => $grp->id,
                    'course_id' => $grp->course_id,
                    'attendance_date' => now()->subDays(20 - $s * 4)->toDateString(),
                    'session_notes' => "Session $s",
                    'recorded_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        AttendanceSession::insert($attendanceSessionsData);
        $sessionsList = AttendanceSession::all();

        // Bulk attendance records
        $attendanceRecordsData = [];
        foreach ($sessionsList as $sess) {
            $grpEnrollments = EnrollmentCourse::where('group_id', $sess->group_id)->pluck('enrollment_id');
            $studentIds = Enrollment::whereIn('id', $grpEnrollments)->pluck('student_id');

            foreach ($studentIds as $stdId) {
                $attendanceRecordsData[] = [
                    'attendance_session_id' => $sess->id,
                    'student_id' => $stdId,
                    'status' => 'present',
                    'notes' => 'Attended',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        AttendanceRecord::insert($attendanceRecordsData);

        $seedDuration = microtime(true) - $seedStartTime;

        // Verify counts
        expect(Student::count())->toBe(600);
        expect(Enrollment::count())->toBe(600);
        expect(Installment::count())->toBe(1200);
        expect(Payment::count())->toBe(600);
        expect(AttendanceRecord::count())->toBeGreaterThan(2000);

        // =========================================================================
        // 3. BENCHMARK WORKLOAD EXECUTION & METRICS COLLECTION
        // =========================================================================
        $benchmarks = [];

        // Benchmark 1: Student Dashboard Queries (100 sample students)
        $sampleStudents = Student::take(100)->get();
        $studentDashboardTimes = [];
        DB::flushQueryLog();
        DB::enableQueryLog();

        foreach ($sampleStudents as $s) {
            $t0 = microtime(true);
            $activeEnrollments = $s->enrollments()
                ->where('status', 'active')
                ->with(['enrollmentCourses.course', 'enrollmentCourses.group'])
                ->get();

            $attendanceCount = AttendanceRecord::where('student_id', $s->id)->where('status', 'present')->count();
            $balance = $s->enrollments()->get()->sum(fn($e) => $e->getRemainingBalance());
            $studentDashboardTimes[] = (microtime(true) - $t0) * 1000; // ms
        }
        $benchmarks['student_dashboard'] = $this->calculateLatencyStats($studentDashboardTimes);

        // Benchmark 2: Parent Portal Multi-Child Overview (50 sample parents)
        $sampleParents = User::where('email', 'like', 'parent_load_%')->take(50)->get();
        $parentTimes = [];
        foreach ($sampleParents as $p) {
            $t0 = microtime(true);
            $children = Student::whereHas('parents', fn($q) => $q->where('user_id', $p->id))
                ->with(['enrollments' => fn($q) => $q->where('status', 'active')])
                ->get();
            $parentTimes[] = (microtime(true) - $t0) * 1000;
        }
        $benchmarks['parent_portal'] = $this->calculateLatencyStats($parentTimes);

        // Benchmark 3: Financial Vault Reconciliation (All 6 vaults)
        $vaultTimes = [];
        foreach ($vaults as $v) {
            $t0 = microtime(true);
            $bal = $v->getCurrentBalance();
            $vaultTimes[] = (microtime(true) - $t0) * 1000;
            expect($bal)->toBeGreaterThan(0.0);
        }
        $benchmarks['vault_reconciliation'] = $this->calculateLatencyStats($vaultTimes);

        // Benchmark 4: Global Overdue Installments & Financial Aggregation Query
        $t0 = microtime(true);
        $overdueCount = Installment::where('status', 'overdue')
            ->orWhere(fn($q) => $q->where('status', 'pending')->whereDate('due_date', '<', now()))
            ->count();
        $totalCollected = Payment::where('status', 'completed')->sum('amount');
        $financialReportTime = (microtime(true) - $t0) * 1000;
        $benchmarks['financial_reporting'] = ['time_ms' => $financialReportTime, 'total_collected' => $totalCollected];

        // Benchmark 5: Search Queries across 600 students (by name, code, phone)
        $searchTimes = [];
        for ($k = 1; $k <= 30; $k++) {
            $searchCode = 'STU-LOAD-' . str_pad(rand(1, 600), 4, '0', STR_PAD_LEFT);
            $t0 = microtime(true);
            $found = Student::where('student_code', $searchCode)
                ->orWhere('phone', 'like', "%$k%")
                ->with('user', 'enrollments')
                ->first();
            $searchTimes[] = (microtime(true) - $t0) * 1000;
            expect($found)->not->toBeNull();
        }
        $benchmarks['student_search'] = $this->calculateLatencyStats($searchTimes);

        // Measure Peak Memory & Duration
        $peakMemoryMb = memory_get_peak_usage(true) / 1024 / 1024;
        $totalDuration = microtime(true) - $overallStartTime;

        // Output summary to screen for report inclusion
        echo PHP_EOL . "=== LOAD TESTING BENCHMARK RESULTS (600 STUDENTS) ===" . PHP_EOL;
        echo "Seeding Duration: " . round($seedDuration, 2) . "s" . PHP_EOL;
        echo "Peak Memory: " . round($peakMemoryMb, 2) . " MB" . PHP_EOL;
        echo "Student Dashboard (100 ops) - P50: {$benchmarks['student_dashboard']['p50']}ms, P95: {$benchmarks['student_dashboard']['p95']}ms" . PHP_EOL;
        echo "Parent Portal (50 ops) - P50: {$benchmarks['parent_portal']['p50']}ms, P95: {$benchmarks['parent_portal']['p95']}ms" . PHP_EOL;
        echo "Vault Reconciliation (6 vaults) - Avg: {$benchmarks['vault_reconciliation']['mean']}ms" . PHP_EOL;
        echo "Financial Report Query: " . round($financialReportTime, 2) . "ms (Total Collected: {$totalCollected} EGP)" . PHP_EOL;
        echo "Student Search (30 ops) - P50: {$benchmarks['student_search']['p50']}ms, P95: {$benchmarks['student_search']['p95']}ms" . PHP_EOL;
        echo "======================================================" . PHP_EOL;

        // Assert performance thresholds
        expect($benchmarks['student_dashboard']['p95'])->toBeLessThan(100.0); // P95 under 100ms
        expect($benchmarks['parent_portal']['p95'])->toBeLessThan(100.0);
        expect($benchmarks['student_search']['p95'])->toBeLessThan(50.0);
        expect($financialReportTime)->toBeLessThan(250.0);
    }

    private function calculateLatencyStats(array $times): array
    {
        sort($times);
        $count = count($times);
        $mean = array_sum($times) / $count;
        $p50Index = (int) floor($count * 0.50);
        $p90Index = (int) floor($count * 0.90);
        $p95Index = (int) floor($count * 0.95);

        return [
            'count' => $count,
            'mean' => round($mean, 2),
            'p50' => round($times[$p50Index], 2),
            'p90' => round($times[$p90Index], 2),
            'p95' => round($times[min($p95Index, $count - 1)], 2),
            'min' => round(min($times), 2),
            'max' => round(max($times), 2),
        ];
    }
}

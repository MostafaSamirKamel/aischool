<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Course;
use App\Models\Group;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\EnrollmentCancellation;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\ExamAttempt;
use App\Models\ExamAnswer;
use App\Models\Grade;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Vault;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\VaultTransfer;
use App\Models\SalaryPayment;
use App\Models\StudentFollowUpTask;
use App\Models\StudentFollowUpContact;
use App\Models\SupervisoryObservation;
use App\Models\InstructorEvaluation;
use App\Models\InstructorEvaluationAppeal;
use App\Models\SupervisionReport;

class PopulateExistingStudentsDemoData extends Command
{
    protected $signature = 'ai-school:populate-demo-data {--dry-run : Preview actions without persisting} {--cleanup : Remove demo records tagged with AI_SCHOOL_PERSISTENT_DEMO}';
    protected $description = 'Populate all existing screens with realistic, persistent, connected demo records starting with existing students';

    public const TAG = 'AI_SCHOOL_PERSISTENT_DEMO';

    public function handle()
    {
        $this->info("========================================================================");
        $this->info("AI SCHOOL — PERSISTENT DATABASE POPULATION FOR EXISTING STUDENTS");
        $this->info("Tag: " . self::TAG);
        $this->info("========================================================================");

        if ($this->option('cleanup')) {
            return $this->handleCleanup();
        }

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn(">>> RUNNING IN DRY-RUN MODE: No changes will be saved to the database <<<");
        }

        $students = Student::with(['user', 'enrollments.group', 'parents'])->get();
        $this->info("Found " . $students->count() . " existing students in database.");

        $tenants = Tenant::all();
        $this->info("Found " . $tenants->count() . " active branches/tenants.");

        // Distribution of scenarios
        $scenarioCounts = [
            'A' => 0, // High achiever / fully paid
            'B' => 0, // Academic alert / follow-up / partial pay
            'C' => 0, // Multi-course enrollment
            'D' => 0, // Renewal enrollment
            'E' => 0, // Canceled / refund settlement
            'F' => 0, // General active
        ];

        $manifest = [
            'created_enrollments' => 0,
            'created_parents' => 0,
            'created_sessions' => 0,
            'created_attendance' => 0,
            'created_assignments' => 0,
            'created_submissions' => 0,
            'created_exams' => 0,
            'created_questions' => 0,
            'created_attempts' => 0,
            'created_grades' => 0,
            'created_installments' => 0,
            'created_payments' => 0,
            'created_followups' => 0,
            'created_contacts' => 0,
            'created_observations' => 0,
            'created_evaluations' => 0,
            'created_supervision_reports' => 0,
            'created_expenses' => 0,
            'created_revenues' => 0,
            'created_transfers' => 0,
            'created_salary_payments' => 0,
        ];

        DB::beginTransaction();

        try {
            // Step 1: Ensure Parents & Guardians for all existing students
            $this->info("\n--- Phase 1: Ensuring Parent Links for Existing Students ---");
            $defaultParentUser = User::role('parent')->first();
            foreach ($students as $student) {
                if ($student->parents->count() === 0) {
                    if (!$dryRun) {
                        $parentUser = User::where('tenant_id', $student->tenant_id)->role('parent')->first() ?? $defaultParentUser;
                        if ($parentUser) {
                            StudentParent::firstOrCreate([
                                'student_id' => $student->id,
                                'user_id' => $parentUser->id,
                            ], [
                                'relation' => 'father',
                                'is_primary' => true,
                            ]);
                            $manifest['created_parents']++;
                        }
                    } else {
                        $manifest['created_parents']++;
                    }
                }
            }
            $this->info("Parent links checked/updated: " . $manifest['created_parents']);

            // Step 2: Ensure Enrollments for Students without enrollments
            $this->info("\n--- Phase 2: Ensuring Valid Enrollments for All Students ---");
            $groupsByTenant = Group::with('course')->get()->groupBy('tenant_id');

            foreach ($students as $student) {
                $tenantId = $student->tenant_id ?? 1;
                $branchGroups = $groupsByTenant->get($tenantId) ?? $groupsByTenant->first();

                if ($student->enrollments->count() === 0) {
                    $selectedGroup = $branchGroups->random();
                    $course = $selectedGroup->course;
                    $coursePrice = $course ? (float) $course->price : 2000.00;

                    if (!$dryRun) {
                        $enrollment = Enrollment::create([
                            'tenant_id' => $tenantId,
                            'student_id' => $student->id,
                            'group_id' => $selectedGroup->id,
                            'enrollment_code' => 'ENR-DEMO-' . $student->id . '-' . rand(100, 999),
                            'enrollment_date' => now()->subDays(rand(10, 60)),
                            'total_amount' => $coursePrice,
                            'paid_amount' => 0.00,
                            'status' => 'active',
                            'notes' => self::TAG . ' - تسجيل تمهيدي تجريبي للطالب القائم',
                        ]);

                        if ($course) {
                            EnrollmentCourse::firstOrCreate([
                                'enrollment_id' => $enrollment->id,
                                'course_id' => $course->id,
                            ], [
                                'group_id' => $selectedGroup->id,
                                'course_price' => $coursePrice,
                                'status' => 'active',
                                'total_sessions' => 12,
                                'current_session' => 3,
                            ]);
                        }
                        $manifest['created_enrollments']++;
                    } else {
                        $manifest['created_enrollments']++;
                    }
                }
            }

            // Reload students with all enrollments and groups
            $students = Student::with(['user', 'enrollments.group.course', 'parents'])->get();

            // Step 3: Populate Sessions & Attendance across ALL Groups with Enrollments
            $this->info("\n--- Phase 3: Populating Educational Sessions & Attendance ---");
            $allGroups = Group::with('course')->get();

            foreach ($allGroups as $group) {
                // Get all enrollments for this group directly
                $groupEnrollments = Enrollment::withoutGlobalScopes()
                    ->where('group_id', $group->id)
                    ->with('student.user')
                    ->get();

                if ($groupEnrollments->count() === 0) {
                    continue;
                }

                $tenantId = $group->tenant_id ?? 1;
                $instructorId = $group->instructor_id ?? 4;

                // Create a schedule of 3 completed sessions in the past
                for ($i = 3; $i >= 1; $i--) {
                    $sessionDate = now()->subDays($i * 7 + 2)->toDateString();

                    $existingSession = AttendanceSession::withoutGlobalScopes()
                        ->where('group_id', $group->id)
                        ->whereDate('attendance_date', $sessionDate)
                        ->first();

                    if (!$existingSession && !$dryRun) {
                        $session = AttendanceSession::create([
                            'tenant_id' => $tenantId,
                            'group_id' => $group->id,
                            'course_id' => $group->course_id,
                            'attendance_date' => $sessionDate,
                            'classroom' => 'قاعة ' . ($group->id % 5 + 101),
                            'session_notes' => self::TAG . " - جلسة تدريبية معتمدة رقم {$i}",
                            'recorded_by' => $instructorId,
                        ]);
                        $manifest['created_sessions']++;

                        // Record attendance for group students
                        foreach ($groupEnrollments as $idx => $enr) {
                            if (!$enr->student) continue;

                            // Scenarios for attendance: 85% present, 10% absent, 5% late
                            $status = ($idx % 5 === 0 && $i === 2) ? 'absent' : (($idx % 7 === 0) ? 'late' : 'present');

                            AttendanceRecord::firstOrCreate([
                                'attendance_session_id' => $session->id,
                                'student_id' => $enr->student->id,
                            ], [
                                'status' => $status,
                                'check_in_time' => ($status === 'late') ? '16:30' : '16:00',
                                'check_out_time' => '18:00',
                                'commitment_score' => ($status === 'present') ? 5 : 3,
                                'focus_score' => ($status === 'present') ? 5 : 3,
                                'participation_score' => ($status === 'present') ? 4 : 2,
                                'notes' => self::TAG . ' - رصد حضور فعلي تجريبي',
                            ]);
                            $manifest['created_attendance']++;
                        }
                    } elseif (!$existingSession && $dryRun) {
                        $manifest['created_sessions']++;
                        $manifest['created_attendance'] += $groupEnrollments->count();
                    }
                }

                // Add 1 upcoming session (future date, no attendance recorded yet)
                $futureDate = now()->addDays(($group->id % 7) + 2)->toDateString();
                $existingFutureSession = AttendanceSession::withoutGlobalScopes()
                    ->where('group_id', $group->id)
                    ->whereDate('attendance_date', $futureDate)
                    ->first();

                if (!$existingFutureSession && !$dryRun) {
                    AttendanceSession::create([
                        'tenant_id' => $tenantId,
                        'group_id' => $group->id,
                        'course_id' => $group->course_id,
                        'attendance_date' => $futureDate,
                        'classroom' => 'قاعة ' . ($group->id % 5 + 101),
                        'session_notes' => self::TAG . " - جلسة قادمة مجدولة (لم يتم رصد الحضور بعد)",
                        'recorded_by' => $instructorId,
                    ]);
                    $manifest['created_sessions']++;
                }
            }

            // Step 4: Populate Homework & Submissions
            $this->info("\n--- Phase 4: Populating Homework & Student Submissions ---");
            foreach ($allGroups as $group) {
                $groupEnrollments = Enrollment::withoutGlobalScopes()
                    ->where('group_id', $group->id)
                    ->with('student.user')
                    ->get();

                if ($groupEnrollments->count() === 0) continue;

                $tenantId = $group->tenant_id ?? 1;
                $instructorId = $group->instructor_id ?? 4;

                $assignment = Assignment::withoutGlobalScopes()
                    ->where('group_id', $group->id)
                    ->where('title', 'like', '%' . self::TAG . '%')
                    ->first();

                if (!$assignment && !$dryRun) {
                    $assignment = Assignment::create([
                        'tenant_id' => $tenantId,
                        'group_id' => $group->id,
                        'instructor_id' => $instructorId,
                        'title' => "واجب تطبيقي لمجموعة " . $group->name . " [" . self::TAG . "]",
                        'description' => "يرجى حل التدريبات العملية المرفقة وتسليم الحلول بصيغة PDF أو كود مصدري.",
                        'due_date' => now()->subDays(5),
                        'max_score' => 10.00,
                        'status' => 'active',
                    ]);
                    $manifest['created_assignments']++;

                    // Create submissions for group students
                    foreach ($groupEnrollments as $idx => $enr) {
                        if (!$enr->student) continue;

                        $isLate = ($idx % 4 === 1);
                        $isLowScore = ($idx % 4 === 2);
                        $score = $isLowScore ? 4.00 : ($isLate ? 7.50 : 9.50);

                        AssignmentSubmission::firstOrCreate([
                            'assignment_id' => $assignment->id,
                            'student_id' => $enr->student->id,
                        ], [
                            'tenant_id' => $tenantId,
                            'submission_notes' => self::TAG . " - تسليم حل الطالب " . ($enr->student->user?->name ?? ''),
                            'submitted_at' => $isLate ? now()->subDays(2) : now()->subDays(6),
                            'score' => $score,
                            'instructor_feedback' => $isLowScore ? "يحتاج إلى مراجعة الأساسيات والتركيز" : "إجابة ممتازة وحل منظم",
                            'graded_at' => now()->subDays(1),
                            'published_at' => now()->subDays(1),
                            'graded_by' => $instructorId,
                            'graded' => true,
                            'status' => $isLate ? 'late' : 'submitted',
                        ]);
                        $manifest['created_submissions']++;
                    }
                } elseif (!$assignment && $dryRun) {
                    $manifest['created_assignments']++;
                    $manifest['created_submissions'] += $groupEnrollments->count();
                }
            }

            // Step 5: Populate Examinations, Questions, Attempts & Grades
            $this->info("\n--- Phase 5: Populating Examinations, Questions, Attempts & Grades ---");
            foreach ($allGroups as $group) {
                $groupEnrollments = Enrollment::withoutGlobalScopes()
                    ->where('group_id', $group->id)
                    ->with('student.user')
                    ->get();

                if ($groupEnrollments->count() === 0) continue;

                $tenantId = $group->tenant_id ?? 1;
                $instructorId = $group->instructor_id ?? 4;

                $exam = Exam::withoutGlobalScopes()
                    ->where('group_id', $group->id)
                    ->where('title', 'like', '%' . self::TAG . '%')
                    ->first();

                if (!$exam && !$dryRun) {
                    $exam = Exam::create([
                        'tenant_id' => $tenantId,
                        'course_id' => $group->course_id,
                        'group_id' => $group->id,
                        'instructor_id' => $instructorId,
                        'title' => "الاختبار الشامل لمجموعة " . $group->name . " [" . self::TAG . "]",
                        'description' => "اختبار تقييمي رسمي يشمل أسئلة اختيار من متعدد وأسئلة تطبيقية.",
                        'type' => 'midterm',
                        'exam_date' => now()->subDays(4),
                        'duration' => 60,
                        'total_marks' => 20.00,
                        'passing_marks' => 10.00,
                        'status' => 'completed',
                        'instructions' => self::TAG . " - التزم بالوقت المحدد ولا تغلق المتصفح أثناء الإجابة.",
                    ]);
                    $manifest['created_exams']++;

                    // Create questions
                    $q1 = ExamQuestion::create([
                        'exam_id' => $exam->id,
                        'question_text' => 'ما هو المفهوم الأساسي للبرمجة كائنية التوجه (OOP)؟',
                        'points' => 10.00,
                        'order' => 1,
                    ]);
                    ExamQuestionOption::create(['exam_question_id' => $q1->id, 'option_text' => 'تغليف البيانات والوظائف داخل كائنات', 'is_correct' => true, 'order' => 1]);
                    ExamQuestionOption::create(['exam_question_id' => $q1->id, 'option_text' => 'كتابة الأكواد في ملف نصي واحد', 'is_correct' => false, 'order' => 2]);
                    ExamQuestionOption::create(['exam_question_id' => $q1->id, 'option_text' => 'إلغاء المتغيرات البرمجية', 'is_correct' => false, 'order' => 3]);

                    $q2 = ExamQuestion::create([
                        'exam_id' => $exam->id,
                        'question_text' => 'هل تدعم الخوارزميات الذكية التعلم من البيانات السابقة؟',
                        'points' => 10.00,
                        'order' => 2,
                    ]);
                    ExamQuestionOption::create(['exam_question_id' => $q2->id, 'option_text' => 'نعم، عبر تدريب النماذج الإحصائية', 'is_correct' => true, 'order' => 1]);
                    ExamQuestionOption::create(['exam_question_id' => $q2->id, 'option_text' => 'لا، تعمل دون أي بيانات', 'is_correct' => false, 'order' => 2]);
                    $manifest['created_questions'] += 2;

                    // Attempts & Grades for group students
                    foreach ($groupEnrollments as $idx => $enr) {
                        if (!$enr->student) continue;

                        $isFail = ($idx % 5 === 2);
                        $score = $isFail ? 6.00 : ($idx % 2 === 0 ? 18.00 : 14.00);
                        $passed = ($score >= 10.00);

                        ExamAttempt::firstOrCreate([
                            'exam_id' => $exam->id,
                            'student_id' => $enr->student->id,
                        ], [
                            'tenant_id' => $tenantId,
                            'score' => $score,
                            'total_score' => 20.00,
                            'percentage' => ($score / 20.00) * 100,
                            'passed' => $passed,
                            'started_at' => now()->subDays(4)->setTime(10, 0),
                            'completed_at' => now()->subDays(4)->setTime(10, 45),
                            'time_taken' => 45,
                        ]);
                        $manifest['created_attempts']++;

                        Grade::firstOrCreate([
                            'exam_id' => $exam->id,
                            'student_id' => $enr->student->id,
                        ], [
                            'tenant_id' => $tenantId,
                            'enrollment_id' => $enr->id,
                            'marks_obtained' => $score,
                            'remarks' => self::TAG . ($passed ? ' - أداء أكاديمي متميز' : ' - درجات متدنية تستوجب المتابعة'),
                            'graded_by' => $instructorId,
                            'graded_at' => now()->subDays(3),
                        ]);
                        $manifest['created_grades']++;
                    }
                } elseif (!$exam && $dryRun) {
                    $manifest['created_exams']++;
                    $manifest['created_questions'] += 2;
                    $manifest['created_attempts'] += $groupEnrollments->count();
                    $manifest['created_grades'] += $groupEnrollments->count();
                }
            }

            // Step 6: Populate Financial Transactions, Installments & Collections
            $this->info("\n--- Phase 6: Populating Installments, Payments & Vault Transactions ---");
            $primaryVault = Vault::where('tenant_id', 1)->first() ?? Vault::first();

            foreach ($students as $idx => $student) {
                $enrollment = $student->enrollments->first();
                if (!$enrollment) continue;

                $tenantId = $enrollment->tenant_id ?? 1;
                $vault = Vault::where('tenant_id', $tenantId)->first() ?? $primaryVault;
                $amount = (float) $enrollment->total_amount > 0 ? (float) $enrollment->total_amount : 2400.00;
                $half = $amount / 2;

                $installmentsCount = Installment::withoutGlobalScopes()->where('enrollment_id', $enrollment->id)->count();

                if ($installmentsCount === 0) {
                    if ($idx % 4 === 0) {
                        $scenarioCounts['A']++;
                        if (!$dryRun) {
                            Installment::create([
                                'tenant_id' => $tenantId,
                                'enrollment_id' => $enrollment->id,
                                'installment_number' => 1,
                                'amount' => $half,
                                'paid_amount' => $half,
                                'due_date' => now()->subDays(30),
                                'paid_date' => now()->subDays(30),
                                'status' => 'paid',
                                'notes' => self::TAG . ' - قسط أول مسدد بالكامل',
                            ]);
                            Installment::create([
                                'tenant_id' => $tenantId,
                                'enrollment_id' => $enrollment->id,
                                'installment_number' => 2,
                                'amount' => $half,
                                'paid_amount' => $half,
                                'due_date' => now()->subDays(5),
                                'paid_date' => now()->subDays(5),
                                'status' => 'paid',
                                'notes' => self::TAG . ' - قسط ثان مسدد بالكامل',
                            ]);
                            $manifest['created_installments'] += 2;

                            Payment::create([
                                'tenant_id' => $tenantId,
                                'enrollment_id' => $enrollment->id,
                                'vault_id' => $vault->id,
                                'amount' => $amount,
                                'payment_method' => 'cash',
                                'payment_date' => now()->subDays(5),
                                'status' => 'completed',
                                'notes' => self::TAG . ' - سداد كامل لقيمة الاشتراك',
                            ]);
                            $manifest['created_payments']++;
                            $enrollment->update(['paid_amount' => $amount]);
                        } else {
                            $manifest['created_installments'] += 2;
                            $manifest['created_payments']++;
                        }
                    } elseif ($idx % 4 === 1) {
                        $scenarioCounts['B']++;
                        if (!$dryRun) {
                            Installment::create([
                                'tenant_id' => $tenantId,
                                'enrollment_id' => $enrollment->id,
                                'installment_number' => 1,
                                'amount' => $half,
                                'paid_amount' => $half,
                                'due_date' => now()->subDays(15),
                                'paid_date' => now()->subDays(15),
                                'status' => 'paid',
                                'notes' => self::TAG . ' - قسط أول مسدد',
                            ]);
                            Installment::create([
                                'tenant_id' => $tenantId,
                                'enrollment_id' => $enrollment->id,
                                'installment_number' => 2,
                                'amount' => $half,
                                'paid_amount' => 0.00,
                                'due_date' => now()->addDays(20),
                                'status' => 'pending',
                                'notes' => self::TAG . ' - قسط مستقبلي متبقي',
                            ]);
                            $manifest['created_installments'] += 2;

                            Payment::create([
                                'tenant_id' => $tenantId,
                                'enrollment_id' => $enrollment->id,
                                'vault_id' => $vault->id,
                                'amount' => $half,
                                'payment_method' => 'cash',
                                'payment_date' => now()->subDays(15),
                                'status' => 'completed',
                                'notes' => self::TAG . ' - سداد دفعة مقدمة جزئية',
                            ]);
                            $manifest['created_payments']++;
                            $enrollment->update(['paid_amount' => $half]);
                        } else {
                            $manifest['created_installments'] += 2;
                            $manifest['created_payments']++;
                        }
                    } elseif ($idx % 4 === 2) {
                        $scenarioCounts['B']++;
                        if (!$dryRun) {
                            Installment::create([
                                'tenant_id' => $tenantId,
                                'enrollment_id' => $enrollment->id,
                                'installment_number' => 1,
                                'amount' => $half,
                                'paid_amount' => $half,
                                'due_date' => now()->subDays(40),
                                'paid_date' => now()->subDays(40),
                                'status' => 'paid',
                                'notes' => self::TAG . ' - قسط أول مسدد',
                            ]);
                            Installment::create([
                                'tenant_id' => $tenantId,
                                'enrollment_id' => $enrollment->id,
                                'installment_number' => 2,
                                'amount' => $half,
                                'paid_amount' => 0.00,
                                'due_date' => now()->subDays(10),
                                'status' => 'overdue',
                                'notes' => self::TAG . ' - قسط متأخر السداد',
                            ]);
                            $manifest['created_installments'] += 2;

                            Payment::create([
                                'tenant_id' => $tenantId,
                                'enrollment_id' => $enrollment->id,
                                'vault_id' => $vault->id,
                                'amount' => $half,
                                'payment_method' => 'cash',
                                'payment_date' => now()->subDays(40),
                                'status' => 'completed',
                                'notes' => self::TAG . ' - سداد دفعة أولى',
                            ]);
                            $manifest['created_payments']++;
                            $enrollment->update(['paid_amount' => $half]);
                        } else {
                            $manifest['created_installments'] += 2;
                            $manifest['created_payments']++;
                        }
                    } else {
                        $scenarioCounts['E']++;
                        if (!$dryRun) {
                            Installment::create([
                                'tenant_id' => $tenantId,
                                'enrollment_id' => $enrollment->id,
                                'installment_number' => 1,
                                'amount' => $half,
                                'paid_amount' => $half,
                                'due_date' => now()->subDays(25),
                                'paid_date' => now()->subDays(25),
                                'status' => 'paid',
                                'notes' => self::TAG . ' - قسط مسدد قبل الإلغاء',
                            ]);
                            Installment::create([
                                'tenant_id' => $tenantId,
                                'enrollment_id' => $enrollment->id,
                                'installment_number' => 2,
                                'amount' => $half,
                                'paid_amount' => 0.00,
                                'due_date' => now()->addDays(5),
                                'status' => 'cancelled',
                                'notes' => self::TAG . ' - قسط ملغى ضمن التسوية',
                            ]);
                            $manifest['created_installments'] += 2;

                            Payment::create([
                                'tenant_id' => $tenantId,
                                'enrollment_id' => $enrollment->id,
                                'vault_id' => $vault->id,
                                'amount' => $half,
                                'payment_method' => 'cash',
                                'payment_date' => now()->subDays(25),
                                'status' => 'completed',
                                'notes' => self::TAG . ' - دفعة مسددة قبل الإلغاء',
                            ]);
                            $manifest['created_payments']++;

                            $waived = $half;
                            $retained = 300.00;
                            $refundable = $half - $retained;

                            $enrollment->update([
                                'status' => 'cancelled',
                                'cancelled_at' => now()->subDays(5),
                                'cancellation_reason' => 'انتقال السكن بناءً على طلب ولي الأمر',
                                'cancellation_notes' => self::TAG . ' - إلغاء معتمد مع تسوية مالية',
                                'settlement_status' => 'settled',
                                'settlement_type' => 'partial_refund',
                                'waived_amount' => $waived,
                                'retained_amount' => $retained,
                                'refundable_amount' => $refundable,
                                'refunded_amount' => $refundable,
                                'refund_status' => 'completed',
                                'refund_vault_id' => $vault->id,
                                'refund_payment_method' => 'cash',
                                'refunded_at' => now()->subDays(4),
                            ]);

                            EnrollmentCancellation::firstOrCreate([
                                'enrollment_id' => $enrollment->id,
                            ], [
                                'student_id' => $student->id,
                                'tenant_id' => $tenantId,
                                'cancellation_reason' => 'طلب ولي الأمر لظروف الانتقال',
                                'cancellation_notes' => self::TAG . ' - تسوية معتمدة واسترداد نقدي',
                                'settlement_type' => 'partial_refund',
                                'settlement_status' => 'settled',
                                'waived_amount' => $waived,
                                'retained_amount' => $retained,
                                'refundable_amount' => $refundable,
                                'refunded_amount' => $refundable,
                                'refund_status' => 'completed',
                            ]);
                        } else {
                            $manifest['created_installments'] += 2;
                            $manifest['created_payments']++;
                        }
                    }
                } else {
                    // Already has installments, map based on existing status
                    if ($enrollment->status === 'cancelled') {
                        $scenarioCounts['E']++;
                    } elseif ($enrollment->isFullyPaid()) {
                        $scenarioCounts['A']++;
                    } else {
                        $scenarioCounts['B']++;
                    }
                }
            }

            // Step 7: Multi-Course & Renewal Enrollments (Scenarios C & D)
            $this->info("\n--- Phase 7: Populating Dual Enrollments & Renewals ---");
            $eligibleStudents = $students->where('status', 'active')->values();

            // Scenario C: Dual Course Enrollments for 10 students
            for ($k = 0; $k < min(10, $eligibleStudents->count()); $k++) {
                $student = $eligibleStudents[$k];
                $tenantId = $student->tenant_id ?? 1;
                $branchGroups = $groupsByTenant->get($tenantId) ?? $groupsByTenant->first();

                if ($branchGroups->count() >= 2) {
                    $secondGroup = $branchGroups->where('id', '!=', $student->enrollments->first()?->group_id)->first();
                    if ($secondGroup && $student->enrollments->where('group_id', $secondGroup->id)->count() === 0) {
                        $scenarioCounts['C']++;
                        if (!$dryRun) {
                            $secondEnr = Enrollment::create([
                                'tenant_id' => $tenantId,
                                'student_id' => $student->id,
                                'group_id' => $secondGroup->id,
                                'enrollment_code' => 'ENR-MULTI-' . $student->id . '-' . rand(100, 999),
                                'enrollment_date' => now()->subDays(15),
                                'total_amount' => 1800.00,
                                'paid_amount' => 1800.00,
                                'status' => 'active',
                                'notes' => self::TAG . ' - اشتراك ثان في مسار تكميلي',
                            ]);
                            EnrollmentCourse::firstOrCreate([
                                'enrollment_id' => $secondEnr->id,
                                'course_id' => $secondGroup->course_id,
                            ], [
                                'group_id' => $secondGroup->id,
                                'course_price' => 1800.00,
                                'status' => 'active',
                                'total_sessions' => 8,
                                'current_session' => 2,
                            ]);
                            $manifest['created_enrollments']++;
                        } else {
                            $manifest['created_enrollments']++;
                        }
                    }
                }
            }

            // Scenario D: Term Renewals for 10 students
            for ($m = 10; $m < min(20, $eligibleStudents->count()); $m++) {
                $student = $eligibleStudents[$m];
                $tenantId = $student->tenant_id ?? 1;
                $branchGroups = $groupsByTenant->get($tenantId) ?? $groupsByTenant->first();
                $existingGroupIds = $student->enrollments->pluck('group_id')->toArray();
                $nextGroup = $branchGroups->whereNotIn('id', $existingGroupIds)->first();

                if ($nextGroup) {
                    $renewalCode = 'ENR-RENEW-' . $student->id . '-' . rand(100, 999);
                    $exists = Enrollment::withoutGlobalScopes()->where('enrollment_code', $renewalCode)->exists();
                    $scenarioCounts['D']++;
                    if (!$exists && !$dryRun) {
                        Enrollment::create([
                            'tenant_id' => $tenantId,
                            'student_id' => $student->id,
                            'group_id' => $nextGroup->id,
                            'enrollment_code' => $renewalCode,
                            'enrollment_date' => now()->subDays(5),
                            'total_amount' => 2200.00,
                            'paid_amount' => 1100.00,
                            'status' => 'active',
                            'notes' => self::TAG . ' - تجديد اشتراك رسمي لفترة تدريبية جديدة',
                        ]);
                        $manifest['created_enrollments']++;
                    } elseif (!$exists && $dryRun) {
                        $manifest['created_enrollments']++;
                    }
                }
            }

            // Step 8: Academic Follow-Up Tasks & Contact Logs
            $this->info("\n--- Phase 8: Populating Academic Follow-Up Center ---");
            $failingGrades = Grade::with('student', 'exam')->where('marks_obtained', '<', 10)->take(10)->get();

            foreach ($failingGrades as $grade) {
                if (!$grade->student) continue;

                $existingTask = StudentFollowUpTask::withoutGlobalScopes()
                    ->where('student_id', $grade->student_id)
                    ->where('reason', 'like', '%' . self::TAG . '%')
                    ->first();

                if (!$existingTask && !$dryRun) {
                    $task = StudentFollowUpTask::create([
                        'tenant_id' => $grade->tenant_id ?? 1,
                        'student_id' => $grade->student_id,
                        'group_id' => $grade->exam?->group_id,
                        'created_by' => 11,
                        'assigned_to' => 11,
                        'trigger_type' => 'academic_alert',
                        'title' => "متابعة أداء أكاديمي متدنٍ للطالب " . ($grade->student->user?->name ?? ''),
                        'reason' => self::TAG . " - تدني درجات الاختبار: حصل على {$grade->marks_obtained} من {$grade->exam?->total_marks}",
                        'priority' => 'high',
                        'status' => StudentFollowUpTask::STATUS_NEW,
                    ]);
                    $manifest['created_followups']++;

                    // Unanswered call attempt: task status remains NEW
                    StudentFollowUpContact::create([
                        'tenant_id' => $grade->tenant_id ?? 1,
                        'task_id' => $task->id,
                        'student_id' => $grade->student_id,
                        'contact_method' => 'phone_call',
                        'contact_result' => 'no_answer',
                        'guardian_name' => 'ولي الأمر',
                        'guardian_phone' => '01012345678',
                        'notes' => self::TAG . ' - محاولة اتصال هاتفية لم يتم الرد عليها',
                        'contacted_at' => now()->subDays(2),
                        'recorded_by' => 11,
                    ]);
                    $manifest['created_contacts']++;

                    // Answered call attempt: task status transitions to CONTACTED
                    StudentFollowUpContact::create([
                        'tenant_id' => $grade->tenant_id ?? 1,
                        'task_id' => $task->id,
                        'student_id' => $grade->student_id,
                        'contact_method' => 'phone_call',
                        'contact_result' => 'contacted',
                        'guardian_name' => 'ولي الأمر',
                        'guardian_phone' => '01012345678',
                        'notes' => self::TAG . ' - تم التواصل مع ولي الأمر والاتفاق على خطة تقوية إضافية',
                        'contacted_at' => now()->subDays(1),
                        'recorded_by' => 11,
                    ]);
                    $manifest['created_contacts']++;
                } elseif (!$existingTask && $dryRun) {
                    $manifest['created_followups']++;
                    $manifest['created_contacts'] += 2;
                }
            }

            // Step 9: Educational Supervision Center
            $this->info("\n--- Phase 9: Populating Educational Supervision Center ---");
            $supervisors = User::role('supervisor')->get();
            $instructors = User::role('instructor')->get();

            if ($supervisors->count() > 0 && $instructors->count() > 0) {
                $supervisor = $supervisors->first();
                $instructor = $instructors->first();
                $tenantId = $supervisor->tenant_id ?? 1;

                if (!SupervisoryObservation::withoutGlobalScopes()->where('title', 'like', '%' . self::TAG . '%')->exists()) {
                    if (!$dryRun) {
                        SupervisoryObservation::create([
                            'tenant_id' => $tenantId,
                            'supervisor_id' => $supervisor->id,
                            'instructor_id' => $instructor->id,
                            'group_id' => 1,
                            'category' => 'إدارة الصف والتفاعل',
                            'title' => 'ملاحظة توجيهية: تفعيل مشاركة جميع الطلاب [' . self::TAG . ']',
                            'description' => 'لوحظ تركيز التفاعل الصفي على مجموعة محددة من الطلاب دون إشراك الباقين.',
                            'required_action' => 'توزيع الأسئلة بصورة عادلة واستخدام استراتيجيات التعلم النشط.',
                            'priority' => 'medium',
                            'status' => SupervisoryObservation::STATUS_COMPLETED,
                            'instructor_response' => 'تم تطبيق استراتيجية العصف الذهني وإشراك جميع الطلاب.',
                            'instructor_responded_at' => now()->subDays(3),
                            'approved_by' => $supervisor->id,
                            'approved_at' => now()->subDays(2),
                            'verification_notes' => self::TAG . ' - تم التحقق في الزيارة اللاحقة وإغلاق الملاحظة بنجاح.',
                        ]);

                        SupervisoryObservation::create([
                            'tenant_id' => $tenantId,
                            'supervisor_id' => $supervisor->id,
                            'instructor_id' => $instructor->id,
                            'group_id' => 1,
                            'category' => 'استخدام الوسائل والتقنيات',
                            'title' => 'ملاحظة توجيهية: توظيف العروض التقديمية [' . self::TAG . ']',
                            'description' => 'تطوير العروض المرئية وإضافة أمثلة برمجية حية.',
                            'required_action' => 'تحديث محتوى العرض للأسبوع القادم.',
                            'priority' => 'low',
                            'status' => SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW,
                            'instructor_response' => 'تم رفع العرض المعدل للاعتماد.',
                            'instructor_responded_at' => now()->subDay(),
                        ]);
                        $manifest['created_observations'] += 2;
                    } else {
                        $manifest['created_observations'] += 2;
                    }
                }

                if (!InstructorEvaluation::withoutGlobalScopes()->where('strengths', 'like', '%' . self::TAG . '%')->exists()) {
                    if (!$dryRun) {
                        $eval = InstructorEvaluation::create([
                            'tenant_id' => $tenantId,
                            'evaluation_code' => 'EVAL-DEMO-' . rand(1000, 9999),
                            'instructor_id' => $instructor->id,
                            'supervisor_id' => $supervisor->id,
                            'evaluation_type' => InstructorEvaluation::TYPE_MONTHLY,
                            'evaluation_period' => now()->format('Y-m'),
                            'score_explanation_quality' => 5,
                            'score_student_interaction' => 4,
                            'score_practical_application' => 4,
                            'score_curriculum_adherence' => 5,
                            'score_time_management' => 4,
                            'score_homework_followup' => 5,
                            'calculated_average' => 4.50,
                            'criteria_observed_count' => 6,
                            'strengths' => self::TAG . ' - التزام عالي بالمنهج، قدرة ممتازة على تبسيط المفاهيم المعقدة.',
                            'areas_for_improvement' => 'زيادة التطبيقات العملية الفردية للطلاب الضعاف.',
                            'recommendations' => 'إقامة ورش عمل إضافية للمشاريع الطلابية.',
                            'status' => InstructorEvaluation::STATUS_PUBLISHED,
                            'version' => 1,
                            'published_at' => now()->subDays(5),
                            'published_by' => $supervisor->id,
                            'management_notes' => self::TAG . ' - تقييم شهري رسمي معتمد للإدارة',
                        ]);
                        $manifest['created_evaluations']++;

                        InstructorEvaluationAppeal::create([
                            'tenant_id' => $tenantId,
                            'evaluation_id' => $eval->id,
                            'evaluation_version' => 1,
                            'instructor_id' => $instructor->id,
                            'type' => InstructorEvaluationAppeal::TYPE_APPEAL,
                            'reason' => self::TAG . ' - استفسار بخصوص درجة التطبيق العملي حيث تم تنفيذ 4 مشاريع كاملة.',
                            'status' => InstructorEvaluationAppeal::STATUS_ACCEPTED,
                            'supervisor_response' => 'تمت مراجعة المشرف وتعديل الملاحظات والتأكيد على تميز المعلم.',
                            'supervisor_responded_at' => now()->subDays(2),
                            'supervisor_id' => $supervisor->id,
                            'management_decision' => 'قبول التماس المدرب وتعديل التقرير',
                            'decided_by' => 1,
                            'decided_at' => now()->subDay(),
                        ]);

                        SupervisionReport::create([
                            'tenant_id' => $tenantId,
                            'report_type' => SupervisionReport::TYPE_MONTHLY,
                            'period_start' => now()->startOfMonth()->toDateString(),
                            'period_end' => now()->endOfMonth()->toDateString(),
                            'period_key' => now()->format('Y-m'),
                            'version' => 1,
                            'summary' => 'تقرير تقييمي شامل لجودة البيئة التعليمية وانتظام الحصص بمجموعات الفرع [' . self::TAG . '].',
                            'recommendations' => 'الاستمرار في خطط الدعم الإضافية ومتابعة الواجبات.',
                            'actions_taken' => 'تم رصد الحضور بنسبة 92.5% واعتماد الخطط الإشرافية.',
                            'status' => SupervisionReport::STATUS_PUBLISHED,
                            'metrics_snapshot' => [
                                'total_instructors' => 5,
                                'total_sessions' => 45,
                                'attendance_rate' => 92.5,
                                'completed_observations' => 8,
                                'open_observations' => 2,
                            ],
                            'prepared_by' => $supervisor->id,
                            'published_by' => $supervisor->id,
                            'published_at' => now()->subDay(),
                        ]);
                        $manifest['created_supervision_reports']++;
                    } else {
                        $manifest['created_evaluations']++;
                        $manifest['created_supervision_reports']++;
                    }
                }
            }

            // Step 10: Financial Operations (Expenses, Revenues, Transfers, Salaries)
            $this->info("\n--- Phase 10: Populating Operational Finance & Cash Movement ---");
            $cairoVault = Vault::find(1);
            $secondVault = Vault::find(2) ?? Vault::where('id', '!=', 1)->first();
            $expCat = ExpenseCategory::first() ?? ExpenseCategory::create(['name' => 'مصروفات تشغيلية', 'tenant_id' => 1]);
            $revCat = RevenueCategory::first() ?? RevenueCategory::create(['name' => 'إيرادات أنشطة إضافية', 'tenant_id' => 1]);

            if ($cairoVault && !Expense::withoutGlobalScopes()->where('title', 'like', '%' . self::TAG . '%')->exists()) {
                if (!$dryRun) {
                    Expense::create([
                        'tenant_id' => 1,
                        'expense_category_id' => $expCat->id,
                        'vault_id' => $cairoVault->id,
                        'title' => 'أدوات ومستلزمات معمل الحاسب الآلي [' . self::TAG . ']',
                        'description' => 'شراء كابلات شبكات ومحولات شاشات للمعمل التدريبي.',
                        'amount' => 450.00,
                        'expense_date' => now()->subDays(7),
                        'payment_method' => 'cash',
                        'status' => 'approved',
                        'created_by' => 1,
                        'approved_by' => 1,
                        'approved_at' => now()->subDays(7),
                        'notes' => self::TAG . ' - مصروفات معتمدة',
                    ]);
                    $manifest['created_expenses']++;

                    Revenue::create([
                        'tenant_id' => 1,
                        'revenue_category_id' => $revCat->id,
                        'vault_id' => $cairoVault->id,
                        'title' => 'مبيعات الكتيبات والمذكرات التدريبية [' . self::TAG . ']',
                        'description' => 'إيرادات بيع حقائب تعليمية للمسار البرمجي.',
                        'amount' => 600.00,
                        'revenue_date' => now()->subDays(6),
                        'payment_method' => 'vault',
                        'status' => 'completed',
                        'created_by' => 1,
                        'received_by' => 1,
                        'received_at' => now()->subDays(6),
                        'notes' => self::TAG . ' - إيراد نشاط إضافي',
                    ]);
                    $manifest['created_revenues']++;

                    if ($secondVault && $secondVault->tenant_id === $cairoVault->tenant_id) {
                        VaultTransfer::create([
                            'tenant_id' => 1,
                            'from_vault_id' => $cairoVault->id,
                            'to_vault_id' => $secondVault->id,
                            'amount' => 1000.00,
                            'transfer_date' => now()->subDays(4),
                            'status' => 'completed',
                            'notes' => self::TAG . ' - تحويل نقدي داخلي لتعزيز السيولة',
                            'approved_by' => 1,
                            'approved_at' => now()->subDays(4),
                        ]);
                        $manifest['created_transfers']++;
                    }

                    $instructorUser = User::role('instructor')->first();
                    if ($instructorUser) {
                        SalaryPayment::create([
                            'tenant_id' => 1,
                            'employee_type' => 'instructor',
                            'instructor_id' => $instructorUser->id,
                            'amount' => 1200.00,
                            'payment_date' => now()->subDays(3),
                            'payment_method' => 'cash',
                            'vault_id' => $cairoVault->id,
                            'period_start' => now()->startOfMonth(),
                            'period_end' => now()->endOfMonth(),
                            'sessions_count' => 10,
                            'status' => 'paid',
                            'notes' => self::TAG . ' - صرف راتب مستحق عن الحصص المنفذة',
                            'paid_by' => 1,
                        ]);
                        $manifest['created_salary_payments']++;
                    }
                } else {
                    $manifest['created_expenses']++;
                    $manifest['created_revenues']++;
                    $manifest['created_transfers']++;
                    $manifest['created_salary_payments']++;
                }
            }

            if (!$dryRun) {
                DB::commit();
                $this->info("\n>>> SUCCESS: All persistent demo records committed to the database! <<<");
            } else {
                DB::rollBack();
                $this->info("\n>>> DRY RUN COMPLETED: No changes committed. <<<");
            }

            $this->displaySummaryTable($manifest, $scenarioCounts);
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\nExecution Failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    protected function displaySummaryTable(array $manifest, array $scenarioCounts)
    {
        $this->info("\n--- CREATED RECORDS MANIFEST ---");
        $tableData = [];
        foreach ($manifest as $key => $count) {
            $tableData[] = [str_replace('_', ' ', ucwords($key)), $count];
        }
        $this->table(['Entity / Record Type', 'Count'], $tableData);

        $this->info("\n--- STUDENT SCENARIOS DISTRIBUTION ---");
        $primaryScenariosCount = $scenarioCounts['A'] + $scenarioCounts['B'] + $scenarioCounts['E'];
        $remainingActive = max(0, Student::count() - $primaryScenariosCount);

        $this->table(['Scenario', 'Description', 'Students'], [
            ['Scenario A', 'Exemplary / Fully Paid / Passed Exams / Attendance High', $scenarioCounts['A']],
            ['Scenario B', 'Academic Alert / Overdue Debt / Missed Homework / Follow-up Active', $scenarioCounts['B']],
            ['Scenario C', 'Dual Course Enrollment in 2 Groups', $scenarioCounts['C']],
            ['Scenario D', 'Term Renewal & Continuing Student', $scenarioCounts['D']],
            ['Scenario E', 'Canceled Subscription & Financial Settlement Refund', $scenarioCounts['E']],
            ['Scenario F', 'General Active Ongoing Progression', $remainingActive],
        ]);
    }

    protected function handleCleanup()
    {
        $this->warn("\n>>> EXECUTING CLEANUP FOR TAG: " . self::TAG . " <<<");

        DB::beginTransaction();
        try {
            // Delete in reverse order of dependencies
            $reports = SupervisionReport::withoutGlobalScopes()->where('title', 'like', '%' . self::TAG . '%')->orWhere('notes', 'like', '%' . self::TAG . '%')->delete();
            $appeals = InstructorEvaluationAppeal::withoutGlobalScopes()->where('reason', 'like', '%' . self::TAG . '%')->delete();
            $evals = InstructorEvaluation::withoutGlobalScopes()->where('strengths', 'like', '%' . self::TAG . '%')->orWhere('management_notes', 'like', '%' . self::TAG . '%')->delete();
            $obs = SupervisoryObservation::withoutGlobalScopes()->where('title', 'like', '%' . self::TAG . '%')->orWhere('verification_notes', 'like', '%' . self::TAG . '%')->delete();
            
            $contacts = StudentFollowUpContact::withoutGlobalScopes()->where('notes', 'like', '%' . self::TAG . '%')->delete();
            $tasks = StudentFollowUpTask::withoutGlobalScopes()->where('reason', 'like', '%' . self::TAG . '%')->delete();

            $salaries = SalaryPayment::withoutGlobalScopes()->where('notes', 'like', '%' . self::TAG . '%')->delete();
            $transfers = VaultTransfer::withoutGlobalScopes()->where('notes', 'like', '%' . self::TAG . '%')->delete();
            $revenues = Revenue::withoutGlobalScopes()->where('notes', 'like', '%' . self::TAG . '%')->delete();
            $expenses = Expense::withoutGlobalScopes()->where('notes', 'like', '%' . self::TAG . '%')->delete();

            $payments = Payment::withoutGlobalScopes()->where('notes', 'like', '%' . self::TAG . '%')->delete();
            $installments = Installment::withoutGlobalScopes()->where('notes', 'like', '%' . self::TAG . '%')->delete();

            $grades = Grade::withoutGlobalScopes()->where('remarks', 'like', '%' . self::TAG . '%')->delete();
            $attempts = ExamAttempt::withoutGlobalScopes()->whereHas('exam', function($q) {
                $q->where('title', 'like', '%' . self::TAG . '%');
            })->delete();
            $questions = ExamQuestion::withoutGlobalScopes()->whereHas('exam', function($q) {
                $q->where('title', 'like', '%' . self::TAG . '%');
            })->delete();
            $exams = Exam::withoutGlobalScopes()->where('title', 'like', '%' . self::TAG . '%')->delete();

            $submissions = AssignmentSubmission::withoutGlobalScopes()->where('submission_notes', 'like', '%' . self::TAG . '%')->delete();
            $assignments = Assignment::withoutGlobalScopes()->where('title', 'like', '%' . self::TAG . '%')->delete();

            $attendances = AttendanceRecord::withoutGlobalScopes()->where('notes', 'like', '%' . self::TAG . '%')->delete();
            $sessions = AttendanceSession::withoutGlobalScopes()->where('session_notes', 'like', '%' . self::TAG . '%')->delete();

            $cancellations = EnrollmentCancellation::withoutGlobalScopes()->where('notes', 'like', '%' . self::TAG . '%')->delete();
            $enrollments = Enrollment::withoutGlobalScopes()->where('notes', 'like', '%' . self::TAG . '%')->delete();

            DB::commit();

            $this->info("Cleanup Completed Successfully!");
            $this->table(['Module', 'Deleted Records'], [
                ['Supervision Reports', $reports],
                ['Instructor Evaluations & Appeals', $evals + $appeals],
                ['Supervisory Observations', $obs],
                ['Follow-up Tasks & Contacts', $tasks + $contacts],
                ['Financial (Salaries, Transfers, Rev, Exp)', $salaries + $transfers + $revenues + $expenses],
                ['Payments & Installments', $payments + $installments],
                ['Exams, Questions, Attempts & Grades', $exams + $questions + $attempts + $grades],
                ['Assignments & Submissions', $assignments + $submissions],
                ['Sessions & Attendance', $sessions + $attendances],
                ['Demo Enrollments & Cancellations', $enrollments + $cancellations],
            ]);

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Cleanup Failed: " . $e->getMessage());
            return 1;
        }
    }
}

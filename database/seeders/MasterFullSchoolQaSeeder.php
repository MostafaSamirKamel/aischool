<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\Installment;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\SalaryPayment;
use App\Models\Student;
use App\Models\StudentGroupTransfer;
use App\Models\Supervisor;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultTransfer;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MasterFullSchoolQaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure required permissions and roles exist
        $this->ensurePermissionsAndRoles();

        // 2. Branches / Tenants (2 Branches)
        $branch1 = Tenant::firstOrCreate(
            ['name' => 'الفرع الرئيسي - القاهرة'],
            ['slug' => 'main', 'domain' => 'cairo.aischool.test', 'is_active' => true]
        );

        $branch2 = Tenant::firstOrCreate(
            ['name' => 'فرع دمنهور'],
            ['slug' => 'damanhour', 'domain' => 'damanhour.aischool.test', 'is_active' => true]
        );

        // 3. Vaults (6 Vaults: 3 per branch)
        $vaults = [];
        foreach ([$branch1, $branch2] as $branch) {
            $vaults[$branch->id]['cash'] = Vault::firstOrCreate(
                ['tenant_id' => $branch->id, 'type' => 'cash'],
                ['name' => "الخزينة النقدية - {$branch->name}", 'opening_balance' => 50000, 'is_active' => true]
            );
            $vaults[$branch->id]['vodafone'] = Vault::firstOrCreate(
                ['tenant_id' => $branch->id, 'type' => 'vodafone'],
                ['name' => "فودافون كاش - {$branch->name}", 'opening_balance' => 20000, 'is_active' => true]
            );
            $vaults[$branch->id]['instapay'] = Vault::firstOrCreate(
                ['tenant_id' => $branch->id, 'type' => 'instapay'],
                ['name' => "إنستاباي - {$branch->name}", 'opening_balance' => 30000, 'is_active' => true]
            );
        }

        // 4. Classrooms (6 Classrooms: 3 per branch)
        $classrooms = [];
        foreach ([$branch1, $branch2] as $branch) {
            $classrooms[$branch->id][] = Classroom::firstOrCreate(
                ['tenant_id' => $branch->id, 'name' => "معمل الحاسب 1 - {$branch->name}"],
                ['capacity' => 15, 'status' => 'active']
            );
            $classrooms[$branch->id][] = Classroom::firstOrCreate(
                ['tenant_id' => $branch->id, 'name' => "معمل الذكاء الاصطناعي 2 - {$branch->name}"],
                ['capacity' => 12, 'status' => 'active']
            );
            $classrooms[$branch->id][] = Classroom::firstOrCreate(
                ['tenant_id' => $branch->id, 'name' => "قاعة المحاضرات A - {$branch->name}"],
                ['capacity' => 25, 'status' => 'active']
            );
        }

        // 5. Staff & Accounts
        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@aischool.test'],
            [
                'name' => 'المدير العام (Super Admin)',
                'password' => Hash::make('password'),
                'tenant_id' => $branch1->id,
                'is_super_admin' => true,
                'status' => 'active',
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Staff per branch: 1 Branch Manager, 1 Accountant, 1 Secretary, 2 Registration Employees, 1 Supervisor, 3 Instructors
        $instructors = [];
        foreach ([$branch1, $branch2] as $bIndex => $branch) {
            $prefix = $bIndex === 0 ? 'cairo' : 'damanhour';

            // Branch Manager
            $manager = User::firstOrCreate(
                ['email' => "manager.{$prefix}@aischool.test"],
                ['name' => "مدير فرع {$branch->name}", 'password' => Hash::make('password'), 'tenant_id' => $branch->id, 'status' => 'active']
            );
            $manager->assignRole('admin');

            // Accountant
            $accountant = User::firstOrCreate(
                ['email' => "accountant.{$prefix}@aischool.test"],
                ['name' => "محاسب فرع {$branch->name}", 'password' => Hash::make('password'), 'tenant_id' => $branch->id, 'status' => 'active']
            );
            $accountant->assignRole('accountant');

            // Secretary
            $secretary = User::firstOrCreate(
                ['email' => "secretary.{$prefix}@aischool.test"],
                ['name' => "سكرتير فرع {$branch->name}", 'password' => Hash::make('password'), 'tenant_id' => $branch->id, 'status' => 'active']
            );
            $secretary->assignRole('secretary');

            // Registration Employees (2 per branch = 4 total)
            for ($r = 1; $r <= 2; $r++) {
                $regEmp = User::firstOrCreate(
                    ['email' => "registration.{$prefix}.{$r}@aischool.test"],
                    ['name' => "موظف تسجيل {$r} - {$branch->name}", 'password' => Hash::make('password'), 'tenant_id' => $branch->id, 'status' => 'active']
                );
                $regEmp->assignRole('registration');
            }

            // Supervisor (1 per branch = 2 total)
            $supUser = User::firstOrCreate(
                ['email' => "supervisor.{$prefix}@aischool.test"],
                ['name' => "مشرف أكاديمي - {$branch->name}", 'password' => Hash::make('password'), 'tenant_id' => $branch->id, 'status' => 'active']
            );
            $supUser->assignRole('supervisor');
            Supervisor::firstOrCreate(
                ['user_id' => $supUser->id],
                [
                    'tenant_id' => $branch->id,
                    'employee_number' => "SUP-00" . ($bIndex + 1),
                    'national_id' => "285010100000" . ($bIndex + 1),
                    'date_of_birth' => '1985-01-01',
                    'gender' => 'male',
                    'hire_date' => '2024-01-01',
                    'phone' => "010100000{$bIndex}",
                ]
            );

            // Instructors (3 per branch = 6 total)
            for ($ins = 1; $ins <= 3; $ins++) {
                $insCode = "INS-00" . ($bIndex * 3 + $ins);
                $insUser = User::firstOrCreate(
                    ['email' => "instructor.{$prefix}.{$ins}@aischool.test"],
                    ['name' => "المدرب {$ins} - {$branch->name}", 'password' => Hash::make('password'), 'tenant_id' => $branch->id, 'status' => 'active']
                );
                $insUser->assignRole('instructor');

                $instructor = Instructor::firstOrCreate(
                    ['user_id' => $insUser->id],
                    [
                        'tenant_id' => $branch->id,
                        'employee_code' => $insCode,
                        'hourly_rate' => 150,
                        'specialization' => 'Computer Science',
                    ]
                );
                $instructors[$branch->id][] = [
                    'user' => $insUser,
                    'instructor' => $instructor,
                ];
            }
        }

        // 6. Courses (8 Courses)
        $courseCatalog = [
            ['name' => 'Python Fundamentals', 'code' => 'PY-101', 'price' => 1500, 'total_sessions' => 12],
            ['name' => 'Python Advanced & OOP', 'code' => 'PY-201', 'price' => 1800, 'total_sessions' => 12],
            ['name' => 'Web Development with Laravel', 'code' => 'WEB-101', 'price' => 2200, 'total_sessions' => 16],
            ['name' => 'Scratch Programming for Kids', 'code' => 'SCR-101', 'price' => 1200, 'total_sessions' => 8],
            ['name' => 'Robotics & Embedded Systems', 'code' => 'ROB-101', 'price' => 2000, 'total_sessions' => 12],
            ['name' => 'AI & Machine Learning Basics', 'code' => 'AI-101', 'price' => 2500, 'total_sessions' => 16],
            ['name' => 'Data Science & Analysis', 'code' => 'DS-101', 'price' => 2300, 'total_sessions' => 14],
            ['name' => 'UI/UX & Graphic Design', 'code' => 'DES-101', 'price' => 1600, 'total_sessions' => 10],
        ];

        $courses = [];
        foreach ([$branch1, $branch2] as $branch) {
            foreach ($courseCatalog as $cData) {
                $courses[$branch->id][] = Course::firstOrCreate(
                    ['tenant_id' => $branch->id, 'name' => $cData['name']],
                    [
                        'price' => $cData['price'],
                        'sessions_count' => $cData['total_sessions'],
                        'status' => 'active',
                    ]
                );
            }
        }

        // 7. Groups (16 Groups: 8 per branch across various stages)
        $groups = [];
        $groupStages = [
            ['suffix' => 'Group A (Not Started)', 'completed' => 0, 'status' => 'active'],
            ['suffix' => 'Group B (2 Sessions)', 'completed' => 2, 'status' => 'active'],
            ['suffix' => 'Group C (4 Sessions)', 'completed' => 4, 'status' => 'active'],
            ['suffix' => 'Group D (7 Sessions - Ahead)', 'completed' => 7, 'status' => 'active'],
            ['suffix' => 'Group E (Near Completion)', 'completed' => 10, 'status' => 'active'],
            ['suffix' => 'Group F (Full Capacity)', 'completed' => 3, 'status' => 'active', 'max' => 5],
            ['suffix' => 'Group G (Partially Occupied)', 'completed' => 5, 'status' => 'active', 'max' => 12],
            ['suffix' => 'Group H (Weekend Batch)', 'completed' => 1, 'status' => 'active', 'max' => 10],
        ];

        foreach ([$branch1, $branch2] as $branch) {
            $branchCourses = $courses[$branch->id];
            $branchInstructors = $instructors[$branch->id];
            $branchClassrooms = $classrooms[$branch->id];

            foreach ($groupStages as $idx => $stg) {
                $course = $branchCourses[$idx % count($branchCourses)];
                $insObj = $branchInstructors[$idx % count($branchInstructors)];
                $classroom = $branchClassrooms[$idx % count($branchClassrooms)];

                $maxStudents = $stg['max'] ?? 10;

                $groups[$branch->id][] = Group::firstOrCreate(
                    ['tenant_id' => $branch->id, 'name' => "{$course->name} - {$stg['suffix']}"],
                    [
                        'course_id' => $course->id,
                        'instructor_id' => $insObj['user']->id,
                        'classroom_id' => $classroom->id,
                        'capacity' => 0,
                        'max_students' => $maxStudents,
                        'completed_sessions' => $stg['completed'],
                        'total_sessions' => $course->sessions_count ?? 12,
                        'status' => $stg['status'],
                        'start_date' => now()->subWeeks(3)->toDateString(),
                        'schedule' => [
                            ['day' => 'saturday', 'start_time' => '14:00', 'end_time' => '16:00'],
                            ['day' => 'tuesday', 'start_time' => '14:00', 'end_time' => '16:00'],
                        ],
                    ]
                );
            }
        }

        // 8. Parents & Students (40 Parents, 60 Students: 30 students per branch)
        // 20 parents with 1 child = 20 students
        // 20 parents with 2 children = 40 students -> Total = 60 students!
        $allStudents = [];
        $studentCounter = 1;

        for ($p = 1; $p <= 40; $p++) {
            $branch = ($p <= 20) ? $branch1 : $branch2;

            $parentUser = User::firstOrCreate(
                ['email' => "parent{$p}@aischool.test"],
                ['name' => "ولي أمر الطالب {$p}", 'password' => Hash::make('password'), 'tenant_id' => $branch->id, 'status' => 'active']
            );
            $parentUser->assignRole('parent');

            $childrenCount = ($p > 20) ? 2 : 1; // 20 parents have 2 kids, 20 parents have 1 kid

            for ($c = 1; $c <= $childrenCount; $c++) {
                $stIndex = $studentCounter++;
                $studentUser = User::firstOrCreate(
                    ['email' => "student{$stIndex}@aischool.test"],
                    ['name' => "طالب تجريبي {$stIndex}", 'password' => Hash::make('password'), 'tenant_id' => $branch->id, 'status' => 'active']
                );
                $studentUser->assignRole('student');

                $student = Student::firstOrCreate(
                    ['user_id' => $studentUser->id],
                    [
                        'tenant_id' => $branch->id,
                        'student_code' => "STD-" . str_pad($stIndex, 4, '0', STR_PAD_LEFT),
                        'phone' => "0120000" . str_pad($stIndex, 4, '0', STR_PAD_LEFT),
                        'gender' => ($stIndex % 2 === 0) ? 'female' : 'male',
                        'status' => 'active',
                    ]
                );

                \App\Models\StudentParent::firstOrCreate(
                    ['student_id' => $student->id, 'user_id' => $parentUser->id],
                    [
                        'tenant_id' => $branch->id,
                        'relation' => 'father',
                        'is_primary' => true,
                        'receive_notifications' => true,
                    ]
                );

                $allStudents[] = [
                    'student' => $student,
                    'user' => $studentUser,
                    'parent' => $parentUser,
                    'branch' => $branch,
                ];
            }
        }

        // 9. Enrollments, Courses & Financial Payments
        // Enroll students into groups and generate attendance, homework, exams, installments
        $categoriesExpense = ExpenseCategory::firstOrCreate(
            ['tenant_id' => $branch1->id, 'name' => 'رواتب وأجور'],
            ['code' => 'EXP-SALARY', 'is_active' => true]
        );
        $categoriesRevenue = RevenueCategory::firstOrCreate(
            ['tenant_id' => $branch1->id, 'name' => 'رسوم دراسية'],
            ['code' => 'REV-TUITION', 'is_active' => true]
        );

        foreach ($allStudents as $idx => $stData) {
            $student = $stData['student'];
            $branch = $stData['branch'];
            $branchGroups = $groups[$branch->id];
            $group = $branchGroups[$idx % count($branchGroups)];
            $course = $group->course;
            $cashVault = $vaults[$branch->id]['cash'];

            $enrollment = Enrollment::firstOrCreate(
                ['tenant_id' => $branch->id, 'student_id' => $student->id, 'group_id' => $group->id],
                [
                    'enrollment_code' => 'ENR-' . str_pad($idx + 1, 5, '0', STR_PAD_LEFT),
                    'enrollment_date' => now()->subMonth()->toDateString(),
                    'total_amount' => $course->price,
                    'paid_amount' => 500,
                    'discount' => 100,
                    'payment_plan' => 'installments',
                    'installments_count' => 2,
                    'status' => 'active',
                    'vault_id' => $cashVault->id,
                ]
            );

            EnrollmentCourse::firstOrCreate(
                ['enrollment_id' => $enrollment->id, 'course_id' => $course->id],
                [
                    'group_id' => $group->id,
                    'course_price' => $course->price,
                    'total_sessions' => $course->sessions_count ?? 12,
                    'current_session' => min(4, (int)$group->completed_sessions),
                    'status' => 'active',
                ]
            );

            $enrollment->groups()->syncWithoutDetaching([$group->id => ['enrolled_at' => now()->subWeeks(2)]]);

            // Installment 1: Paid (500 EGP)
            Installment::firstOrCreate(
                ['enrollment_id' => $enrollment->id, 'installment_number' => 1],
                [
                    'tenant_id' => $branch->id,
                    'amount' => 500,
                    'paid_amount' => 500,
                    'status' => 'paid',
                    'due_date' => now()->subMonth(),
                    'paid_date' => now()->subMonth(),
                ]
            );

            // Installment 2: Pending (Remaining)
            $remaining = max(0, $course->price - 100 - 500);
            Installment::firstOrCreate(
                ['enrollment_id' => $enrollment->id, 'installment_number' => 2],
                [
                    'tenant_id' => $branch->id,
                    'amount' => $remaining,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'due_date' => now()->addMonth(),
                ]
            );

            // Student Financial Payment record (100+ transactions across dataset)
            Payment::firstOrCreate(
                ['enrollment_id' => $enrollment->id, 'amount' => 500],
                [
                    'tenant_id' => $branch->id,
                    'vault_id' => $cashVault->id,
                    'payment_method' => 'cash',
                    'payment_date' => now()->subMonth(),
                    'transaction_reference' => 'REC-' . str_pad($idx + 1, 6, '0', STR_PAD_LEFT),
                    'status' => 'completed',
                ]
            );
        }

        // Sync capacities of all groups
        foreach ([$branch1, $branch2] as $branch) {
            foreach ($groups[$branch->id] as $grp) {
                $grp->syncCapacity();
            }
        }

        // 10. Sessions and Attendance Events (60+ sessions, 150+ attendance records)
        foreach ([$branch1, $branch2] as $branch) {
            foreach ($groups[$branch->id] as $gIndex => $grp) {
                if ($grp->completed_sessions > 0) {
                    for ($s = 1; $s <= min(4, (int)$grp->completed_sessions); $s++) {
                        $attDate = now()->subDays(20 - ($s * 4))->toDateString();
                        $session = AttendanceSession::withoutGlobalScopes()
                            ->where('group_id', $grp->id)
                            ->whereDate('attendance_date', $attDate)
                            ->first();

                        if (!$session) {
                            $session = AttendanceSession::create([
                                'tenant_id' => $branch->id,
                                'group_id' => $grp->id,
                                'attendance_date' => $attDate,
                                'course_id' => $grp->course_id,
                                'classroom' => $grp->classroom?->name ?? 'معمل 1',
                                'recorded_by' => $grp->instructor_id,
                                'session_notes' => "Session {$s} successfully delivered.",
                            ]);
                        }

                        // Mark attendance for active group students
                        foreach ($grp->activeStudents()->get() as $st) {
                            AttendanceRecord::firstOrCreate(
                                ['attendance_session_id' => $session->id, 'student_id' => $st->id],
                                ['status' => 'present', 'check_in_time' => now()->subDays(20 - ($s * 4))]
                            );
                        }
                    }
                }
            }
        }

        // 11. Homework & Exams (60+ Homework events, 50+ Exam attempts)
        foreach ([$branch1, $branch2] as $branch) {
            foreach ($groups[$branch->id] as $grp) {
                $assignment = Assignment::firstOrCreate(
                    ['tenant_id' => $branch->id, 'group_id' => $grp->id, 'title' => "واجب تطبيقي لمجموعة {$grp->name}"],
                    [
                        'instructor_id' => $grp->instructor_id,
                        'due_date' => now()->addDays(7),
                        'max_score' => 20,
                        'description' => 'قم بحل التمارين المرفقة ورفع الكود المصدري.',
                    ]
                );

                $exam = Exam::firstOrCreate(
                    ['tenant_id' => $branch->id, 'group_id' => $grp->id, 'title' => "اختبار تقييم نصفي - {$grp->name}"],
                    [
                        'course_id' => $grp->course_id,
                        'instructor_id' => $grp->instructor_id,
                        'exam_date' => now()->subDays(3),
                        'duration' => 60,
                        'total_marks' => 100,
                        'passing_marks' => 50,
                        'status' => 'completed',
                    ]
                );

                foreach ($grp->activeStudents()->get() as $st) {
                    AssignmentSubmission::firstOrCreate(
                        ['tenant_id' => $branch->id, 'assignment_id' => $assignment->id, 'student_id' => $st->id],
                        ['score' => 18, 'status' => 'graded', 'submitted_at' => now()->subDays(2), 'instructor_feedback' => 'ممتاز أحسنت العمل']
                    );

                    ExamAttempt::firstOrCreate(
                        ['tenant_id' => $branch->id, 'exam_id' => $exam->id, 'student_id' => $st->id],
                        ['score' => 92, 'total_score' => 100, 'percentage' => 92, 'passed' => true, 'completed_at' => now()->subDays(3)]
                    );
                }
            }
        }

        // 12. Vault Transfers (12+ transfers across branch vaults)
        foreach ([$branch1, $branch2] as $branch) {
            $bVaults = $vaults[$branch->id];
            for ($vt = 1; $vt <= 6; $vt++) {
                VaultTransfer::firstOrCreate(
                    [
                        'tenant_id' => $branch->id,
                        'from_vault_id' => $bVaults['cash']->id,
                        'to_vault_id' => $bVaults['vodafone']->id,
                        'notes' => "تحويل بنكي دوري رقم {$vt}",
                    ],
                    [
                        'amount' => 500,
                        'transfer_date' => now()->subDays(15 - $vt),
                        'status' => 'completed',
                        'transferred_by' => $superAdmin->id,
                    ]
                );
            }
        }

        // 13. Salary Payments & Expenses (25+ events)
        foreach ([$branch1, $branch2] as $branch) {
            $cashVault = $vaults[$branch->id]['cash'];
            foreach ($instructors[$branch->id] as $insObj) {
                SalaryPayment::firstOrCreate(
                    [
                        'tenant_id' => $branch->id,
                        'instructor_id' => $insObj['instructor']->id,
                        'payment_date' => now()->subDays(5)->toDateString(),
                    ],
                    [
                        'employee_type' => 'instructor',
                        'vault_id' => $cashVault->id,
                        'amount' => 600,
                        'status' => 'paid',
                        'notes' => 'صرف مستحقات 4 جلسات تدريبية مكتملة',
                        'created_by' => $superAdmin->id,
                    ]
                );

                Expense::firstOrCreate(
                    [
                        'tenant_id' => $branch->id,
                        'vault_id' => $cashVault->id,
                        'expense_category_id' => $categoriesExpense->id,
                        'title' => 'مستحقات تدريبية - ' . $insObj['user']->name,
                        'expense_date' => now()->subDays(5)->toDateString(),
                    ],
                    [
                        'amount' => 600,
                        'description' => 'صرف مستحقات تدريبية',
                        'status' => 'paid',
                        'created_by' => $superAdmin->id,
                    ]
                );
            }
        }

        // 14. Messaging Scenarios (30+ Group messages)
        foreach ([$branch1, $branch2] as $branch) {
            foreach ($groups[$branch->id] as $mIndex => $grp) {
                GroupMessage::firstOrCreate(
                    [
                        'tenant_id' => $branch->id,
                        'group_id' => $grp->id,
                        'message' => "مرحباً بكم في المجموعة الدراسية، يرجى مراجعة الدرس القادم رقم {$mIndex}.",
                    ],
                    [
                        'sender_id' => $grp->instructor_id,
                        'sender_type' => 'instructor',
                        'created_at' => now()->subDays(7),
                    ]
                );
            }
        }
    }

    protected function ensurePermissionsAndRoles(): void
    {
        $permissions = [
            'transfer_student_group',
            'view_enrollments',
            'update_enrollments',
            'cancel_enrollments',
            'settle_enrollments',
            'refund_enrollments',
            'view_vault_balance',
            'view_vault_transactions',
            'view_vault_reports',
            'transfer_vault_funds',
            'manage_salaries',
            'pay_salaries',
            'manage_expenses',
            'view_reports',
            'export_reports',
            'save_daily_snapshot',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $roles = [
            'super_admin' => $permissions,
            'admin' => $permissions,
            'accountant' => [
                'view_enrollments', 'view_vault_balance', 'view_vault_transactions',
                'view_vault_reports', 'transfer_vault_funds', 'manage_salaries',
                'pay_salaries', 'manage_expenses', 'view_reports', 'export_reports',
            ],
            'registration' => [
                'view_enrollments', 'update_enrollments', 'transfer_student_group',
                'cancel_enrollments', 'settle_enrollments',
            ],
            'secretary' => [
                'view_enrollments',
            ],
            'supervisor' => [
                'view_enrollments', 'view_reports',
            ],
            'instructor' => [],
            'parent' => [],
            'student' => [],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            if (!empty($perms)) {
                $role->givePermissionTo($perms);
            }
        }
    }
}

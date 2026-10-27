<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo json_encode([
    'tenants' => \App\Models\Tenant::count(),
    'users' => \App\Models\User::count(),
    'students' => \App\Models\Student::count(),
    'parents' => \App\Models\StudentParent::count(),
    'instructors' => \App\Models\Instructor::count(),
    'courses' => \App\Models\Course::count(),
    'groups' => \App\Models\Group::count(),
    'enrollments' => \App\Models\Enrollment::count(),
    'installments' => \App\Models\Installment::count(),
    'vaults' => \App\Models\Vault::count(),
    'payments' => \App\Models\Payment::count(),
    'revenues' => \App\Models\Revenue::count(),
    'expenses' => \App\Models\Expense::count(),
    'salary_payments' => \App\Models\SalaryPayment::count(),
    'cancellations' => \App\Models\EnrollmentCancellation::count(),
    'sessions' => \App\Models\AttendanceSession::count(),
    'attendance_records' => \App\Models\AttendanceRecord::count(),
    'assignments' => \App\Models\Assignment::count(),
    'submissions' => \App\Models\AssignmentSubmission::count(),
    'exams' => \App\Models\Exam::count(),
    'exam_attempts' => \App\Models\ExamAttempt::count(),
    'grades' => \App\Models\Grade::count(),
    'follow_up_tasks' => \App\Models\StudentFollowUpTask::count(),
    'supervisors' => \App\Models\Supervisor::count(),
    'supervision_reports' => \App\Models\SupervisionReport::count(),
], JSON_PRETTY_PRINT);

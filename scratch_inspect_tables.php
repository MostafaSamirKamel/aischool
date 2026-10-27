<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = [
    'students',
    'enrollments',
    'enrollment_courses',
    'enrollment_cancellations',
    'payments',
    'installments',
    'vaults',
    'vault_transfers',
    'expenses',
    'revenues',
    'salary_payments',
    'instructor_payments',
    'attendance_sessions',
    'attendance_records',
    'session_schedules',
    'assignments',
    'assignment_submissions',
    'exams',
    'exam_attempts',
    'supervisory_observations',
    'instructor_evaluations',
    'supervision_reports',
    'student_follow_up_tasks',
    'student_follow_up_contacts'
];

$output = [];
foreach ($tables as $t) {
    if (Schema::hasTable($t)) {
        $output[$t] = Schema::getColumnListing($t);
    } else {
        $output[$t] = 'NOT_FOUND';
    }
}

file_put_contents(__DIR__ . '/tables_columns.json', json_encode($output, JSON_PRETTY_PRINT));
echo "DONE\n";

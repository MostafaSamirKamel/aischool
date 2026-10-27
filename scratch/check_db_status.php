<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$status = [
    'students_total' => \App\Models\Student::count(),
    'new_simulation_groups' => \App\Models\Group::whereIn('id', [94, 95, 96])->pluck('name', 'id')->toArray(),
    'simulation_students' => \App\Models\Enrollment::whereIn('group_id', [94, 95, 96])->count(),
    'simulation_sessions' => \App\Models\SessionSchedule::whereIn('group_id', [94, 95, 96])->count(),
    'simulation_homeworks' => \App\Models\Assignment::whereIn('group_id', [94, 95, 96])->count(),
    'homework_submissions' => \App\Models\AssignmentSubmission::count(),
    'simulation_exams' => \App\Models\Exam::whereIn('group_id', [94, 95, 96])->count(),
    'exam_attempts' => \App\Models\ExamAttempt::count(),
    'classrooms' => \App\Models\Classroom::whereIn('id', [1, 2])->pluck('name', 'id')->toArray(),
    'vaults_balance' => \App\Models\Vault::all()->map(fn($v) => ['id' => $v->id, 'name' => $v->name, 'balance' => $v->getCurrentBalance()])->toArray(),
];

echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

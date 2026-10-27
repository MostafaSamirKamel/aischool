<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sub = \App\Models\AssignmentSubmission::first();
if ($sub) {
    echo "Sub ID: {$sub->id}, Score: {$sub->score}, Graded: " . var_export($sub->graded, true) . ", is_official: " . var_export($sub->is_official, true) . "\n";
    $svc = app(\App\Services\AcademicCenterService::class);
    $res = $svc->recalculateOfficialHomeworkGrade($sub->assignment_id, $sub->student_id);
    echo "Recalculated highest: " . ($res ? "ID {$res->id}, is_official: " . var_export($res->is_official, true) : "NULL") . "\n";
} else {
    echo "No submission found.\n";
}

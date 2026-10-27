<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['exams', 'exam_questions', 'exam_attempts', 'exam_answers', 'assignments', 'assignment_submissions'];
foreach ($tables as $t) {
    echo "=== Table: {$t} ===\n";
    echo implode(', ', \Illuminate\Support\Facades\Schema::getColumnListing($t)) . "\n\n";
}

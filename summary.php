<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "📊 Database Summary:\n";
echo "===================\n\n";

echo "Tracks: " . App\Models\Track::withoutGlobalScopes()->count() . "\n";
echo "Courses: " . App\Models\Course::withoutGlobalScopes()->count() . "\n";
echo "Classrooms: " . App\Models\Classroom::withoutGlobalScopes()->count() . "\n";
echo "Groups: " . App\Models\Group::withoutGlobalScopes()->count() . "\n";
echo "Students: " . App\Models\Student::withoutGlobalScopes()->count() . "\n";
echo "Instructors: " . App\Models\Instructor::withoutGlobalScopes()->count() . "\n";
echo "Enrollments: " . App\Models\Enrollment::withoutGlobalScopes()->count() . "\n";
echo "Exams: " . App\Models\Exam::withoutGlobalScopes()->count() . "\n";
echo "Grades: " . App\Models\Grade::withoutGlobalScopes()->count() . "\n";
echo "Attendances: " . App\Models\Attendance::withoutGlobalScopes()->count() . "\n";

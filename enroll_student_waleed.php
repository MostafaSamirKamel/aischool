<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use Illuminate\Support\Facades\DB;

echo "=== تسجيل الطالب وليد في الكورس والمجموعة ===\n\n";

// معلومات الطالب
$studentId = 1;
$groupId = 2;
$courseId = 1;

$student = Student::find($studentId);
if (!$student) {
    echo "❌ الطالب غير موجود!\n";
    exit;
}

echo "الطالب: {$student->user->name} (#{$student->student_code})\n";
echo "المجموعة: {$groupId}\n";
echo "الكورس: {$courseId}\n\n";

// التحقق من وجود enrollment
$enrollment = Enrollment::where('student_id', $studentId)
    ->where('tenant_id', $student->tenant_id)
    ->first();

if (!$enrollment) {
    echo "🔧 إنشاء Enrollment جديد...\n";
    
    // احصل على آخر enrollment_code
    $lastCode = DB::table('enrollments')
        ->where('enrollment_code', 'like', 'ENR-2026-%')
        ->orderBy('enrollment_code', 'desc')
        ->value('enrollment_code');
    
    $nextNumber = 1;
    if ($lastCode) {
        $parts = explode('-', $lastCode);
        $nextNumber = intval($parts[2] ?? 0) + 1;
    }
    
    $enrollmentCode = 'ENR-2026-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    
    $enrollment = Enrollment::create([
        'student_id' => $studentId,
        'tenant_id' => $student->tenant_id,
        'enrollment_code' => $enrollmentCode,
        'enrollment_date' => now()->subDays(10),
        'academic_year' => '2025-2026',
        'status' => 'active',
        'total_amount' => 2000,
        'paid_amount' => 0,
        'remaining_amount' => 2000,
    ]);
    
    echo "   ✅ Enrollment ID: {$enrollment->id}\n";
    echo "   Code: {$enrollmentCode}\n\n";
} else {
    echo "✅ Enrollment موجود: ID {$enrollment->id}\n\n";
}

// التحقق من وجود enrollment_course
$enrollmentCourse = EnrollmentCourse::where('enrollment_id', $enrollment->id)
    ->where('course_id', $courseId)
    ->first();

if (!$enrollmentCourse) {
    echo "🔧 إنشاء EnrollmentCourse جديد...\n";
    
    // احصل على معلومات الكورس
    $course = DB::table('courses')->where('id', $courseId)->first();
    $totalSessions = $course->total_sessions ?? 12;
    
    // احصل على عدد الجلسات الحالية للمجموعة
    $sessionsCount = DB::table('attendance_sessions')
        ->where('group_id', $groupId)
        ->where('course_id', $courseId)
        ->count();
    
    $enrollmentCourse = EnrollmentCourse::create([
        'enrollment_id' => $enrollment->id,
        'course_id' => $courseId,
        'group_id' => $groupId,
        'status' => 'active',
        'current_session' => $sessionsCount, // يبدأ من عدد الجلسات الحالية
        'total_sessions' => $totalSessions,
        'course_price' => 2000,
    ]);
    
    echo "   ✅ EnrollmentCourse ID: {$enrollmentCourse->id}\n";
    echo "   Current Session: {$enrollmentCourse->current_session}\n";
    echo "   Total Sessions: {$enrollmentCourse->total_sessions}\n\n";
} else {
    echo "✅ EnrollmentCourse موجود: ID {$enrollmentCourse->id}\n\n";
}

echo "✅ تم بنجاح!\n\n";
echo "💡 الآن:\n";
echo "   1. سجل دخول كـ وليد (student@admin.com)\n";
echo "   2. افتح \"حضوري\" - هتلاقي سجل الحضور\n";
echo "   3. افتح \"كورساتي\" - هتلاقي تقدم الكورس\n\n";

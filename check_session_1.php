<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== فحص جلسة الحضور (Session ID: 1) ===\n\n";

// الجلسة اللي المدرس بيعدل فيها
$session = DB::table('attendance_sessions')->where('id', 1)->first();

if (!$session) {
    echo "❌ الجلسة غير موجودة!\n";
    exit;
}

echo "✅ جلسة الحضور:\n";
echo "   ID: {$session->id}\n";
echo "   التاريخ: {$session->attendance_date}\n";
echo "   المجموعة: {$session->group_id}\n";
echo "   الكورس: {$session->course_id}\n";
echo "   Tenant ID: {$session->tenant_id}\n\n";

// سجلات الحضور في هذه الجلسة
$records = DB::table('attendance_records')
    ->where('attendance_session_id', 1)
    ->get();

echo "📋 سجلات الحضور في الجلسة: {$records->count()}\n\n";

if ($records->isNotEmpty()) {
    foreach ($records as $record) {
        $student = DB::table('students')->where('id', $record->student_id)->first();
        $user = DB::table('users')->where('id', $student->user_id)->first();
        
        $emoji = match($record->status) {
            'present' => '✅',
            'absent' => '❌',
            'late' => '⏰',
            'excused' => '📝',
            default => '❓'
        };
        
        echo "   {$emoji} {$user->name} (Student ID: {$student->id}, Code: {$student->student_code})\n";
        echo "      الحالة: {$record->status}\n";
        echo "      وقت الدخول: " . ($record->check_in_time ?? 'N/A') . "\n\n";
    }
}

// التحقق من الطلاب المسجلين في المجموعة
echo "\n" . str_repeat("=", 80) . "\n";
echo "👥 الطلاب المسجلين في المجموعة {$session->group_id}:\n\n";

$enrolledStudents = DB::table('enrollment_courses')
    ->join('enrollments', 'enrollment_courses.enrollment_id', '=', 'enrollments.id')
    ->join('students', 'enrollments.student_id', '=', 'students.id')
    ->join('users', 'students.user_id', '=', 'users.id')
    ->where('enrollment_courses.group_id', $session->group_id)
    ->where('enrollment_courses.course_id', $session->course_id)
    ->select('students.id as student_id', 'students.student_code', 'users.name', 'enrollment_courses.status')
    ->get();

echo "عدد الطلاب: {$enrolledStudents->count()}\n\n";

if ($enrolledStudents->isNotEmpty()) {
    foreach ($enrolledStudents as $s) {
        echo "   📌 {$s->name} (Student ID: {$s->student_id}, Code: {$s->student_code})\n";
        echo "      الحالة: {$s->status}\n";
        
        // هل له سجل حضور؟
        $hasRecord = DB::table('attendance_records')
            ->where('attendance_session_id', 1)
            ->where('student_id', $s->student_id)
            ->exists();
        
        if ($hasRecord) {
            echo "      ✅ له سجل حضور في هذه الجلسة\n";
        } else {
            echo "      ❌ ليس له سجل حضور في هذه الجلسة\n";
        }
        echo "\n";
    }
} else {
    echo "   ⚠️ لا يوجد طلاب مسجلين في هذه المجموعة!\n";
}

echo "\n🔍 التشخيص:\n";
echo "════════════\n";
echo "الطالب #STU-2026-0001 (وليد) غير مسجل في أي enrollment.\n";
echo "لذلك لا يظهر له:\n";
echo "   ❌ سجل الحضور\n";
echo "   ❌ تقدم الكورس\n\n";
echo "💡 الحل: يجب تسجيل الطالب في enrollment أولاً!\n\n";

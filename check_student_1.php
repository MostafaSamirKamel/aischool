<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\DB;

echo "=== فحص مفصل للطالب رقم 1 (User ID: 14) ===\n\n";

// 1. التحقق من المستخدم
$user = User::find(14);
if (!$user) {
    echo "❌ المستخدم 14 غير موجود!\n";
    exit;
}

echo "✅ المستخدم: {$user->name} (ID: 14)\n";
echo "   Email: {$user->email}\n";
echo "   Tenant ID: {$user->tenant_id}\n\n";

// 2. التحقق من الطالب
$student = Student::where('user_id', 14)->first();
if (!$student) {
    echo "❌ لا يوجد طالب مرتبط بالمستخدم 14!\n";
    exit;
}

echo "✅ الطالب: #{$student->student_code} (Student ID: {$student->id})\n";
echo "   Tenant ID: {$student->tenant_id}\n\n";

// 3. التحقق من التسجيلات (Enrollments)
$enrollments = DB::table('enrollments')
    ->where('student_id', $student->id)
    ->get();
echo "📚 التسجيلات (Enrollments): {$enrollments->count()}\n";

if ($enrollments->isEmpty()) {
    echo "❌ لا توجد تسجيلات للطالب!\n";
    exit;
}

foreach ($enrollments as $enrollment) {
    echo "\n┌" . str_repeat("─", 80) . "┐\n";
    echo "│ Enrollment ID: {$enrollment->id}\n";
    echo "│ Status: {$enrollment->status}\n";
    echo "│ Tenant ID: {$enrollment->tenant_id}\n";
    echo "└" . str_repeat("─", 80) . "┘\n";
    
    // 4. التحقق من كورسات التسجيل
    $enrollmentCourses = DB::table('enrollment_courses')
        ->where('enrollment_id', $enrollment->id)
        ->get();
    echo "   📖 Enrollment Courses: {$enrollmentCourses->count()}\n\n";
    
    foreach ($enrollmentCourses as $ec) {
        echo "   ┌" . str_repeat("─", 75) . "┐\n";
        echo "   │ EnrollmentCourse ID: {$ec->id}\n";
        echo "   │ Course ID: {$ec->course_id}\n";
        echo "   │ Group ID: {$ec->group_id}\n";
        echo "   │ Status: {$ec->status}\n";
        echo "   │ Current Session: {$ec->current_session}\n";
        echo "   │ Total Sessions: {$ec->total_sessions}\n";
        echo "   └" . str_repeat("─", 75) . "┘\n";
        
        // 5. التحقق من جلسات الحضور للمجموعة
        if ($ec->group_id && $ec->course_id) {
            echo "\n   📅 جلسات الحضور للمجموعة {$ec->group_id}:\n";
            
            $sessions = DB::table('attendance_sessions')
                ->where('group_id', $ec->group_id)
                ->where('course_id', $ec->course_id)
                ->get();
            
            echo "      إجمالي الجلسات: {$sessions->count()}\n\n";
            
            if ($sessions->isNotEmpty()) {
                foreach ($sessions as $session) {
                    echo "      ┌─ Session ID: {$session->id} - {$session->attendance_date}\n";
                    
                    // التحقق من سجل حضور الطالب
                    $record = DB::table('attendance_records')
                        ->where('attendance_session_id', $session->id)
                        ->where('student_id', $student->id)
                        ->first();
                    
                    if ($record) {
                        $emoji = match($record->status) {
                            'present' => '✅',
                            'absent' => '❌',
                            'late' => '⏰',
                            'excused' => '📝',
                            default => '❓'
                        };
                        echo "      │  {$emoji} الحالة: {$record->status}\n";
                        echo "      │  وقت الدخول: " . ($record->check_in_time ?? 'N/A') . "\n";
                    } else {
                        echo "      │  ⚠️ لا يوجد سجل حضور لهذا الطالب في هذه الجلسة!\n";
                    }
                    echo "      └─\n";
                }
            } else {
                echo "      ⚠️ لا توجد جلسات حضور مسجلة!\n";
            }
        } else {
            echo "\n   ⚠️ group_id أو course_id مفقود!\n";
        }
        echo "\n" . str_repeat("=", 80) . "\n";
    }
}

// 6. ملخص المشكلة
echo "\n\n🔍 التشخيص:\n";
echo "════════════\n\n";

$totalRecords = DB::table('attendance_records')
    ->where('student_id', $student->id)
    ->count();

echo "إجمالي سجلات الحضور للطالب: {$totalRecords}\n";

if ($totalRecords == 0) {
    echo "\n❌ المشكلة الرئيسية: لا توجد سجلات حضور (attendance_records) للطالب!\n";
    echo "\n💡 الحل:\n";
    echo "   - المدرس سجل attendance_session فقط\n";
    echo "   - لكن لم يسجل attendance_records للطلاب داخل الجلسة\n";
    echo "   - يجب على المدرس تسجيل حضور كل طالب على حدة في الجلسة\n\n";
} else {
    $present = DB::table('attendance_records')
        ->where('student_id', $student->id)
        ->where('status', 'present')
        ->count();
    
    echo "   - حاضر: {$present}\n";
    echo "   - المشكلة قد تكون في عرض البيانات في لوحة الطالب\n\n";
}

echo "\n";

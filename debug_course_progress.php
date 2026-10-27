<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;
use App\Models\EnrollmentCourse;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;

echo "=== فحص تقدم الكورس للطالب ===\n\n";

// اطلب من المستخدم كود الطالب
echo "أدخل كود الطالب (Student Code): ";
$studentCode = trim(fgets(STDIN));

$student = Student::where('student_code', $studentCode)->first();

if (!$student) {
    echo "❌ الطالب غير موجود!\n";
    exit;
}

echo "\n✅ الطالب: {$student->user->name} (#{$studentCode})\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// جيب كل الـ enrollment courses للطالب
$enrollmentCourses = EnrollmentCourse::whereHas('enrollment', function($q) use ($student) {
    $q->where('student_id', $student->id);
})->with(['course', 'group', 'enrollment'])->get();

if ($enrollmentCourses->isEmpty()) {
    echo "❌ لا يوجد كورسات مسجلة للطالب!\n";
    exit;
}

echo "📚 عدد الكورسات المسجلة: {$enrollmentCourses->count()}\n\n";

foreach ($enrollmentCourses as $ec) {
    echo "┌" . str_repeat("─", 68) . "┐\n";
    echo "│ 📖 الكورس: " . str_pad($ec->course->name ?? 'N/A', 50) . "│\n";
    echo "├" . str_repeat("─", 68) . "┤\n";
    echo "│ 🔑 ID: " . str_pad($ec->id, 58) . "│\n";
    echo "│ 🎯 المجموعة: " . str_pad($ec->group?->name ?? '⚠️ NULL', 52) . "│\n";
    echo "│ 📊 الحالة: " . str_pad($ec->status, 54) . "│\n";
    echo "│ 🎓 التقدم: {$ec->current_session} / {$ec->total_sessions}" . str_pad("", 42) . "│\n";
    echo "│ 📈 النسبة: " . str_pad($ec->getProgressPercentage() . "%", 54) . "│\n";
    echo "└" . str_repeat("─", 68) . "┘\n";
    
    // التحقق من جلسات الحضور
    if ($ec->group_id) {
        $sessions = AttendanceSession::where('group_id', $ec->group_id)
            ->where('course_id', $ec->course_id)
            ->get();
        
        echo "\n📅 جلسات الحضور للمجموعة:\n";
        echo "   إجمالي الجلسات المسجلة: {$sessions->count()}\n";
        
        // التحقق من سجلات حضور الطالب
        $attendanceRecords = AttendanceRecord::where('student_id', $student->id)
            ->whereIn('attendance_session_id', $sessions->pluck('id'))
            ->with('session')
            ->get();
        
        echo "   سجلات حضور الطالب: {$attendanceRecords->count()}\n";
        
        if ($attendanceRecords->isNotEmpty()) {
            echo "\n   📋 تفاصيل الحضور:\n";
            foreach ($attendanceRecords as $record) {
                $status_emoji = match($record->status) {
                    'present' => '✅',
                    'absent' => '❌',
                    'late' => '⏰',
                    'excused' => '📝',
                    default => '❓'
                };
                echo "   {$status_emoji} {$record->session->attendance_date->format('Y-m-d')} - {$record->status}\n";
            }
        }
        
        // المشكلة المحتملة
        echo "\n⚠️ التشخيص:\n";
        
        if (!$ec->group_id) {
            echo "   ❌ المشكلة: group_id = NULL\n";
            echo "   💡 الحل: يجب ربط الكورس بمجموعة\n";
        } elseif ($ec->status !== 'active') {
            echo "   ❌ المشكلة: الحالة = {$ec->status} (وليس active)\n";
            echo "   💡 الحل: يجب تفعيل الكورس\n";
        } elseif ($sessions->count() == 0) {
            echo "   ❌ المشكلة: لا توجد جلسات حضور مسجلة\n";
            echo "   💡 الحل: يجب على المدرس تسجيل جلسات حضور\n";
        } elseif ($attendanceRecords->count() != $ec->current_session) {
            echo "   ⚠️ تناقض: عدد سجلات الحضور ({$attendanceRecords->count()}) ≠ current_session ({$ec->current_session})\n";
            echo "   💡 الملاحظة: Observer يعمل عند إنشاء AttendanceSession (وليس AttendanceRecord)\n";
            echo "   📊 عدد الجلسات المسجلة: {$sessions->count()}\n";
            echo "   🔄 current_session يجب أن يساوي عدد الجلسات، وليس عدد مرات حضور الطالب!\n";
        } else {
            echo "   ✅ كل شيء يبدو صحيحاً!\n";
        }
        
    } else {
        echo "\n⚠️ التشخيص:\n";
        echo "   ❌ المشكلة الرئيسية: المجموعة غير محددة (group_id = NULL)\n";
        echo "   💡 الحل: يجب ربط enrollment course بمجموعة\n";
    }
    
    echo "\n" . str_repeat("=", 70) . "\n\n";
}

echo "\n🔍 ملاحظة مهمة:\n";
echo "   - current_session يتم تحديثه تلقائياً عند إنشاء AttendanceSession\n";
echo "   - يتم الزيادة لكل الطلاب في المجموعة (وليس فقط الحاضرين)\n";
echo "   - الشرط: enrollment_course يجب أن يكون له group_id والحالة active\n\n";
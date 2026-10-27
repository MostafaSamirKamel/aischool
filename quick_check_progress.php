<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EnrollmentCourse;
use App\Models\AttendanceSession;

echo "=== فحص سريع لتحديث التقدم ===\n\n";

$enrollmentCourses = EnrollmentCourse::with(['course', 'group', 'enrollment'])
    ->where('status', 'active')
    ->get();

echo "عدد enrollment courses النشطة: {$enrollmentCourses->count()}\n\n";

if ($enrollmentCourses->isEmpty()) {
    echo "❌ لا توجد enrollment courses نشطة!\n";
    exit;
}

foreach ($enrollmentCourses as $ec) {
    echo "┌" . str_repeat("─", 80) . "┐\n";
    echo "│ Enrollment Course ID: " . str_pad($ec->id, 54) . "│\n";
    echo "│ الكورس: " . str_pad($ec->course->name ?? 'NULL', 64) . "│\n";
    echo "│ المجموعة ID: " . str_pad($ec->group_id ?? 'NULL', 60) . "│\n";
    echo "│ المجموعة: " . str_pad($ec->group?->name ?? 'NULL', 62) . "│\n";
    echo "│ الحالة: " . str_pad($ec->status, 66) . "│\n";
    echo "│ التقدم: {$ec->current_session} / {$ec->total_sessions}" . str_pad("", 56) . "│\n";
    echo "└" . str_repeat("─", 80) . "┘\n";
    
    if ($ec->group_id && $ec->course_id) {
        $sessions = AttendanceSession::where('group_id', $ec->group_id)
            ->where('course_id', $ec->course_id)
            ->count();
        
        echo "   📅 جلسات الحضور المسجلة: {$sessions}\n";
        
        if ($ec->current_session != $sessions) {
            echo "   ⚠️ تناقض: current_session ({$ec->current_session}) ≠ جلسات مسجلة ({$sessions})\n";
        } else {
            echo "   ✅ التقدم متطابق مع عدد الجلسات!\n";
        }
    } else {
        echo "   ⚠️ لا يمكن حساب الجلسات (group_id أو course_id مفقود)\n";
    }
    echo "\n";
}

echo "\n📝 ملاحظة:\n";
echo "   - Observer يعمل عند إنشاء AttendanceSession تلقائياً\n";
echo "   - يجب أن يكون current_session = عدد الجلسات المسجلة\n\n";

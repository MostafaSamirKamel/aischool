<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;
use App\Models\EnrollmentCourse;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;

echo "=== فحص شامل لمشكلة تقدم الكورسات ===\n\n";

// جيب كل enrollment courses اللي فيها مشاكل محتملة
$enrollmentCourses = EnrollmentCourse::with([
    'course', 
    'group', 
    'enrollment.student.user'
])->get();

echo "📊 إجمالي enrollment courses: {$enrollmentCourses->count()}\n\n";

$issues = [
    'no_group' => 0,
    'not_active' => 0,
    'zero_progress_with_sessions' => 0,
    'ok' => 0,
];

$problemCourses = [];

$detailedInfo = [];

foreach ($enrollmentCourses as $ec) {
    $student = $ec->enrollment->student ?? null;
    
    if (!$student) continue;
    
    $hasIssue = false;
    $issueType = '';
    
    // معلومات مفصلة
    $sessions = 0;
    if ($ec->group_id) {
        $sessions = AttendanceSession::where('group_id', $ec->group_id)
            ->where('course_id', $ec->course_id)
            ->count();
    }
    
    $detailedInfo[] = [
        'student_code' => $student->student_code,
        'student_name' => $student->user->name,
        'course_name' => $ec->course->name ?? 'N/A',
        'group_id' => $ec->group_id,
        'group_name' => $ec->group?->name ?? 'NULL',
        'status' => $ec->status,
        'current_session' => $ec->current_session,
        'total_sessions' => $ec->total_sessions,
        'sessions_registered' => $sessions,
    ];
    
    // تحقق 1: هل المجموعة محددة؟
    if (!$ec->group_id) {
        $issues['no_group']++;
        $hasIssue = true;
        $issueType = 'no_group';
    }
    // تحقق 2: هل الحالة active؟
    elseif ($ec->status !== 'active') {
        $issues['not_active']++;
        if ($ec->status !== 'completed') {
            $hasIssue = true;
            $issueType = 'not_active';
        }
    }
    // تحقق 3: هل التقدم = 0 رغم وجود جلسات؟
    else {
        if ($sessions > 0 && $ec->current_session == 0) {
            $issues['zero_progress_with_sessions']++;
            $hasIssue = true;
            $issueType = 'zero_progress_with_sessions';
            
            $problemCourses[] = [
                'student_code' => $student->student_code,
                'student_name' => $student->user->name,
                'course_name' => $ec->course->name ?? 'N/A',
                'group_name' => $ec->group?->name ?? 'N/A',
                'sessions_count' => $sessions,
                'current_session' => $ec->current_session,
                'status' => $ec->status,
            ];
        } else {
            $issues['ok']++;
        }
    }
}

// عرض المعلومات المفصلة
echo "\n📋 تفاصيل جميع enrollment courses:\n";
echo str_repeat("=", 120) . "\n";
foreach ($detailedInfo as $i => $info) {
    echo ($i + 1) . ". {$info['student_name']} (#{$info['student_code']})\n";
    echo "   الكورس: {$info['course_name']}\n";
    echo "   المجموعة: {$info['group_name']} (ID: {$info['group_id']})\n";
    echo "   الحالة: {$info['status']}\n";
    echo "   التقدم: {$info['current_session']} / {$info['total_sessions']}\n";
    echo "   جلسات مسجلة: {$info['sessions_registered']}\n";
    echo str_repeat("-", 120) . "\n";
}
echo "\n";

echo "📈 ملخص المشاكل:\n";
echo "┌" . str_repeat("─", 50) . "┐\n";
echo "│ ❌ بدون مجموعة (no group_id): " . str_pad($issues['no_group'], 11) . "│\n";
echo "│ ⚠️  حالة غير active: " . str_pad($issues['not_active'], 22) . "│\n";
echo "│ 🔴 تقدم = 0 رغم وجود جلسات: " . str_pad($issues['zero_progress_with_sessions'], 14) . "│\n";
echo "│ ✅ لا مشاكل: " . str_pad($issues['ok'], 32) . "│\n";
echo "└" . str_repeat("─", 50) . "┘\n\n";

if (!empty($problemCourses)) {
    echo "🔍 تفاصيل الكورسات المشكلة (تقدم = 0 رغم وجود جلسات):\n";
    echo str_repeat("=", 100) . "\n\n";
    
    foreach ($problemCourses as $i => $pc) {
        echo ($i + 1) . ". الطالب: {$pc['student_name']} (#{$pc['student_code']})\n";
        echo "   الكورس: {$pc['course_name']}\n";
        echo "   المجموعة: {$pc['group_name']}\n";
        echo "   عدد الجلسات المسجلة: {$pc['sessions_count']}\n";
        echo "   current_session: {$pc['current_session']} ❌\n";
        echo "   الحالة: {$pc['status']}\n";
        echo "   ---\n\n";
    }
    
    echo "\n💡 الحل المقترح:\n";
    echo "   المشكلة: Observer يجب أن يعمل عند إنشاء AttendanceSession\n";
    echo "   ممكن يكون:\n";
    echo "   1. Observer مش كان مسجل من الأول (تم تسجيله مؤخراً)\n";
    echo "   2. تم إنشاء الجلسات قبل تفعيل Observer\n";
    echo "   3. enrollment_course كانت inactive وقت إنشاء الجلسة\n\n";
    
    echo "🔧 لتصحيح البيانات القديمة، شغل:\n";
    echo "   php fix_enrollment_progress.php\n\n";
} else {
    echo "✅ مافيش مشاكل في التقدم!\n\n";
}

// فحص تسجيل Observer
echo "\n🔍 فحص تسجيل Observer:\n";
$observerFile = __DIR__ . '/app/Observers/AttendanceSessionObserver.php';
$appServiceProvider = __DIR__ . '/app/Providers/AppServiceProvider.php';

if (file_exists($observerFile)) {
    echo "   ✅ AttendanceSessionObserver موجود\n";
} else {
    echo "   ❌ AttendanceSessionObserver غير موجود!\n";
}

$content = file_get_contents($appServiceProvider);
if (strpos($content, 'AttendanceSession::observe') !== false) {
    echo "   ✅ Observer مسجل في AppServiceProvider\n";
} else {
    echo "   ❌ Observer غير مسجل في AppServiceProvider!\n";
}

echo "\n";

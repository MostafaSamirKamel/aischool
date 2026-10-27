<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Group;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\Instructor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "=== إنشاء بيانات اختبار لنظام تتبع تقدم الكورسات ===\n\n";

// Get tenant_id from first user
$tenant = User::whereNotNull('tenant_id')->first();
if (!$tenant) {
    echo "❌ لا يوجد مستخدمين في النظام! أنشئ مستخدم أولاً.\n";
    exit;
}
$tenant_id = $tenant->tenant_id;

if (!$tenant_id) {
    echo "❌ tenant_id = NULL! سيتم استخدام tenant_id = 1\n";
    $tenant_id = 1;
}

echo "✅ Tenant ID: {$tenant_id}\n\n";

try {
    DB::beginTransaction();
    
    // 1. إنشاء مدرس
    echo "1️⃣ إنشاء مدرس...\n";
    $instructorUser = User::firstOrCreate(
        ['email' => 'instructor.test@school.local'],
        [
            'name' => 'أستاذ محمد أحمد',
            'password' => Hash::make('password123'),
            'tenant_id' => $tenant_id,
        ]
    );
    
    $instructor = Instructor::firstOrCreate(
        ['user_id' => $instructorUser->id],
        [
            'tenant_id' => $tenant_id,
            'employee_code' => 'INS001',
            'specialization' => 'رياضيات',
            'qualifications' => 'بكالوريوس تربية',
            'hire_date' => now(),
        ]
    );
    echo "   ✅ المدرس: {$instructorUser->name} (ID: {$instructor->id})\n\n";
    
    // 2. إنشاء كورس
    echo "2️⃣ إنشاء كورس...\n";
    $course = Course::firstOrCreate(
        ['name' => 'رياضيات متقدمة', 'tenant_id' => $tenant_id],
        [
            'name_ar' => 'رياضيات متقدمة',
            'name_en' => 'Advanced Mathematics',
            'description' => 'كورس رياضيات للمبتدئين',
            'total_sessions' => 12,
            'price' => 2000,
        ]
    );
    echo "   ✅ الكورس: {$course->name} (ID: {$course->id})\n\n";
    
    // 3. إنشاء مجموعة
    echo "3️⃣ إنشاء مجموعة...\n";
    $group = Group::firstOrCreate(
        ['name' => 'مجموعة اختبار 1'],
        [
            'tenant_id' => $tenant_id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'capacity' => 30,
            'schedule' => 'السبت والاثنين 4:00 مساءً',
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(30),
        ]
    );
    echo "   ✅ المجموعة: {$group->name} (ID: {$group->id})\n\n";
    
    // 4. إنشاء 3 طلاب
    echo "4️⃣ إنشاء طلاب...\n";
    $students = [];
    for ($i = 1; $i <= 3; $i++) {
        $studentUser = User::firstOrCreate(
            ['email' => "student{$i}.test@school.local"],
            [
                'name' => "طالب تجريبي {$i}",
                'password' => Hash::make('password123'),
                'tenant_id' => $tenant_id,
            ]
        );
        
        $student = Student::firstOrCreate(
            ['user_id' => $studentUser->id],
            [
                'tenant_id' => $tenant_id,
                'student_code' => 'STU' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'date_of_birth' => now()->subYears(18),
                'gender' => $i % 2 == 0 ? 'male' : 'female',
            ]
        );
        
        $students[] = $student;
        echo "   ✅ الطالب {$i}: {$studentUser->name} (#{$student->student_code})\n";
    }
    echo "\n";
    
    // 5. إنشاء تسجيلات (enrollments)
    echo "5️⃣ إنشاء تسجيلات...\n";
    foreach ($students as $i => $student) {
        $enrollment = Enrollment::firstOrCreate(
            [
                'student_id' => $student->id,
                'tenant_id' => $tenant_id,
            ],
            [
                'enrollment_code' => 'ENR-' . date('Y') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'enrollment_date' => now()->subDays(10),
                'academic_year' => '2025-2026',
                'status' => 'active',
                'total_amount' => 2000,
                'paid_amount' => 0,
                'remaining_amount' => 2000,
            ]
        );
        
        echo "   ✅ Enrollment ID {$enrollment->id} للطالب #{$student->student_code}\n";
        
        // إنشاء enrollment_course
        $enrollmentCourse = EnrollmentCourse::firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'course_id' => $course->id,
            ],
            [
                'group_id' => $group->id,
                'status' => 'active',
                'current_session' => 0,
                'total_sessions' => 12,
                'course_price' => 2000,
            ]
        );
        
        echo "      → EnrollmentCourse ID {$enrollmentCourse->id} (current_session: {$enrollmentCourse->current_session})\n";
    }
    echo "\n";
    
    // 6. إنشاء جلسات حضور
    echo "6️⃣ إنشاء جلسات حضور...\n";
    $sessionsToCreate = 5; // سننشئ 5 جلسات
    
    for ($session = 1; $session <= $sessionsToCreate; $session++) {
        $attendanceDate = now()->subDays($sessionsToCreate - $session + 1);
        
        $attendanceSession = AttendanceSession::create([
            'tenant_id' => $tenant_id,
            'attendance_date' => $attendanceDate,
            'course_id' => $course->id,
            'group_id' => $group->id,
            'classroom' => 'قاعة A1',
            'session_notes' => "جلسة رقم {$session}",
            'recorded_by' => $instructorUser->id,
        ]);
        
        echo "   ✅ جلسة {$session}: {$attendanceDate->format('Y-m-d')} (ID: {$attendanceSession->id})\n";
        
        // تسجيل حضور الطلاب
        foreach ($students as $studentIndex => $student) {
            // الطالب الأول حاضر دائماً، الثاني غائب أحياناً، الثالث متأخر أحياناً
            $status = match($studentIndex) {
                0 => 'present',
                1 => $session % 3 == 0 ? 'absent' : 'present',
                2 => $session % 2 == 0 ? 'late' : 'present',
                default => 'present',
            };
            
            AttendanceRecord::create([
                'attendance_session_id' => $attendanceSession->id,
                'student_id' => $student->id,
                'status' => $status,
                'check_in_time' => $status != 'absent' ? '16:00' : null,
            ]);
        }
        
        echo "      → تم تسجيل حضور " . count($students) . " طلاب\n";
    }
    echo "\n";
    
    DB::commit();
    
    echo "✅ تم إنشاء جميع البيانات بنجاح!\n\n";
    
    // 7. التحقق من التقدم
    echo "7️⃣ التحقق من تقدم الكورسات...\n";
    echo str_repeat("=", 80) . "\n";
    
    foreach ($students as $student) {
        $enrollmentCourse = EnrollmentCourse::whereHas('enrollment', function($q) use ($student) {
            $q->where('student_id', $student->id);
        })
        ->where('course_id', $course->id)
        ->first();
        
        if ($enrollmentCourse) {
            $progress = $enrollmentCourse->getProgressPercentage();
            echo "الطالب: {$student->user->name} (#{$student->student_code})\n";
            echo "  الحالة: {$enrollmentCourse->status}\n";
            echo "  التقدم: {$enrollmentCourse->current_session} / {$enrollmentCourse->total_sessions}\n";
            echo "  النسبة: {$progress}%\n";
            echo "  ---\n";
        }
    }
    
    echo "\n✅ انتهى السكريبت بنجاح!\n";
    echo "\n💡 يمكنك الآن:\n";
    echo "   1. تسجيل الدخول كطالب: student1.test@school.local / password123\n";
    echo "   2. التحقق من تقدم الكورس في لوحة الطالب\n";
    echo "   3. تسجيل الدخول كمدرس: instructor.test@school.local / password123\n";
    echo "   4. إضافة جلسات حضور جديدة ومراقبة تحديث التقدم تلقائياً\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ خطأ: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
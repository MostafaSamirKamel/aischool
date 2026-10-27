<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\EnrollmentCourse;
use App\Models\Enrollment;
use App\Models\Student;

echo "=== فحص العلاقات بين Enrollment و Student ===\n\n";

// احصل على enrollment courses
$enrollmentCourses = EnrollmentCourse::with(['course', 'group'])->take(5)->get();

echo "عدد enrollment courses: {$enrollmentCourses->count()}\n\n";

foreach ($enrollmentCourses as $ec) {
    echo "====================\n";
    echo "EnrollmentCourse ID: {$ec->id}\n";
    echo "Course: " . ($ec->course->name ?? 'NULL') . "\n";
    echo "Group: " . ($ec->group?->name ?? 'NULL') . "\n";
    echo "Enrollment ID: {$ec->enrollment_id}\n";
    
    // احصل على الـ enrollment
    $enrollment = Enrollment::find($ec->enrollment_id);
    if ($enrollment) {
        echo "✅ Enrollment موجود (ID: {$enrollment->id})\n";
        echo "   Student ID: {$enrollment->student_id}\n";
        
        // احصل على الطالب
        $student = Student::find($enrollment->student_id);
        if ($student) {
            echo "   ✅ Student موجود (ID: {$student->id})\n";
            echo "   Student Code: {$student->student_code}\n";
            echo "   User ID: {$student->user_id}\n";
            
            if ($student->user) {
                echo "   ✅ User موجود: {$student->user->name}\n";
            } else {
                echo "   ❌ User غير موجود!\n";
            }
        } else {
            echo "   ❌ Student غير موجود!\n";
        }
    } else {
        echo "❌ Enrollment غير موجود!\n";
    }
    
    echo "\n";
}

// تحقق من جدول enrollments
echo "\n=== فحص جدول enrollments ===\n";
$enrollmentsCount = Enrollment::count();
echo "عدد Enrollments: {$enrollmentsCount}\n";

$studentsWithEnrollments = Enrollment::distinct('student_id')->count('student_id');
echo "عدد الطلاب المسجلين: {$studentsWithEnrollments}\n";

// تحقق من جدول enrollment_courses
echo "\n=== فحص جدول enrollment_courses ===\n";
$enrollmentCoursesCount = EnrollmentCourse::count();
echo "عدد Enrollment Courses: {$enrollmentCoursesCount}\n";

$withGroups = EnrollmentCourse::whereNotNull('group_id')->count();
echo "مع مجموعة: {$withGroups}\n";

$withoutGroups = EnrollmentCourse::whereNull('group_id')->count();
echo "بدون مجموعة: {$withoutGroups}\n";

$active = EnrollmentCourse::where('status', 'active')->count();
echo "النشطة: {$active}\n";

$completed = EnrollmentCourse::where('status', 'completed')->count();
echo "المكتملة: {$completed}\n";

echo "\n";

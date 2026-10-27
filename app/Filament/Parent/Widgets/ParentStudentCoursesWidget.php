<?php

namespace App\Filament\Parent\Widgets;

use App\Models\Student;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class ParentStudentCoursesWidget extends Widget
{
    use HasWidgetShield;
    protected string $view = 'filament.parent.widgets.parent-student-courses';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected function getViewData(): array
    {
        $student = \App\Services\Parent\ParentStudentAccessService::getSelectedStudent();
        
        if (!$student) {
            return ['courses' => []];
        }

        // جلب جميع التسجيلات للطالب
        $enrollments = $student->enrollments()
            ->where('status', 'active')
            ->with(['enrollmentCourses.course', 'enrollmentCourses.group.instructor.user', 'enrollmentCourses.group.sessionSchedules'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($enrollments->isEmpty()) {
            return ['courses' => []];
        }

        // دمج جميع الكورسات من جميع التسجيلات
        $courses = [];
        foreach ($enrollments as $enrollment) {
            foreach ($enrollment->enrollmentCourses as $enrollmentCourse) {
                $course = $enrollmentCourse->course;
                $group = $enrollmentCourse->group;

                if (!$course || !$group) {
                    continue;
                }

                $sessions = $group->sessionSchedules ?? collect();
                $totalSessions = $sessions->count();
                $completedSessions = $sessions->where('status', 'completed')->count();

                // حساب الحضور
                $attendanceRecords = $student->attendanceRecords()
                    ->where('group_id', $group->id)
                    ->get();
                $attendedSessions = $attendanceRecords->where('status', 'present')->count();

                $courses[] = [
                    'enrollment_code' => $enrollment->enrollment_code,
                    'course_name' => $course->name,
                    'course_code' => $course->code ?? 'C-' . str_pad($course->id, 4, '0', STR_PAD_LEFT),
                    'course_price' => $enrollmentCourse->course_price ?? $course->price,
                    'is_active' => true,
                    'group_name' => $group->name,
                    'instructor_name' => optional(optional($group->instructor)->user)->name ?? 'غير محدد',
                    'total_sessions' => $totalSessions,
                    'completed_sessions' => $completedSessions,
                    'remaining_sessions' => max(0, $totalSessions - $completedSessions),
                    'attended_sessions' => $attendedSessions,
                    'session_progress' => $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 1) : 0,
                    'attendance_progress' => $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100, 1) : 0,
                ];
            }
        }

        return ['courses' => $courses];
    }
}

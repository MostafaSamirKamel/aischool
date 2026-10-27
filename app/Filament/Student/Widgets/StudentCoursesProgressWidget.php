<?php

namespace App\Filament\Student\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;

class StudentCoursesProgressWidget extends Widget
{
    use HasWidgetShield;
    protected string $view = 'filament.student.widgets.student-courses-progress';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getEnrollment()
    {
        $student = auth()->user()->student;

        if (!$student) {
            return null;
        }

        // جلب أول تسجيل نشط مع كل العلاقات المطلوبة
        return $student->enrollments()
            ->where('status', 'active')
            ->with([
                'enrollmentCourses.course',
                'enrollmentCourses.group.instructor.user',
                'enrollmentCourses.group.attendanceSessions'
            ])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getEnrollments()
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return collect();
        }

        // جلب جميع التسجيلات النشطة
        return $student->enrollments()
            ->where('status', 'active')
            ->with(['enrollmentCourses.course', 'enrollmentCourses.group.instructor.user', 'enrollmentCourses.group.sessionSchedules'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCourseStats($enrollmentCourse)
    {
        if (!$enrollmentCourse || !$enrollmentCourse->group) {
            return [
                'total_sessions' => 0,
                'completed_sessions' => 0,
                'remaining_sessions' => 0,
                'attended_sessions' => 0,
                'progress' => 0,
            ];
        }

        $group = $enrollmentCourse->group;
        $student = auth()->user()->student;

        // عدد الجلسات الكلي
        $totalSessions = $group->attendanceSessions()->count();

        // عدد الجلسات المكتملة (الجلسات التي تم عقدها في الماضي)
        $completedSessions = $group->attendanceSessions()
            ->where('attendance_date', '<=', today())
            ->count();

        // عدد الجلسات المتبقية
        $remainingSessions = max(0, $totalSessions - $completedSessions);

        // عدد الجلسات التي حضرها الطالب
        $attendedSessions = 0;
        if ($student) {
            $attendedSessions = $group->attendanceSessions()
                ->whereHas('records', function ($query) use ($student) {
                    $query->where('student_id', $student->id)
                        ->where('status', 'present');
                })
                ->count();
        }

        // نسبة التقدم
        $progress = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 1) : 0;

        return [
            'total_sessions' => $totalSessions,
            'completed_sessions' => $completedSessions,
            'remaining_sessions' => $remainingSessions,
            'attended_sessions' => $attendedSessions,
            'progress' => $progress,
        ];
    }

    protected function getViewData(): array
    {
        $enrollments = $this->getEnrollments();
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
                $remainingSessions = max(0, $totalSessions - $completedSessions);

                $courses[] = [
                    'enrollment_code' => $enrollment->enrollment_code,
                    'course_name' => $course->name,
                    'course_code' => $course->code ?? 'C-' . str_pad($course->id, 4, '0', STR_PAD_LEFT),
                    'course_price' => $enrollmentCourse->course_price ?? $course->price,
                    'group_name' => $group->name,
                    'instructor_name' => optional(optional($group->instructor)->user)->name ?? 'غير محدد',
                    'total_sessions' => $totalSessions,
                    'completed_sessions' => $completedSessions,
                    'remaining_sessions' => $remainingSessions,
                    'session_progress' => $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 1) : 0,
                ];
            }
        }

        return ['courses' => $courses];
    }
}

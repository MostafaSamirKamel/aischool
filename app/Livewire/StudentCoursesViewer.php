<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\Attendance;
use Livewire\Component;

class StudentCoursesViewer extends Component
{
    public $enrollmentId;
    public $enrollment;
    public $courses = [];

    public function mount($enrollmentId)
    {
        $this->enrollmentId = $enrollmentId;
        $this->loadEnrollment();
    }

    public function loadEnrollment()
    {
        $enrollment = Enrollment::with([
            'enrollmentCourses' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'enrollmentCourses.course' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'enrollmentCourses.group.sessionSchedules',
            'enrollmentCourses.group.instructor.user',
            'student',
        ])->find($this->enrollmentId);

        if (!$enrollment) {
            $this->courses = [];
            return;
        }

        $this->enrollment = $enrollment;
        $courses = [];

        foreach ($enrollment->enrollmentCourses as $enrollmentCourse) {
            $course = $enrollmentCourse->course;
            if (!$course)
                continue;

            $group = $enrollmentCourse->group;
            $isActive = !is_null($group);

            if (!$group) {
                $courses[] = [
                    'course_name' => $course->name,
                    'course_code' => $course->code ?? 'N/A',
                    'course_price' => $enrollmentCourse->course_price ?? $course->price,
                    'is_active' => false,
                    'group_name' => null,
                    'instructor_name' => null,
                    'total_sessions' => 0,
                    'completed_sessions' => 0,
                    'remaining_sessions' => 0,
                    'attended_sessions' => 0,
                    'session_progress' => 0,
                    'attendance_progress' => 0,
                ];
                continue;
            }

            $sessions = $group->sessionSchedules->map(function ($session) {
                $sessionDate = \Carbon\Carbon::parse($session->date);
                $now = now();
                $status = $sessionDate->isPast() ? 'منتهية' : ($sessionDate->isToday() ? 'جارية' : 'مجدولة');
                return [
                    'id' => $session->id,
                    'date' => $session->date,
                    'status' => $status,
                ];
            });

            $completedSessions = $sessions->where('status', 'منتهية')->count();
            $totalSessions = $sessions->count();
            $remainingSessions = $totalSessions - $completedSessions;

            $completedSessionDates = $sessions
                ->where('status', 'منتهية')
                ->pluck('date')
                ->map(fn($date) => \Carbon\Carbon::parse($date)->format('Y-m-d'))
                ->toArray();

            $attendedSessions = Attendance::where('student_id', $enrollment->student_id)
                ->where('group_id', $group->id)
                ->whereIn('date', $completedSessionDates)
                ->where('status', 'present')
                ->count();

            $sessionProgress = $totalSessions > 0 ? ($completedSessions / $totalSessions) * 100 : 0;
            $attendanceProgress = $totalSessions > 0 ? ($attendedSessions / $totalSessions) * 100 : 0;

            $courses[] = [
                'course_name' => $course->name,
                'course_code' => $course->code ?? 'N/A',
                'course_price' => $enrollmentCourse->course_price ?? $course->price,
                'is_active' => true,
                'group_name' => $group->name,
                'instructor_name' => optional(optional($group->instructor)->user)->name,
                'total_sessions' => $totalSessions,
                'completed_sessions' => $completedSessions,
                'remaining_sessions' => $remainingSessions,
                'attended_sessions' => $attendedSessions,
                'session_progress' => round($sessionProgress, 1),
                'attendance_progress' => round($attendanceProgress, 1),
            ];
        }

        $this->courses = $courses;
    }

    public function render()
    {
        return view('livewire.student-courses-viewer', [
            'courses' => $this->courses
        ]);
    }
}

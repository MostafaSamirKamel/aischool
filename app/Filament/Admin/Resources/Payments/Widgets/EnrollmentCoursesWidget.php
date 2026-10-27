<?php

namespace App\Filament\Admin\Resources\Payments\Widgets;

use App\Models\Enrollment;
use App\Filament\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class EnrollmentCoursesWidget extends Widget
{
    use HasWidgetShield;
    
    protected string $view = 'filament.admin.widgets.enrollment-courses-widget';
    
    protected int | string | array $columnSpan = 'full';

    public ?int $studentId = null;

    #[On('student-selected')]
    public function updateStudentId(?int $studentId): void
    {
        $this->studentId = $studentId;
    }

    protected function getViewData(): array
    {
        return [];
    }
    
    public function getCoursesData(): array
    {
        if (!$this->studentId) {
            return [];
        }

        $enrollment = Enrollment::with([
            'enrollmentCourses.course',
            'enrollmentCourses.group.sessionSchedules',
            'student.attendanceRecords',
        ])->where('student_id', $this->studentId)->first();

        if (!$enrollment) {
            return [];
        }

        $courses = [];
        foreach ($enrollment->enrollmentCourses as $enrollmentCourse) {
            $course = $enrollmentCourse->course;
            $group = $enrollmentCourse->group;
            
            if (!$course || !$group) continue;

            $sessions = $group->sessionSchedules->map(function ($session) {
                $sessionDate = \Carbon\Carbon::parse($session->date);
                $startTime = \Carbon\Carbon::parse($session->start_time);
                $endTime = \Carbon\Carbon::parse($session->end_time);
                $now = now();

                $status = 'مجدولة';
                if ($sessionDate->isPast()) {
                    $status = 'منتهية';
                } elseif ($sessionDate->isToday()) {
                    if ($now->between($startTime, $endTime)) {
                        $status = 'جارية';
                    } elseif ($now->greaterThan($endTime)) {
                        $status = 'منتهية';
                    }
                }

                return [
                    'id' => $session->id,
                    'date' => $sessionDate->format('Y-m-d'),
                    'day' => $sessionDate->locale('ar')->translatedFormat('l'),
                    'start_time' => $startTime->format('H:i'),
                    'end_time' => $endTime->format('H:i'),
                    'status' => $status,
                ];
            })->toArray();

            $completedSessions = collect($sessions)->where('status', 'منتهية')->count();
            $totalSessions = count($sessions);
            $remainingSessions = $totalSessions - $completedSessions;

            // حساب الحضور الفعلي
            $sessionIds = collect($sessions)->pluck('id')->toArray();
            $attendedSessions = $enrollment->student->attendanceRecords()
                ->whereIn('session_schedule_id', $sessionIds)
                ->where('status', 'present')
                ->distinct('session_schedule_id')
                ->count();

            // Progress percentages - كلهم مقارنة بإجمالي المحاضرات
            $sessionProgress = $totalSessions > 0 ? ($completedSessions / $totalSessions) * 100 : 0;
            $attendanceProgress = $totalSessions > 0 ? ($attendedSessions / $totalSessions) * 100 : 0;

            $courses[] = [
                'course_name' => $course->name,
                'course_code' => $course->code ?? 'N/A',
                'course_price' => $enrollmentCourse->price ?? $course->price,
                'total_sessions' => $totalSessions,
                'completed_sessions' => $completedSessions,
                'remaining_sessions' => $remainingSessions,
                'attended_sessions' => $attendedSessions,
                'session_progress' => round($sessionProgress, 1),
                'attendance_progress' => round($attendanceProgress, 1),
                'sessions' => $sessions,
            ];
        }

        return $courses;
    }
}

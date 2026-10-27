<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\EnrollmentCourse;
use App\Models\Course;

class Dashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected string $view = 'filament.student.pages.dashboard';
    protected static ?string $title = 'الرئيسية';
    protected static ?string $navigationLabel = 'الرئيسية';
    protected static ?int $navigationSort = -2;

    public function getTitle(): string
    {
        return __('لوحة التحكم والمؤشرات البيانية');
    }

    protected function getViewData(): array
    {
        $student = auth()->user()->student;

        $groupIds = [];
        if ($student) {
            $groupIds = Enrollment::where('student_id', $student->id)
                ->where('status', 'active')
                ->with('enrollmentCourses')
                ->get()
                ->flatMap(fn($enrollment) => $enrollment->enrollmentCourses->pluck('group_id'))
                ->unique()
                ->filter()
                ->toArray();
        }

        $upcomingExamsCount = 0;
        if (!empty($groupIds)) {
            $upcomingExamsCount = Exam::whereIn('group_id', $groupIds)
                ->whereIn('status', ['scheduled', 'in_progress', 'completed'])
                ->where('exam_date', '>=', now()->startOfDay())
                ->count();
        }

        $stats = [
            'total_courses' => $student ? $student->enrollments()->count() : 0,
            'attendance_rate' => 0,
            'pending_assignments' => $student ? $student->assignmentSubmissions()->whereNull('status')->orWhere('status', 'pending')->count() : 0,
            'upcoming_exams' => $upcomingExamsCount,
        ];

        if ($student) {
            $totalAttendance = $student->attendanceRecords()->count();
            $presentCount = $student->attendanceRecords()->where('status', 'present')->count();
            $stats['attendance_rate'] = $totalAttendance > 0 ? round(($presentCount * 100 / $totalAttendance), 1) : 0;
        }

        $activeCourses = collect();
        if ($student) {
            $activeCourses = EnrollmentCourse::query()
                ->whereHas('enrollment', fn($q) => $q->where('student_id', $student->id)->where('status', 'active'))
                ->with(['course', 'group.instructor.user'])
                ->take(2)
                ->get()
                ->map(function($ec) {
                    return (object) [
                        'name' => $ec->course?->name ?? 'كورس غير معروف',
                        'instructor_name' => $ec->group?->instructor?->name ?? $ec->course?->instructor?->name ?? 'مدرس غير محدد',
                        'group_name' => $ec->group?->name ?? 'مجموعة غير محددة',
                    ];
                });
        }

        return [
            'student' => $student,
            'stats' => $stats,
            'activeCourses' => $activeCourses,
            'upcomingSession' => null,
        ];
    }
}

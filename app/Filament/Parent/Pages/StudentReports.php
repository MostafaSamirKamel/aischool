<?php

namespace App\Filament\Parent\Pages;

use BackedEnum;
use Filament\Pages\Page;
use App\Models\AttendanceRecord;
use App\Models\ExamAttempt;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use Filament\Support\Icons\Heroicon;

use Livewire\Attributes\On;

class StudentReports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected string $view = 'filament.parent.pages.student-reports';
    
    protected static ?string $navigationLabel = 'تقارير الطالب';
    
    protected static ?string $title = 'تقارير الطالب';
    
    protected static ?int $navigationSort = 50;

    #[On('childSelected')]
    public function updateSelectedChild($childId = null): void
    {
        if ($childId) {
            \App\Services\Parent\ParentStudentAccessService::switchStudent((int) $childId);
        }
    }

    public function getSelectedStudent(): ?Student
    {
        return \App\Services\Parent\ParentStudentAccessService::getSelectedStudent();
    }

    public function getStats(): array
    {
        $student = $this->getSelectedStudent();
        
        if (!$student) {
            return [
                'totalGroups' => 0,
                'completedExams' => 0,
                'submittedAssignments' => 0,
                'avgScore' => 0,
                'attendanceRate' => 0,
                'absentDays' => 0,
                'lateDays' => 0,
            ];
        }

        // عدد المجموعات المسجل فيها
        $totalGroups = $student->enrollments()->count();

        // عدد الامتحانات المكتملة المنشورة
        $completedExams = ExamAttempt::where('student_id', $student->id)
            ->whereNotNull('score')
            ->whereHas('exam', fn($q) => $q->whereNotNull('published_at'))
            ->count();

        // عدد الواجبات المسلمة
        $submittedAssignments = AssignmentSubmission::where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->count();

        // متوسط الدرجات الكلي للاختبارات المنشورة
        $avgScore = ExamAttempt::where('student_id', $student->id)
            ->whereNotNull('score')
            ->whereHas('exam', fn($q) => $q->whereNotNull('published_at'))
            ->avg('score');
        
        $avgScore = $avgScore ? round($avgScore, 1) : 0;

        // نسبة الحضور
        $totalAttendance = AttendanceRecord::where('student_id', $student->id)->count();
        $presentCount = AttendanceRecord::where('student_id', $student->id)
            ->where('status', 'present')
            ->count();

        $attendanceRate = $totalAttendance > 0 
            ? round(($presentCount / $totalAttendance) * 100, 1) 
            : 0;

        // عدد أيام الغياب (آخر 30 يوم)
        $absentDays = AttendanceRecord::where('student_id', $student->id)
            ->where('status', 'absent')
            ->whereHas('session', fn($q) => $q->where('attendance_date', '>=', now()->subDays(30)))
            ->count();

        // عدد أيام التأخير (آخر 30 يوم)
        $lateDays = AttendanceRecord::where('student_id', $student->id)
            ->where('status', 'late')
            ->whereHas('session', fn($q) => $q->where('attendance_date', '>=', now()->subDays(30)))
            ->count();

        return [
            'totalGroups' => $totalGroups,
            'completedExams' => $completedExams,
            'submittedAssignments' => $submittedAssignments,
            'avgScore' => $avgScore,
            'attendanceRate' => $attendanceRate,
            'absentDays' => $absentDays,
            'lateDays' => $lateDays,
        ];
    }

    public function getAttendanceByMonth(): array
    {
        $student = $this->getSelectedStudent();
        
        if (!$student) {
            return [];
        }

        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->locale('ar')->format('F');

            $present = AttendanceRecord::where('student_id', $student->id)
                ->where('status', 'present')
                ->whereHas('session', function ($q) use ($month) {
                    $q->whereYear('attendance_date', $month->year)
                        ->whereMonth('attendance_date', $month->month);
                })
                ->count();

            $absent = AttendanceRecord::where('student_id', $student->id)
                ->where('status', 'absent')
                ->whereHas('session', function ($q) use ($month) {
                    $q->whereYear('attendance_date', $month->year)
                        ->whereMonth('attendance_date', $month->month);
                })
                ->count();

            $late = AttendanceRecord::where('student_id', $student->id)
                ->where('status', 'late')
                ->whereHas('session', function ($q) use ($month) {
                    $q->whereYear('attendance_date', $month->year)
                        ->whereMonth('attendance_date', $month->month);
                })
                ->count();

            $data[] = [
                'month' => $monthName,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
            ];
        }

        return $data;
    }

    public function getExamScores(): array
    {
        $student = $this->getSelectedStudent();
        
        if (!$student) {
            return [];
        }

        return ExamAttempt::where('student_id', $student->id)
            ->whereNotNull('score')
            ->whereHas('exam', fn($q) => $q->whereNotNull('published_at'))
            ->with('exam')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->filter(fn($attempt) => $attempt->exam !== null)
            ->map(function($attempt) {
                $totalMarks = (float) ($attempt->exam->total_marks ?? 0);
                $percentage = $totalMarks > 0 ? ($attempt->score / $totalMarks) * 100 : 0;
                
                return [
                    'exam_title' => $attempt->exam->title ?? 'امتحان',
                    'date' => $attempt->created_at?->format('Y-m-d') ?? '-',
                    'score' => $attempt->score,
                    'total_marks' => $totalMarks,
                    'percentage' => round($percentage, 1),
                    'passed' => $percentage >= 50,
                ];
            })
            ->values()
            ->toArray();
    }

    public function getAssignmentsProgress(): array
    {
        $student = $this->getSelectedStudent();
        
        if (!$student) {
            return [];
        }

        return AssignmentSubmission::where('student_id', $student->id)
            ->with('assignment')
            ->orderBy('submitted_at', 'desc')
            ->limit(20)
            ->get()
            ->filter(fn($submission) => $submission->assignment !== null)
            ->map(function($submission) {
                $maxScore = (float) ($submission->assignment->max_score ?? 0);
                $percentage = ($submission->score !== null && $maxScore > 0) 
                    ? ($submission->score / $maxScore) * 100 
                    : null;
                
                $status = $submission->status;
                if (!$status) {
                    $status = $submission->score !== null ? 'graded' : 'submitted';
                }

                return [
                    'assignment_title' => $submission->assignment->title ?? 'واجب',
                    'due_date' => $submission->assignment->due_date?->format('Y-m-d') ?? '-',
                    'submitted_at' => $submission->submitted_at?->format('Y-m-d H:i') ?? '-',
                    'status' => $status,
                    'score' => $submission->score,
                    'max_score' => $maxScore,
                    'percentage' => $percentage ? round($percentage, 1) : null,
                    'graded' => $submission->score !== null,
                ];
            })
            ->values()
            ->toArray();
    }

    public function getPerformanceByGroup(): array
    {
        $student = $this->getSelectedStudent();
        
        if (!$student) {
            return [];
        }

        return $student->enrollments()
            ->with('groups.course')
            ->get()
            ->flatMap(function ($enrollment) use ($student) {
                // For each enrollment, get all groups
                return $enrollment->groups->map(function ($group) use ($student) {
                    // متوسط درجات الطالب في هذه المجموعة
                    $groupExamAttempts = ExamAttempt::where('student_id', $student->id)
                        ->whereHas('exam', fn($q) => $q->where('group_id', $group->id))
                        ->whereNotNull('score')
                        ->get();

                    $avgScore = $groupExamAttempts->count() > 0
                        ? round($groupExamAttempts->avg('score'), 1)
                        : 0;

                    // نسبة الحضور في هذه المجموعة
                    $totalAttendance = AttendanceRecord::where('student_id', $student->id)
                        ->whereHas('session', fn($q) => $q->where('group_id', $group->id))
                        ->count();

                    $presentCount = AttendanceRecord::where('student_id', $student->id)
                        ->whereHas('session', fn($q) => $q->where('group_id', $group->id))
                        ->where('status', 'present')
                        ->count();

                    $attendanceRate = $totalAttendance > 0
                        ? round(($presentCount / $totalAttendance) * 100, 1)
                        : 0;

                    $courseName = $group->course?->name ?? '';
                    $displayName = $courseName ? ($courseName . ' - ' . $group->name) : $group->name;

                    return [
                        'group_name' => $displayName,
                        'avg_score' => $avgScore,
                        'attendance_rate' => $attendanceRate,
                        'exams_count' => $groupExamAttempts->count(),
                    ];
                });
            })
            ->unique('group_name')
            ->values()
            ->toArray();
    }

    public function getScoresTrend(): array
    {
        $student = $this->getSelectedStudent();
        
        if (!$student) {
            return [];
        }

        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->locale('ar')->format('F');
            
            $scores = ExamAttempt::where('student_id', $student->id)
                ->whereNotNull('score')
                ->whereHas('exam', function($q) use ($month) {
                    $q->whereYear('exam_date', $month->year)
                        ->whereMonth('exam_date', $month->month);
                })
                ->get();

            $avgScore = $scores->count() > 0 
                ? round($scores->avg('score'), 1) 
                : 0;

            $data[] = [
                'month' => $monthName,
                'avg_score' => $avgScore,
            ];
        }

        return $data;
    }

    public function getWeeklySummary(): array
    {
        $student = $this->getSelectedStudent();
        if (!$student) {
            return [];
        }

        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        // 1. Sessions & Attendance this week
        $weeklyAttendance = AttendanceRecord::where('student_id', $student->id)
            ->whereHas('session', fn($q) => $q->whereBetween('attendance_date', [$startOfWeek, $endOfWeek]))
            ->with(['session.course', 'session.group'])
            ->get();

        // 2. Completed assignments this week
        $weeklySubmissions = AssignmentSubmission::where('student_id', $student->id)
            ->whereBetween('submitted_at', [$startOfWeek, $endOfWeek])
            ->with('assignment')
            ->get();

        // 3. Exams completed this week
        $weeklyExams = ExamAttempt::where('student_id', $student->id)
            ->whereBetween('completed_at', [$startOfWeek, $endOfWeek])
            ->with('exam')
            ->get();

        // 4. Upcoming sessions next week
        $enrolledGroupIds = \App\Models\EnrollmentCourse::whereIn('enrollment_id', $student->enrollments()->pluck('id'))
            ->pluck('group_id')
            ->filter()
            ->unique();

        $upcomingSessions = \App\Models\SessionSchedule::where('date', '>=', now()->toDateString())
            ->where('date', '<=', now()->addDays(7)->toDateString())
            ->whereIn('group_id', $enrolledGroupIds)
            ->where('status', 'scheduled')
            ->with('group')
            ->orderBy('date')
            ->get();

        return [
            'period' => $startOfWeek->format('Y-m-d') . ' إلى ' . $endOfWeek->format('Y-m-d'),
            'sessionsCount' => $weeklyAttendance->count(),
            'presentCount' => $weeklyAttendance->where('status', 'present')->count(),
            'absentCount' => $weeklyAttendance->where('status', 'absent')->count(),
            'assignmentsCount' => $weeklySubmissions->count(),
            'examsCount' => $weeklyExams->count(),
            'avgExamScore' => $weeklyExams->count() > 0 ? round($weeklyExams->avg('score'), 1) : null,
            'upcomingSessions' => $upcomingSessions->map(fn($s) => [
                'title' => $s->title ?? 'جلسة مجدولة',
                'date' => $s->date?->format('Y-m-d'),
                'time' => $s->time_range ?? '',
                'group' => $s->group?->name ?? '',
            ])->toArray(),
        ];
    }
}

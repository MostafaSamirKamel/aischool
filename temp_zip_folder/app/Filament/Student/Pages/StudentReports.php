<?php

namespace App\Filament\Student\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;
use App\Models\Attendance;
use App\Models\ExamAttempt;
use App\Models\AssignmentSubmission;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class StudentReports extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.student.pages.student-reports';
    
    protected static ?string $navigationLabel = 'تقاريري';
    
    protected static ?string $title = 'تقاريري';
    
    protected static ?int $navigationSort = 11;

    public function getStats(): array
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return [];
        }

        // عدد المجموعات المسجل فيها
        $totalGroups = $student->enrollments()->count();

        // عدد الامتحانات المكتملة
        $completedExams = ExamAttempt::where('student_id', $student->id)
            ->whereNotNull('score')
            ->count();

        // عدد الواجبات المسلمة
        $submittedAssignments = AssignmentSubmission::where('student_id', $student->id)
            ->count();

        // متوسط الدرجات الكلي
        $avgScore = ExamAttempt::where('student_id', $student->id)
            ->whereNotNull('score')
            ->avg('score');
        
        $avgScore = $avgScore ? round($avgScore, 1) : 0;

        // نسبة الحضور
        $totalAttendance = Attendance::where('student_id', $student->id)->count();
        $presentCount = Attendance::where('student_id', $student->id)
            ->where('status', 'present')
            ->count();

        $attendanceRate = $totalAttendance > 0 
            ? round(($presentCount / $totalAttendance) * 100, 1) 
            : 0;

        // عدد أيام الغياب (آخر 30 يوم)
        $absentDays = Attendance::where('student_id', $student->id)
            ->where('status', 'absent')
            ->where('date', '>=', now()->subDays(30))
            ->count();

        // عدد أيام التأخير (آخر 30 يوم)
        $lateDays = Attendance::where('student_id', $student->id)
            ->where('status', 'late')
            ->where('date', '>=', now()->subDays(30))
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
        $student = Auth::user()->student;
        
        if (!$student) {
            return [];
        }

        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->locale('ar')->format('F');
            
            $present = Attendance::where('student_id', $student->id)
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->where('status', 'present')
                ->count();

            $absent = Attendance::where('student_id', $student->id)
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->where('status', 'absent')
                ->count();

            $late = Attendance::where('student_id', $student->id)
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->where('status', 'late')
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
        $student = Auth::user()->student;
        
        if (!$student) {
            return [];
        }

        return ExamAttempt::where('student_id', $student->id)
            ->whereNotNull('score')
            ->with('exam')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function($attempt) {
                $totalMarks = (float) ($attempt->exam->total_marks ?? 0);
                $score = (float) ($attempt->score ?? 0);
                $percentage = $totalMarks > 0 ? ($score / $totalMarks) * 100 : 0;
                
                return [
                    'exam_title' => $attempt->exam->title,
                    'date' => $attempt->created_at->format('Y-m-d'),
                    'score' => $score,
                    'total_marks' => $totalMarks,
                    'percentage' => round($percentage, 1),
                    'passed' => $percentage >= 50,
                ];
            })
            ->toArray();
    }

    public function getAssignmentsProgress(): array
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return [];
        }

        return AssignmentSubmission::where('student_id', $student->id)
            ->with('assignment')
            ->orderBy('submitted_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function($submission) {
                $percentage = $submission->score && $submission->assignment->max_score 
                    ? ($submission->score / $submission->assignment->max_score) * 100 
                    : null;
                
                return [
                    'assignment_title' => $submission->assignment->title,
                    'submitted_at' => $submission->submitted_at->format('Y-m-d H:i'),
                    'status' => $submission->status,
                    'score' => $submission->score,
                    'max_score' => $submission->assignment->max_score,
                    'percentage' => $percentage ? round($percentage, 1) : null,
                    'graded' => $submission->score !== null,
                ];
            })
            ->toArray();
    }

    public function getPerformanceByGroup(): array
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return [];
        }

        return $student->enrollments()
            ->with('enrollmentCourses.group.course')
            ->get()
            ->flatMap(function ($enrollment) use ($student) {
                // For each enrollment, get all groups
                return $enrollment->enrollmentCourses->map(function ($enrollmentCourse) use ($student) {
                    $group = $enrollmentCourse->group;
                    if (!$group) {
                        return null;
                    }

                    // متوسط درجات الطالب في هذه المجموعة
                    $groupExamAttempts = ExamAttempt::where('student_id', $student->id)
                        ->whereHas('exam', fn($q) => $q->where('group_id', $group->id))
                        ->whereNotNull('score')
                        ->get();

                    $avgScore = $groupExamAttempts->count() > 0
                        ? round($groupExamAttempts->avg('score'), 1)
                        : 0;

                    // نسبة الحضور في هذه المجموعة
                    $totalAttendance = Attendance::where('student_id', $student->id)
                        ->where('group_id', $group->id)
                        ->count();

                    $presentCount = Attendance::where('student_id', $student->id)
                        ->where('group_id', $group->id)
                        ->where('status', 'present')
                        ->count();

                    $attendanceRate = $totalAttendance > 0
                        ? round(($presentCount / $totalAttendance) * 100, 1)
                        : 0;

                    return [
                        'group_name' => $group->name,
                        'avg_score' => $avgScore,
                        'attendance_rate' => $attendanceRate,
                        'exams_count' => $groupExamAttempts->count(),
                    ];
                });
            })
            ->toArray();
    }

    public function getScoresTrend(): array
    {
        $student = Auth::user()->student;
        
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

    public function exportPortfolio(string $format = 'csv')
    {
        $student = Auth::user()->student;

        if (!$student) {
            Notification::make()
                ->title('لا يوجد ملف طالب مرتبط بالحساب')
                ->danger()
                ->send();

            return null;
        }

        $stats = $this->getStats();
        $examScores = $this->getExamScores();
        $assignmentsProgress = $this->getAssignmentsProgress();
        $performanceByGroup = array_values(array_filter($this->getPerformanceByGroup()));
        $attendanceByMonth = $this->getAttendanceByMonth();

        $studentName = Auth::user()->name ?? ('student-' . $student->id);
        $fileName = Str::slug('student-portfolio-' . $studentName) . '-' . now()->format('Ymd-His');

        if ($format === 'pdf') {
            $html = view('filament.student.pages.reports.student-portfolio-pdf', [
                'studentName' => $studentName,
                'stats' => $stats,
                'examScores' => $examScores,
                'assignmentsProgress' => $assignmentsProgress,
                'performanceByGroup' => $performanceByGroup,
                'attendanceByMonth' => $attendanceByMonth,
            ])->render();

            $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

            return Response::streamDownload(
                fn () => print($pdf->output()),
                $fileName . '.pdf'
            );
        }

        $rows = $this->buildPortfolioExportRows($stats, $examScores, $assignmentsProgress, $performanceByGroup, $attendanceByMonth);
        $headers = !empty($rows) ? array_keys($rows[0]) : ['القسم', 'العنوان', 'القيمة', 'تفاصيل'];

        return Response::streamDownload(function () use ($headers, $rows) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, $headers);

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $fileName . '.csv');
    }

    protected function buildPortfolioExportRows(
        array $stats,
        array $examScores,
        array $assignmentsProgress,
        array $performanceByGroup,
        array $attendanceByMonth
    ): array {
        $rows = [];

        foreach ($stats as $key => $value) {
            $rows[] = [
                'القسم' => 'ملخص',
                'العنوان' => $key,
                'القيمة' => $value,
                'تفاصيل' => '',
            ];
        }

        foreach ($performanceByGroup as $group) {
            $rows[] = [
                'القسم' => 'الأداء حسب المجموعة',
                'العنوان' => $group['group_name'] ?? '',
                'القيمة' => ($group['avg_score'] ?? 0) . '%',
                'تفاصيل' => 'الحضور: ' . (($group['attendance_rate'] ?? 0) . '%') . ' | امتحانات: ' . ($group['exams_count'] ?? 0),
            ];
        }

        foreach ($examScores as $exam) {
            $rows[] = [
                'القسم' => 'الامتحانات',
                'العنوان' => $exam['exam_title'] ?? '',
                'القيمة' => ($exam['score'] ?? 0) . '/' . ($exam['total_marks'] ?? 0),
                'تفاصيل' => 'النسبة: ' . (($exam['percentage'] ?? 0) . '%') . ' | التاريخ: ' . ($exam['date'] ?? ''),
            ];
        }

        foreach ($assignmentsProgress as $assignment) {
            $rows[] = [
                'القسم' => 'الواجبات',
                'العنوان' => $assignment['assignment_title'] ?? '',
                'القيمة' => $assignment['status'] ?? '',
                'تفاصيل' => 'الدرجة: ' . (($assignment['score'] ?? '-') . '/' . ($assignment['max_score'] ?? '-')) . ' | التاريخ: ' . ($assignment['submitted_at'] ?? ''),
            ];
        }

        foreach ($attendanceByMonth as $month) {
            $rows[] = [
                'القسم' => 'الحضور الشهري',
                'العنوان' => $month['month'] ?? '',
                'القيمة' => 'حاضر: ' . ($month['present'] ?? 0),
                'تفاصيل' => 'غائب: ' . ($month['absent'] ?? 0) . ' | متأخر: ' . ($month['late'] ?? 0),
            ];
        }

        return $rows;
    }

    public function getTitle(): string
    {
        return __('filament/student/student_reports.title');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament/student/student_reports.navigation_label');
    }


}

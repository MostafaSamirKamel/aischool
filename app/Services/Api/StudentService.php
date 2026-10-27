<?php

namespace App\Services\Api;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StudentService
{
    /**
     * @param array<string, mixed> $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;
        $perPage = (int) ($filters['per_page'] ?? 15);

        return Student::query()
            ->with(['user'])
            ->when($search, function ($query, $value) {
                $query->where(function ($q) use ($value) {
                    $q->where('student_code', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('national_id', 'like', "%{$value}%")
                        ->orWhereHas('user', function ($uq) use ($value) {
                            $uq->where('name', 'like', "%{$value}%")
                                ->orWhere('email', 'like', "%{$value}%");
                        });
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findWithEnrollments(int $studentId): Student
    {
        return Student::with(['enrollments.groups.course'])->findOrFail($studentId);
    }

    /**
     * @return array{grades: \Illuminate\Support\Collection<int, Grade>, statistics: array<string, int|float|null>}
     */
    public function gradesSummary(int $studentId): array
    {
        $grades = Grade::with(['exam.course'])
            ->where('student_id', $studentId)
            ->orderBy('graded_at', 'desc')
            ->get();

        return [
            'grades' => $grades,
            'statistics' => [
                'total_exams' => $grades->count(),
                'average_percentage' => round((float) ($grades->avg('percentage') ?? 0), 2),
                'passing_count' => $grades->filter(fn ($g) => $g->exam && $g->marks_obtained >= $g->exam->passing_marks)->count(),
                'highest_grade' => $grades->max('percentage'),
                'lowest_grade' => $grades->min('percentage'),
            ],
        ];
    }

    /**
     * @return array{attendance: LengthAwarePaginator, statistics: array<string, int>}
     */
    public function attendanceSummary(int $studentId): array
    {
        $attendance = Attendance::with('group')
            ->where('student_id', $studentId)
            ->orderBy('date', 'desc')
            ->paginate(30);

        $stats = Attendance::query()
            ->where('student_id', $studentId)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'attendance' => $attendance,
            'statistics' => $stats,
        ];
    }

    /**
     * @return array{student: Student, grades_by_course: \Illuminate\Support\Collection, statistics: array<string, int|float|null>}
     */
    public function transcript(int $studentId): array
    {
        $student = Student::findOrFail($studentId);

        $gradesByCourse = Grade::with(['exam.course'])
            ->where('student_id', $studentId)
            ->orderBy('graded_at', 'desc')
            ->get()
            ->groupBy(fn ($grade) => $grade->exam?->course?->name ?? 'غير محدد');

        $allGrades = Grade::with('exam')->where('student_id', $studentId)->get();

        return [
            'student' => $student,
            'grades_by_course' => $gradesByCourse,
            'statistics' => [
                'total_exams' => $allGrades->count(),
                'average_percentage' => round((float) ($allGrades->avg('percentage') ?? 0), 2),
                'passing_count' => $allGrades->filter(fn ($g) => $g->exam && $g->marks_obtained >= $g->exam->passing_marks)->count(),
                'failing_count' => $allGrades->filter(fn ($g) => $g->exam && $g->marks_obtained < $g->exam->passing_marks)->count(),
                'highest_grade' => $allGrades->max('percentage'),
                'lowest_grade' => $allGrades->min('percentage'),
            ],
        ];
    }
}

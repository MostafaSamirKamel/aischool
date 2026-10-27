<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Grade;
use Barryvdh\DomPDF\Facade\Pdf;

class TranscriptPdfService
{
    public function generate(Student $student)
    {
        $grades = Grade::with(['exam.course', 'exam.group'])
            ->where('student_id', $student->id)
            ->orderBy('graded_at', 'desc')
            ->get()
            ->groupBy(function($grade) {
                return $grade->exam->course->name ?? 'غير محدد';
            });

        $allGrades = Grade::where('student_id', $student->id)->get();
        
        $stats = [
            'total_exams' => $allGrades->count(),
            'average_percentage' => round($allGrades->avg('percentage'), 2),
            'total_marks_obtained' => $allGrades->sum('marks_obtained'),
            'total_possible_marks' => $allGrades->sum(fn($grade) => $grade->exam->total_marks),
            'passing_count' => $allGrades->filter(fn($grade) => $grade->marks_obtained >= $grade->exam->passing_marks)->count(),
            'failing_count' => $allGrades->filter(fn($grade) => $grade->marks_obtained < $grade->exam->passing_marks)->count(),
            'highest_grade' => $allGrades->max('percentage'),
            'lowest_grade' => $allGrades->min('percentage'),
            'grade_distribution' => [
                'A+' => $allGrades->where('grade', 'A+')->count(),
                'A' => $allGrades->where('grade', 'A')->count(),
                'B+' => $allGrades->where('grade', 'B+')->count(),
                'B' => $allGrades->where('grade', 'B')->count(),
                'C+' => $allGrades->where('grade', 'C+')->count(),
                'C' => $allGrades->where('grade', 'C')->count(),
                'D' => $allGrades->where('grade', 'D')->count(),
                'F' => $allGrades->where('grade', 'F')->count(),
            ],
        ];

        $pdf = Pdf::loadView('pdf.student-transcript', [
            'student' => $student,
            'grades' => $grades,
            'stats' => $stats,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }
}

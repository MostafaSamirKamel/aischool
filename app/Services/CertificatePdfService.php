<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Course;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificatePdfService
{
    public function generate(Student $student, Course $course): \Illuminate\Http\Response
    {
        // Get all grades for this student in this course
        $grades = $student->grades()
            ->whereHas('exam', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->with('exam')
            ->get();

        if ($grades->isEmpty()) {
            throw new \Exception('لا توجد درجات لهذا الطالب في هذا الكورس');
        }

        $totalMarks = $grades->sum('marks_obtained');
        $totalPossible = $grades->sum(fn($grade) => $grade->exam->total_marks);
        $finalPercentage = ($totalMarks / $totalPossible) * 100;
        
        $finalGrade = match (true) {
            $finalPercentage >= 95 => 'A+',
            $finalPercentage >= 90 => 'A',
            $finalPercentage >= 85 => 'B+',
            $finalPercentage >= 80 => 'B',
            $finalPercentage >= 75 => 'C+',
            $finalPercentage >= 70 => 'C',
            $finalPercentage >= 60 => 'D',
            default => 'F',
        };

        // Check if passed (all exams passed)
        $allPassed = $grades->every(fn($grade) => $grade->marks_obtained >= $grade->exam->passing_marks);

        if (!$allPassed) {
            throw new \Exception('الطالب لم ينجح في جميع امتحانات الكورس');
        }

        $pdf = Pdf::loadView('pdf.certificate', [
            'student' => $student,
            'course' => $course,
            'finalPercentage' => round($finalPercentage, 2),
            'finalGrade' => $finalGrade,
            'completionDate' => $grades->max('graded_at'),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf;
    }
}

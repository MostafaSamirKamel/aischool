<?php

namespace App\Services\Api;

use App\Models\Exam;
use App\Models\Grade;
use App\Models\Student;
use App\Services\Api\GradeCalculatorService;
use Illuminate\Validation\ValidationException;

class GradeService
{
    public function __construct(private readonly GradeCalculatorService $calculator)
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data, int $gradedBy): Grade
    {
        $exam = Exam::findOrFail((int) $data['exam_id']);

        $studentExistsInTenant = Student::query()
            ->whereKey((int) $data['student_id'])
            ->where('tenant_id', $exam->tenant_id)
            ->exists();

        if (!$studentExistsInTenant) {
            throw ValidationException::withMessages([
                'student_id' => ['Student does not belong to exam tenant.'],
            ]);
        }

        $marks = (float) $data['marks_obtained'];
        $total = (float) $exam->total_marks;

        try {
            $percentage = $this->calculator->calculatePercentage($marks, $total);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'marks_obtained' => [$e->getMessage()],
            ]);
        }

        $grade = $this->calculator->calculateGrade($percentage);

        return Grade::create([
            'tenant_id' => $exam->tenant_id,
            'exam_id' => $exam->id,
            'student_id' => (int) $data['student_id'],
            'enrollment_id' => $data['enrollment_id'] ?? null,
            'marks_obtained' => $marks,
            'percentage' => $percentage,
            'grade' => $grade,
            'remarks' => $data['remarks'] ?? null,
            'graded_by' => $gradedBy,
            'graded_at' => now(),
        ]);
    }
}

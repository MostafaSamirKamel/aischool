<?php

namespace App\Services\Api;

use InvalidArgumentException;

class GradeCalculatorService
{
    public function calculatePercentage(float $marksObtained, float $totalMarks): float
    {
        if ($totalMarks <= 0) {
            throw new InvalidArgumentException('Total marks must be greater than zero.');
        }

        if ($marksObtained < 0) {
            throw new InvalidArgumentException('Marks obtained cannot be negative.');
        }

        if ($marksObtained > $totalMarks) {
            throw new InvalidArgumentException('Marks obtained cannot exceed total marks.');
        }

        return round(($marksObtained / $totalMarks) * 100, 2);
    }

    public function calculateGrade(float $percentage): string
    {
        return match (true) {
            $percentage >= 95 => 'A+',
            $percentage >= 90 => 'A',
            $percentage >= 85 => 'B+',
            $percentage >= 80 => 'B',
            $percentage >= 75 => 'C+',
            $percentage >= 70 => 'C',
            $percentage >= 60 => 'D',
            default => 'F',
        };
    }
}

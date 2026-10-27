<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'exam_id',
        'student_id',
        'enrollment_id',
        'marks_obtained',
        'grade',
        'percentage',
        'remarks',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'percentage' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function ($grade) {
            // Load exam if not loaded
            if (!$grade->relationLoaded('exam')) {
                $grade->load('exam');
            }

            // Auto-calculate percentage
            if ($grade->exam && $grade->exam->total_marks > 0) {
                $grade->percentage = round(
                    ($grade->marks_obtained / $grade->exam->total_marks) * 100, 
                    2
                );
            }

            // Auto-assign grade based on percentage
            if ($grade->percentage !== null && (!$grade->grade || $grade->isDirty('marks_obtained'))) {
                $grade->grade = static::calculateGradeFromPercentage($grade->percentage);
            }

            // Set graded_at if not set
            if (!$grade->graded_at) {
                $grade->graded_at = now();
            }
        });

        static::created(function ($grade) {
            // Send notification to linked user when grade is created.
            if ($grade->student?->user && method_exists($grade->student->user, 'notify')) {
                $grade->student->user->notify(new \App\Notifications\GradePublishedNotification($grade));
            }
        });
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // Grade Calculation
    public static function calculateGradeFromPercentage(float $percentage): string
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

    public function getGradePointsAttribute(): float
    {
        return match ($this->grade) {
            'A+' => 4.0,
            'A' => 3.7,
            'B+' => 3.3,
            'B' => 3.0,
            'C+' => 2.7,
            'C' => 2.3,
            'D' => 2.0,
            'F' => 0.0,
            default => 0.0,
        };
    }

    public function isPass(): bool
    {
        if (!$this->exam) return false;
        return $this->marks_obtained >= $this->exam->passing_marks;
    }

    public function getGradeLabelAttribute(): string
    {
        return match ($this->grade) {
            'A+' => 'ممتاز',
            'A' => 'جيد جداً',
            'B+' => 'جيد جداً',
            'B' => 'جيد',
            'C+' => 'جيد',
            'C' => 'مقبول',
            'D' => 'مقبول',
            'F' => 'راسب',
            default => $this->grade,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return $this->isPass() ? 'success' : 'danger';
    }

    // Scopes
    public function scopePassed($query)
    {
        return $query->whereHas('exam', function ($q) {
            $q->whereRaw('grades.marks_obtained >= exams.passing_marks');
        });
    }

    public function scopeFailed($query)
    {
        return $query->whereHas('exam', function ($q) {
            $q->whereRaw('grades.marks_obtained < exams.passing_marks');
        });
    }

    public function scopeByGrade($query, string $grade)
    {
        return $query->where('grade', $grade);
    }

    public function scopeByStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByExam($query, int $examId)
    {
        return $query->where('exam_id', $examId);
    }
}

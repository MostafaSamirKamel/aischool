<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'exam_id',
        'student_id',
        'attempt_number',
        'score',
        'total_score',
        'percentage',
        'passed',
        'is_official',
        'started_at',
        'completed_at',
        'published_at',
        'time_taken',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'passed' => 'boolean',
        'is_official' => 'boolean',
        'attempt_number' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'published_at' => 'datetime',
        'time_taken' => 'integer',
    ];

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

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    // Helper methods
    public function calculateScore(): void
    {
        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($this->answers as $answer) {
            $question = $answer->question;
            $totalPoints += $question->points;
            if ($answer->is_correct) {
                $earnedPoints += $question->points;
            }
        }

        $this->score = $earnedPoints;
        $this->total_score = $totalPoints;
        $this->percentage = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
        $passingThreshold = ($this->exam && (float)$this->exam->total_marks > 0)
            ? ((float)$this->exam->passing_marks / (float)$this->exam->total_marks * 100)
            : 50.0;
        $this->passed = $this->percentage >= $passingThreshold;
        $this->save();
    }

    public function isCompleted(): bool
    {
        return !is_null($this->completed_at);
    }
}

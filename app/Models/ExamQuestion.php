<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'question_text',
        'points',
        'order',
        'pdf_file',
        'images',
    ];

    protected $casts = [
        'points' => 'decimal:2',
        'order' => 'integer',
        'images' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function booted()
    {
        static::deleting(function ($question) {
            // Cascade-delete الإجابات والخيارات
            $question->answers()->delete();
            $question->options()->delete();
        });
    }

    // Relationships
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ExamQuestionOption::class)->orderBy('order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    // Helper methods
    public function getCorrectOption(): ?ExamQuestionOption
    {
        return $this->options()->where('is_correct', true)->first();
    }

    /**
     * Safely clone this question and its options into an exam, preserving historical attempts (Phase 8.3)
     */
    public function duplicateToExam(int $newExamId): ExamQuestion
    {
        $newQuestion = $this->replicate(['exam_id']);
        $newQuestion->exam_id = $newExamId;
        $newQuestion->save();

        foreach ($this->options as $option) {
            $newOption = $option->replicate(['exam_question_id']);
            $newOption->exam_question_id = $newQuestion->id;
            $newOption->save();
        }

        return $newQuestion;
    }
}

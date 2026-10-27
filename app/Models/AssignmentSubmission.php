<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'tenant_id',
        'attempt_number',
        'submission_notes',
        'file_path',
        'images',
        'submitted_at',
        'score',
        'instructor_feedback',
        'graded_at',
        'published_at',
        'graded_by',
        'graded',
        'is_official',
        'status',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'published_at' => 'datetime',
        'images' => 'array',
        'score' => 'float',
        'graded' => 'boolean',
        'is_official' => 'boolean',
        'attempt_number' => 'integer',
        'status' => 'string',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Check if evaluation has been officially published to the student
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->score !== null;
    }

    /**
     * Check if instructor evaluation is currently in draft mode
     */
    public function isDraft(): bool
    {
        return $this->score !== null && $this->published_at === null;
    }

    /**
     * Check if submission occurred after the assignment due date deadline
     */
    public function isLate(): bool
    {
        if (!$this->submitted_at || !$this->assignment?->due_date) {
            return false;
        }

        $dueDate = \Carbon\Carbon::parse($this->assignment->due_date)->endOfDay();
        return \Carbon\Carbon::parse($this->submitted_at)->gt($dueDate);
    }

    /**
     * Get a normalized list of all submitted image paths
     */
    public function getImagesListAttribute(): array
    {
        if (!empty($this->images) && is_array($this->images)) {
            return array_values(array_filter($this->images));
        }

        if (!empty($this->file_path)) {
            return [$this->file_path];
        }

        return [];
    }

    /**
     * Get count of submitted images
     */
    public function getImageCountAttribute(): int
    {
        return count($this->images_list);
    }

    /**
     * Get Arabic status label for student view
     */
    public function getStudentStatusLabelAttribute(): string
    {
        if ($this->isPublished()) {
            return 'تم التقييم';
        }

        if ($this->isDraft()) {
            return 'قيد المراجعة';
        }

        if ($this->submitted_at) {
            return 'تم التسليم';
        }

        return 'لم يتم التسليم';
    }
}

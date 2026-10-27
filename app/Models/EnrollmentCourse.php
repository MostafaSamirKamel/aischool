<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentCourse extends Model
{
    use HasFactory;

    protected $table = 'enrollment_courses';

    protected $fillable = [
        'enrollment_id',
        'course_id',
        'course_price',
        'status',
        'current_session',
        'group_id',
        'total_sessions',
    ];

    protected $casts = [
        'course_price' => 'decimal:2',
        'current_session' => 'integer',
        'total_sessions' => 'integer',
    ];

    // Relationships
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    // Accessors & Mutators
    /**
     * Get the status in Arabic
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'inactive' => 'غير نشط',
            'active' => 'نشط',
            'completed' => 'مكتمل',
            default => $this->status,
        };
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): int
    {
        if ($this->total_sessions == 0) {
            return 0;
        }
        return (int) (($this->current_session / $this->total_sessions) * 100);
    }

    /**
     * Check if course is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if course is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if course is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Check if course is ready to be completed
     */
    public function shouldBeCompleted(): bool
    {
        return $this->current_session >= $this->total_sessions && $this->status !== 'completed';
    }

    /**
     * Mark course as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'current_session' => $this->total_sessions,
        ]);

        // Send notification to student
        $this->enrollment->student->user->notify(
            new \App\Notifications\CourseCompletedNotification($this)
        );

        // Send notification to parents if exists
        $parents = $this->enrollment->student->parents;
        if ($parents && $parents->count() > 0) {
            foreach ($parents as $parent) {
                $parent->notify(
                    new \App\Notifications\CourseCompletedNotification($this)
                );
            }
        }
    }

    /**
     * Increment current session and check for completion
     */
    public function incrementSession(): void
    {
        $this->current_session = min($this->current_session + 1, $this->total_sessions);
        
        if ($this->shouldBeCompleted()) {
            $this->markAsCompleted();
        } else {
            $this->save();
        }
    }

    /**
     * Set status to active with group assignment
     */
    public function activateWithGroup($groupId): void
    {
        $this->update([
            'status' => 'active',
            'group_id' => $groupId,
        ]);
    }

    /**
     * Change group (only if not completed and new group is in same or earlier session)
     */
    public function changeGroup($newGroupId): bool
    {
        if ($this->isCompleted()) {
            return false;
        }

        $this->update([
            'group_id' => $newGroupId,
        ]);

        return true;
    }
}

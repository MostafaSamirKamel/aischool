<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstructorReview extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'instructor_id',
        'course_id',
        'group_id',
        'reviewer_type',
        'student_id',
        'parent_user_id',
        'rating',
        'comment',
        'aspects',
        'status',
        'rejection_reason',
        'approved_at',
        'approved_by',
        'is_anonymous',
        'reviewer_ip',
    ];

    protected $casts = [
        'aspects' => 'array',
        'approved_at' => 'datetime',
        'is_anonymous' => 'boolean',
    ];

    // Relations
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parentUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeForInstructor($query, int $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeForCourse($query, int $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeForGroup($query, int $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    public function scopeByReviewerType($query, string $type)
    {
        return $query->where('reviewer_type', $type);
    }

    // Helper methods
    public function getReviewerNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'مجهول';
        }

        if ($this->reviewer_type === 'student' && $this->student) {
            return $this->student->user->name;
        }

        if ($this->reviewer_type === 'parent' && $this->parentUser) {
            return $this->parentUser->name;
        }

        return 'غير معروف';
    }

    public function getStarsAttribute(): string
    {
        return str_repeat('⭐', $this->rating);
    }

    public function approve(int $userId): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $userId,
            'rejection_reason' => null,
        ]);
    }

    public function reject(int $userId, string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $userId,
        ]);
    }

    // Static methods
    public static function getAverageRating(int $instructorId, ?int $tenantId = null): float
    {
        return static::where('instructor_id', $instructorId)
            ->where('status', 'approved')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->avg('rating') ?? 0;
    }

    public static function getTotalReviews(int $instructorId, ?int $tenantId = null): int
    {
        return static::where('instructor_id', $instructorId)
            ->where('status', 'approved')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->count();
    }

    public static function getRatingDistribution(int $instructorId, ?int $tenantId = null): array
    {
        $reviews = static::where('instructor_id', $instructorId)
            ->where('status', 'approved')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        return [
            5 => $reviews[5] ?? 0,
            4 => $reviews[4] ?? 0,
            3 => $reviews[3] ?? 0,
            2 => $reviews[2] ?? 0,
            1 => $reviews[1] ?? 0,
        ];
    }
}

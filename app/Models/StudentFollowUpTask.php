<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentFollowUpTask extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'group_id',
        'created_by',
        'assigned_to',
        'trigger_type',
        'source_type',
        'source_id',
        'academic_status',
        'title',
        'reason',
        'priority',
        'status',
        'outcome_notes',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => 'جديدة',
            self::STATUS_CONTACTED => 'تم التواصل',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(StudentFollowUpContact::class, 'task_id')->orderBy('contacted_at', 'desc');
    }

    public function latestContact(): HasOne
    {
        return $this->hasOne(StudentFollowUpContact::class, 'task_id')->latestOfMany('contacted_at');
    }

    public function reassignments(): HasMany
    {
        return $this->hasMany(StudentFollowUpTaskReassignment::class, 'task_id')->orderBy('reassigned_at', 'desc');
    }

    public function scopeNew($query)
    {
        return $query->whereIn('status', [self::STATUS_NEW, 'pending']);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_NEW, 'pending']);
    }

    public function scopeContacted($query)
    {
        return $query->whereIn('status', [self::STATUS_CONTACTED, 'completed']);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_NEW, 'pending', 'in_progress']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CONTACTED, 'completed' => 'تم التواصل',
            default => 'جديدة',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CONTACTED, 'completed' => 'success',
            default => 'warning',
        };
    }

    public function isContacted(): bool
    {
        return in_array($this->status, [self::STATUS_CONTACTED, 'completed']);
    }

    public function isNew(): bool
    {
        return in_array($this->status, [self::STATUS_NEW, 'pending', 'in_progress']);
    }

    /**
     * Mark the follow-up case as contacted (تم التواصل)
     */
    public function markContacted(?string $notes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_CONTACTED,
            'outcome_notes' => $notes ?? $this->outcome_notes,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Reassign the task to another employee while maintaining a full audit log
     */
    public function reassignTo(int|User $newAssignee, int|User $reassignedBy, ?string $reason = null): bool
    {
        $newAssigneeId = $newAssignee instanceof User ? $newAssignee->id : $newAssignee;
        $reassignedById = $reassignedBy instanceof User ? $reassignedBy->id : $reassignedBy;
        $previousAssigneeId = $this->assigned_to;

        // Create audit record
        StudentFollowUpTaskReassignment::create([
            'tenant_id' => $this->tenant_id,
            'task_id' => $this->id,
            'previous_assigned_to' => $previousAssigneeId,
            'new_assigned_to' => $newAssigneeId,
            'reassigned_by' => $reassignedById,
            'reassigned_at' => now(),
            'reason' => $reason,
        ]);

        return $this->update([
            'assigned_to' => $newAssigneeId,
        ]);
    }

    public function markCompleted(?string $notes = null): bool
    {
        return $this->markContacted($notes);
    }
}

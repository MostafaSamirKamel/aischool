<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFollowUpTaskReassignment extends Model
{
    use HasFactory, BelongsToBranch;

    protected $table = 'student_follow_up_task_reassignments';

    protected $fillable = [
        'tenant_id',
        'task_id',
        'previous_assigned_to',
        'new_assigned_to',
        'reassigned_by',
        'reassigned_at',
        'reason',
    ];

    protected $casts = [
        'reassigned_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(StudentFollowUpTask::class, 'task_id');
    }

    public function previousAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_assigned_to');
    }

    public function newAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_assigned_to');
    }

    public function reassignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reassigned_by');
    }
}

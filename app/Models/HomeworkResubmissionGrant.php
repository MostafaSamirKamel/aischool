<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeworkResubmissionGrant extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'assignment_id',
        'student_id',
        'granted_by',
        'expires_at',
        'reason',
        'consumed_at',
        'consumed_submission_id',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
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

    public function granter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function consumedSubmission(): BelongsTo
    {
        return $this->belongsTo(AssignmentSubmission::class, 'consumed_submission_id');
    }

    public function isValid(): bool
    {
        return $this->status === 'active'
            && $this->consumed_at === null
            && $this->expires_at->isFuture();
    }
}

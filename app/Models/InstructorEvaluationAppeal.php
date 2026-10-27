<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorEvaluationAppeal extends Model
{
    use HasFactory, BelongsToBranch;

    public const TYPE_COMMENT = 'comment';
    public const TYPE_APPEAL = 'appeal';

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_SUPERVISOR_RESPONDED = 'supervisor_responded';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_RETURNED_FOR_CLARIFICATION = 'returned_for_clarification';

    protected $fillable = [
        'tenant_id',
        'evaluation_id',
        'evaluation_version',
        'instructor_id',
        'type',
        'reason',
        'evidence',
        'supervisor_response',
        'supervisor_responded_at',
        'supervisor_id',
        'status',
        'management_decision',
        'management_decision_reason',
        'decided_by',
        'decided_at',
    ];

    protected $casts = [
        'evaluation_version' => 'integer',
        'evidence' => 'array',
        'supervisor_responded_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(InstructorEvaluation::class, 'evaluation_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function decisionMaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function decidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function isFormalAppeal(): bool
    {
        return $this->type === self::TYPE_APPEAL;
    }

    public function isComment(): bool
    {
        return $this->type === self::TYPE_COMMENT;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SUPERVISOR_RESPONDED => 'تم رد المشرف',
            self::STATUS_ACCEPTED => 'مقبول (معتمد للإعادة)',
            self::STATUS_REJECTED => 'مرفوض من الإدارة',
            self::STATUS_RETURNED_FOR_CLARIFICATION => 'معاد للاستيضاح',
            default => 'مقدم بانتظار المراجعة',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACCEPTED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_SUPERVISOR_RESPONDED => 'info',
            self::STATUS_RETURNED_FOR_CLARIFICATION => 'warning',
            default => 'gray',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_COMMENT => 'تعليق / توضيح',
            default => 'اعتراض رسمي',
        };
    }

    /**
     * Educational Supervisor responds with observations and evidence.
     */
    public function submitSupervisorResponse(User $supervisor, string $response): void
    {
        $this->update([
            'supervisor_response' => trim($response),
            'supervisor_id' => $supervisor->id,
            'supervisor_responded_at' => now(),
            'status' => self::STATUS_SUPERVISOR_RESPONDED,
        ]);
    }

    /**
     * Management employee makes the final decision on the formal appeal.
     */
    public function decideByManagement(User $manager, string $decision, string $reason): void
    {
        $status = match ($decision) {
            'accepted' => self::STATUS_ACCEPTED,
            'rejected' => self::STATUS_REJECTED,
            'returned_for_clarification' => self::STATUS_RETURNED_FOR_CLARIFICATION,
            default => self::STATUS_SUBMITTED,
        };

        $this->update([
            'management_decision' => $decision,
            'management_decision_reason' => trim($reason),
            'decided_by' => $manager->id,
            'decided_at' => now(),
            'status' => $status,
        ]);
    }
}

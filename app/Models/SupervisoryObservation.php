<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupervisoryObservation extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    public const STATUS_NEW = 'new';
    public const STATUS_WAITING_SUPERVISOR_REVIEW = 'waiting_supervisor_review';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'tenant_id',
        'observation_code',
        'supervisor_id',
        'instructor_id',
        'group_id',
        'attendance_session_id',
        'assignment_id',
        'exam_id',
        'category',
        'title',
        'description',
        'required_action',
        'expected_resolution_date',
        'priority',
        'status',
        'instructor_response',
        'instructor_responded_at',
        'verification_notes',
        'approved_by',
        'approved_at',
        'return_reason',
        'returned_at',
        'attachments',
    ];

    protected $casts = [
        'expected_resolution_date' => 'date',
        'instructor_responded_at' => 'datetime',
        'approved_at' => 'datetime',
        'returned_at' => 'datetime',
        'attachments' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($observation) {
            if (empty($observation->observation_code)) {
                $observation->observation_code = self::generateObservationCode();
            }
            if (empty($observation->status)) {
                $observation->status = self::STATUS_NEW;
            }
        });
    }

    /**
     * Generate globally unique observation code without tenant collisions.
     */
    public static function generateObservationCode(): string
    {
        $latestRecord = self::withoutGlobalScopes()
            ->withTrashed()
            ->whereNotNull('observation_code')
            ->where('observation_code', 'like', 'SOB-%')
            ->orderBy('observation_code', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latestRecord && preg_match('/SOB-(\d+)/', $latestRecord->observation_code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        $maxId = (int) self::withoutGlobalScopes()->withTrashed()->max('id');
        if ($maxId >= $nextNumber) {
            $nextNumber = $maxId + 1;
        }

        do {
            $code = 'SOB-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            $exists = self::withoutGlobalScopes()
                ->withTrashed()
                ->where('observation_code', $code)
                ->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $code;
    }

    // Relationships
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(SupervisoryObservationHistory::class, 'observation_id')->orderBy('created_at', 'desc');
    }

    // Status Helpers
    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isWaitingReview(): bool
    {
        return $this->status === self::STATUS_WAITING_SUPERVISOR_REVIEW;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_WAITING_SUPERVISOR_REVIEW => 'بانتظار مراجعة المشرف',
            self::STATUS_COMPLETED => 'مكتملة',
            default => 'جديدة',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_WAITING_SUPERVISOR_REVIEW => 'warning',
            self::STATUS_COMPLETED => 'success',
            default => 'info',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'attendance_not_recorded' => 'تأخر تسجيل الحضور',
            'homework_grading_delay' => 'تأخر تصحيح الواجبات',
            'examination_review' => 'مراجعة الامتحانات والدرجات',
            'teaching_quality' => 'جودة الشرح والتدريس',
            'incomplete_educational_content' => 'ملاحظات المحتوى التعليمي',
            'session_preparation' => 'جاهزية وتحضير السيشن',
            'student_academic_concern' => 'ملاحظة أكاديمية تخص الطلاب',
            default => 'ملاحظة إشرافية تعليمية عامة',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'منخفضة',
            'urgent' => 'عاجلة',
            default => 'عادية',
        };
    }

    public function getPriorityBadgeColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'gray',
            'urgent' => 'danger',
            default => 'warning',
        };
    }

    /**
     * Instructor responds and submits observation for supervisor review.
     */
    public function submitInstructorResponse(User $instructorUser, string $response): void
    {
        $previousStatus = $this->status;
        $this->update([
            'instructor_response' => trim($response),
            'instructor_responded_at' => now(),
            'status' => self::STATUS_WAITING_SUPERVISOR_REVIEW,
        ]);

        $this->histories()->create([
            'tenant_id' => $this->tenant_id,
            'actor_id' => $instructorUser->id,
            'action' => 'submitted_for_review',
            'previous_status' => $previousStatus,
            'new_status' => self::STATUS_WAITING_SUPERVISOR_REVIEW,
            'comment' => trim($response),
        ]);
    }

    /**
     * Supervisor approves completion of the observation.
     */
    public function approveCompletion(User $supervisor, ?string $verificationNotes = null): void
    {
        $previousStatus = $this->status;
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'approved_by' => $supervisor->id,
            'approved_at' => now(),
            'verification_notes' => $verificationNotes ? trim($verificationNotes) : null,
        ]);

        $this->histories()->create([
            'tenant_id' => $this->tenant_id,
            'actor_id' => $supervisor->id,
            'action' => 'approved_completed',
            'previous_status' => $previousStatus,
            'new_status' => self::STATUS_COMPLETED,
            'comment' => $verificationNotes ? trim($verificationNotes) : 'تم اعتماد معالجة الملاحظة بنجاح.',
        ]);
    }

    /**
     * Supervisor returns observation for correction (reverts to 'new').
     */
    public function returnForCorrection(User $supervisor, string $mandatoryReason): void
    {
        $previousStatus = $this->status;
        $this->update([
            'status' => self::STATUS_NEW,
            'return_reason' => trim($mandatoryReason),
            'returned_at' => now(),
        ]);

        $this->histories()->create([
            'tenant_id' => $this->tenant_id,
            'actor_id' => $supervisor->id,
            'action' => 'returned_for_correction',
            'previous_status' => $previousStatus,
            'new_status' => self::STATUS_NEW,
            'comment' => trim($mandatoryReason),
        ]);
    }
}

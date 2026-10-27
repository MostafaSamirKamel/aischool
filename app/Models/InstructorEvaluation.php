<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstructorEvaluation extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    public const TYPE_SESSION = 'session';
    public const TYPE_MONTHLY = 'monthly';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'tenant_id',
        'evaluation_code',
        'instructor_id',
        'supervisor_id',
        'evaluation_type',
        'evaluation_period',
        'attendance_session_id',
        'review_basis',
        'score_explanation_quality',
        'score_student_interaction',
        'score_practical_application',
        'score_curriculum_adherence',
        'score_time_management',
        'score_homework_followup',
        'calculated_average',
        'criteria_observed_count',
        'strengths',
        'areas_for_improvement',
        'recommendations',
        'status',
        'version',
        'published_at',
        'published_by',
        'management_notes',
    ];

    protected $casts = [
        'score_explanation_quality' => 'integer',
        'score_student_interaction' => 'integer',
        'score_practical_application' => 'integer',
        'score_curriculum_adherence' => 'integer',
        'score_time_management' => 'integer',
        'score_homework_followup' => 'integer',
        'calculated_average' => 'decimal:2',
        'criteria_observed_count' => 'integer',
        'version' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($evaluation) {
            if (empty($evaluation->evaluation_code)) {
                $evaluation->evaluation_code = self::generateEvaluationCode();
            }
            if (empty($evaluation->status)) {
                $evaluation->status = self::STATUS_DRAFT;
            }
            $evaluation->recalculateScoreMetrics();
        });

        static::updating(function ($evaluation) {
            $evaluation->recalculateScoreMetrics();
        });
    }

    /**
     * Generate globally unique evaluation code without tenant collisions.
     */
    public static function generateEvaluationCode(): string
    {
        $latestRecord = self::withoutGlobalScopes()
            ->withTrashed()
            ->whereNotNull('evaluation_code')
            ->where('evaluation_code', 'like', 'IEV-%')
            ->orderBy('evaluation_code', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latestRecord && preg_match('/IEV-(\d+)/', $latestRecord->evaluation_code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        $maxId = (int) self::withoutGlobalScopes()->withTrashed()->max('id');
        if ($maxId >= $nextNumber) {
            $nextNumber = $maxId + 1;
        }

        do {
            $code = 'IEV-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            $exists = self::withoutGlobalScopes()
                ->withTrashed()
                ->where('evaluation_code', $code)
                ->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $code;
    }

    /**
     * Calculate arithmetic average of ONLY observed criteria (excludes null / Not Observed).
     */
    public function recalculateScoreMetrics(): void
    {
        $criteria = [
            $this->score_explanation_quality,
            $this->score_student_interaction,
            $this->score_practical_application,
            $this->score_curriculum_adherence,
            $this->score_time_management,
            $this->score_homework_followup,
        ];

        $validScores = array_filter($criteria, fn($score) => !is_null($score) && is_numeric($score) && $score > 0);
        $count = count($validScores);

        $this->criteria_observed_count = $count;

        if ($count > 0) {
            $this->calculated_average = round(array_sum($validScores) / $count, 2);
        } else {
            $this->calculated_average = null;
        }
    }

    // Relationships
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(InstructorEvaluationAppeal::class, 'evaluation_id')->orderBy('created_at', 'desc');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(InstructorEvaluationVersion::class, 'evaluation_id')->orderBy('version', 'desc');
    }

    // Status & Type Helpers
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isMonthly(): bool
    {
        return $this->evaluation_type === self::TYPE_MONTHLY;
    }

    public function isSession(): bool
    {
        return $this->evaluation_type === self::TYPE_SESSION;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED => 'منشور',
            default => 'مسودة',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED => 'success',
            default => 'gray',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->evaluation_type) {
            self::TYPE_SESSION => 'تقييم سيشن (اختياري)',
            default => 'تقييم شهري رسمي',
        };
    }

    public function getReviewBasisLabelAttribute(): string
    {
        return match ($this->review_basis) {
            'recording_review' => 'مراجعة تسجيل الحصة',
            'educational_work_review' => 'مراجعة الأعمال والمحتوى',
            'monthly_evidence_review' => 'مراجعة الشواهد والتقارير الشهرية',
            default => 'ملاحظة مباشرة',
        };
    }

    /**
     * Publish official evaluation.
     */
    public function publish(User $supervisor): void
    {
        $this->update([
            'status' => self::STATUS_PUBLISHED,
            'published_by' => $supervisor->id,
            'published_at' => now(),
        ]);
    }

    /**
     * Create snapshot in version history before modification.
     */
    public function recordVersionSnapshot(?string $reason = null, ?User $actor = null): void
    {
        $this->versions()->create([
            'tenant_id' => $this->tenant_id,
            'version' => $this->version,
            'snapshot' => $this->toArray(),
            'change_reason' => $reason,
            'changed_by' => $actor?->id,
        ]);

        $this->increment('version');
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupervisionReport extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    public const TYPE_WEEKLY = 'weekly';
    public const TYPE_MONTHLY = 'monthly';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_REVISION_REQUESTED = 'revision_requested';

    protected $fillable = [
        'tenant_id',
        'report_code',
        'report_type',
        'period_start',
        'period_end',
        'period_key',
        'version',
        'parent_report_id',
        'metrics_snapshot',
        'summary',
        'instructor_observations',
        'group_observations',
        'problems_encountered',
        'actions_taken',
        'recommendations',
        'status',
        'prepared_by',
        'published_by',
        'published_at',
        'management_comments',
        'clarification_requests',
        'revision_reason',
        'revision_requested_by',
        'revision_requested_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'version' => 'integer',
        'metrics_snapshot' => 'array',
        'management_comments' => 'array',
        'clarification_requests' => 'array',
        'published_at' => 'datetime',
        'revision_requested_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (empty($report->report_code)) {
                $report->report_code = self::generateReportCode($report->report_type ?? self::TYPE_WEEKLY, $report->period_key ?? date('Y-m'));
            }
            if (empty($report->status)) {
                $report->status = self::STATUS_DRAFT;
            }
        });
    }

    /**
     * Generate globally unique report code without tenant collisions.
     */
    public static function generateReportCode(string $type, string $periodKey): string
    {
        $prefix = ($type === self::TYPE_WEEKLY ? 'SR-WK-' : 'SR-MO-') . str_replace(['/', ' '], '-', $periodKey) . '-';

        $latestRecord = self::withoutGlobalScopes()
            ->withTrashed()
            ->whereNotNull('report_code')
            ->where('report_code', 'like', $prefix . '%')
            ->orderBy('report_code', 'desc')
            ->first();

        $number = 1;
        if ($latestRecord) {
            $lastDigits = (int) substr($latestRecord->report_code, -4);
            $number = $lastDigits + 1;
        }

        do {
            $code = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
            $exists = self::withoutGlobalScopes()
                ->withTrashed()
                ->where('report_code', $code)
                ->exists();
            if ($exists) {
                $number++;
            }
        } while ($exists);

        return $code;
    }

    // Relationships
    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function revisionRequester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revision_requested_by');
    }

    public function parentReport(): BelongsTo
    {
        return $this->belongsTo(SupervisionReport::class, 'parent_report_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(SupervisionReport::class, 'parent_report_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(SupervisionReportVersion::class, 'report_id')->orderBy('version', 'desc');
    }

    // Status Helpers
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isRevisionRequested(): bool
    {
        return $this->status === self::STATUS_REVISION_REQUESTED;
    }

    public function isWeekly(): bool
    {
        return $this->report_type === self::TYPE_WEEKLY;
    }

    public function isMonthly(): bool
    {
        return $this->report_type === self::TYPE_MONTHLY;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED => 'منشور للإدارة',
            self::STATUS_REVISION_REQUESTED => 'مطلوب تعديل ومراجعة',
            default => 'مسودة قيد المراجعة',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED => 'success',
            self::STATUS_REVISION_REQUESTED => 'warning',
            default => 'gray',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->report_type) {
            self::TYPE_WEEKLY => 'تقرير إشرافي أسبوعي',
            default => 'تقرير إدارة تعليمية شهري',
        };
    }

    /**
     * Supervisor explicitly approves and publishes report to management.
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
     * Management adds review comment.
     */
    public function addManagementComment(User $manager, string $comment): void
    {
        $comments = $this->management_comments ?? [];
        $comments[] = [
            'manager_id' => $manager->id,
            'manager_name' => $manager->name,
            'comment' => trim($comment),
            'created_at' => now()->toDateTimeString(),
        ];

        $this->update(['management_comments' => $comments]);
    }

    /**
     * Management requests clarification from the supervisor.
     */
    public function requestClarification(User $manager, string $question): void
    {
        $requests = $this->clarification_requests ?? [];
        $requests[] = [
            'id' => count($requests) + 1,
            'manager_id' => $manager->id,
            'manager_name' => $manager->name,
            'question' => trim($question),
            'response' => null,
            'responded_at' => null,
            'created_at' => now()->toDateTimeString(),
        ];

        $this->update(['clarification_requests' => $requests]);
    }

    /**
     * Supervisor responds to clarification request.
     */
    public function respondToClarification(User $supervisor, int $requestId, string $response): void
    {
        $requests = $this->clarification_requests ?? [];
        foreach ($requests as &$req) {
            if (($req['id'] ?? null) === $requestId) {
                $req['response'] = trim($response);
                $req['responder_id'] = $supervisor->id;
                $req['responder_name'] = $supervisor->name;
                $req['responded_at'] = now()->toDateTimeString();
                break;
            }
        }

        $this->update(['clarification_requests' => $requests]);
    }

    /**
     * Management requests revision for the report.
     */
    public function requestRevision(User $manager, string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REVISION_REQUESTED,
            'revision_reason' => trim($reason),
            'revision_requested_by' => $manager->id,
            'revision_requested_at' => now(),
        ]);
    }

    /**
     * Record snapshot in version history before modification.
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

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstructorPayment extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'instructor_id',
        'period_start',
        'period_end',
        'period_label',
        'total_sessions',
        'completed_sessions',
        'cancelled_sessions',
        'rate_per_session',
        'base_amount',
        'sessions_amount',
        'bonus_amount',
        'deduction_amount',
        'total_amount',
        'status',
        'payment_date',
        'payment_method',
        'payment_reference',
        'notes',
        'approved_at',
        'approved_by',
        'paid_by',
        'sessions_data',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'sessions_data' => 'array',
        'rate_per_session' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'sessions_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($payment) {
            // Auto-calculate total
            $payment->calculateTotal();
        });
    }

    public function setPeriodStartAttribute($value): void
    {
        $this->attributes['period_start'] = $value ? Carbon::parse($value)->toDateString() : null;
    }

    public function setPeriodEndAttribute($value): void
    {
        $this->attributes['period_end'] = $value ? Carbon::parse($value)->toDateString() : null;
    }

    // Relations
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeForInstructor($query, int $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeForPeriod($query, string $start, string $end)
    {
        return $query->whereBetween('period_start', [$start, $end]);
    }

    public function scopeCurrentMonth($query)
    {
        return $query->where('period_start', '>=', now()->startOfMonth())
            ->where('period_end', '<=', now()->endOfMonth());
    }

    // Helper methods
    public function calculateTotal(): void
    {
        $this->total_amount = (float) $this->base_amount
            + (float) $this->sessions_amount
            + (float) $this->bonus_amount
            - (float) $this->deduction_amount;
    }

    public function calculateSessionsAmount(): void
    {
        $this->sessions_amount = (float) $this->completed_sessions * (float) $this->rate_per_session;
    }

    public function approve(int $userId): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $userId,
        ]);
    }

    public function markPaid(int $userId, string $method, ?string $reference = null, ?string $date = null): bool
    {
        return $this->update([
            'status' => 'paid',
            'payment_date' => $date ?? now()->toDateString(),
            'payment_method' => $method,
            'payment_reference' => $reference,
            'paid_by' => $userId,
        ]);
    }

    public function cancel(): bool
    {
        return $this->update(['status' => 'cancelled']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'قيد المراجعة',
            'approved' => 'معتمد',
            'paid' => 'مدفوع',
            'cancelled' => 'ملغي',
            default => $this->status,
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        if (!$this->payment_method) return 'غير محدد';

        return match ($this->payment_method) {
            'cash' => 'نقدي',
            'bank_transfer' => 'تحويل بنكي',
            'check' => 'شيك',
            'online' => 'دفع إلكتروني',
            default => $this->payment_method,
        };
    }

    /**
     * Generate payment for instructor for specific period
     */
    public static function generateForInstructor(
        int $instructorId,
        int $tenantId,
        Carbon $periodStart,
        Carbon $periodEnd,
        float $ratePerSession,
        float $baseAmount = 0
    ): self {
        $instructor = Instructor::find($instructorId);

        // Get attendance sessions in period (completed sessions, not yet paid)
        $sessions = AttendanceSession::whereHas('group', fn($q) => $q->where('instructor_id', $instructorId))
            ->where('tenant_id', $tenantId)
            ->whereBetween('attendance_date', [$periodStart, $periodEnd])
            ->whereNull('salary_payment_id')
            ->with('group')
            ->get();

        $totalSessions = $sessions->count();
        $completedSessions = $totalSessions; // All attendance sessions are considered completed
        $cancelledSessions = 0; // No cancelled concept in attendance sessions

        $sessionsAmount = $completedSessions * $ratePerSession;

        // Prepare sessions data
        $sessionsData = $sessions->map(function ($session) use ($ratePerSession) {
            return [
                'id' => $session->id,
                'date' => $session->attendance_date->format('Y-m-d'),
                'group' => $session->group->name ?? 'غير محدد',
                'rate' => $ratePerSession,
            ];
        })->toArray();

        // Fetch existing to preserve bonus/deduction/status values
        $existing = static::withoutGlobalScopes()->where([
            'tenant_id'      => $tenantId,
            'instructor_id'  => $instructorId,
            'period_start'   => $periodStart,
            'period_end'     => $periodEnd,
        ])->first();

        $bonusAmount     = $existing?->bonus_amount ?? 0;
        $deductionAmount = $existing?->deduction_amount ?? 0;
        $status          = $existing?->status ?? 'pending';

        $shouldReopenPayment = static::shouldReopenAfterSessionChange($existing, (int) $completedSessions);

        if ($shouldReopenPayment) {
            $status = 'pending';
        }

        $payment = static::withoutGlobalScopes()->updateOrCreate(
            [
                'tenant_id'     => $tenantId,
                'instructor_id' => $instructorId,
                'period_start'  => $periodStart,
                'period_end'    => $periodEnd,
            ],
            [
                'period_label'       => $periodStart->format('F Y'),
                'total_sessions'     => $totalSessions,
                'completed_sessions' => $completedSessions,
                'cancelled_sessions' => $cancelledSessions,
                'rate_per_session'   => $ratePerSession,
                'base_amount'        => $baseAmount,
                'sessions_amount'    => $sessionsAmount,
                'bonus_amount'       => $bonusAmount,
                'deduction_amount'   => $deductionAmount,
                'total_amount'       => $baseAmount + $sessionsAmount + $bonusAmount - $deductionAmount,
                'status'             => $status,
                'sessions_data'      => $sessionsData,
            ]
        );

        return $payment;
    }

    /**
     * Generate monthly payment for all instructors
     */
    public static function generateMonthlyPayments(int $tenantId, ?Carbon $month = null): array
    {
        $month = $month ?? now();
        $periodStart = $month->copy()->startOfMonth();
        $periodEnd = $month->copy()->endOfMonth();

        $instructors = Instructor::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();

        $generated = [];

        foreach ($instructors as $instructor) {
            // Check if payment already exists
            $exists = static::where('instructor_id', $instructor->id)
                ->where('period_start', $periodStart)
                ->where('period_end', $periodEnd)
                ->exists();

            if (!$exists) {
                $payment = static::generateForInstructor(
                    $instructor->id,
                    $tenantId,
                    $periodStart,
                    $periodEnd,
                    $instructor->hourly_rate ?? 100, // Default rate
                    $instructor->base_salary ?? 0
                );

                $generated[] = $payment;
            }
        }

        return $generated;
    }

    /**
     * Update or create payment for instructor when new session is recorded
     * Called automatically after AttendanceSession is created
     */
    public static function updateOrCreateForSession(AttendanceSession $session): ?self
    {
        // Get instructor from group
        $group = $session->group ?? Group::withoutGlobalScopes()->find($session->group_id);
        if (!$group || !$group->instructor_id) {
            return null;
        }

        $instructor = Instructor::withoutGlobalScopes()->find($group->instructor_id);
        if (!$instructor) {
            return null;
        }

        $sessionDate = \Carbon\Carbon::parse($session->attendance_date);
        $periodStart = $sessionDate->copy()->startOfMonth();
        $periodEnd = $sessionDate->copy()->endOfMonth();
        $periodStartDate = $periodStart->toDateString();
        $periodEndDate = $periodEnd->toDateString();

        // Calculate all sessions in this period
        $sessions = AttendanceSession::withoutGlobalScopes()
            ->whereHas('group', fn($q) => $q->withoutGlobalScopes()->where('instructor_id', $instructor->id))
            ->where('tenant_id', $session->tenant_id)
            ->whereBetween('attendance_date', [$periodStartDate, $periodEndDate])
            ->whereNull('salary_payment_id')
            ->with(['group' => fn($q) => $q->withoutGlobalScopes()])
            ->get();

        $totalSessions = $sessions->count();
        $completedSessions = $totalSessions;

        $ratePerSession = $instructor->hourly_rate ?? 100;
        $baseAmount = $instructor->base_salary ?? 0;
        $sessionsAmount = $completedSessions * $ratePerSession;

        // Prepare sessions data
        $sessionsData = $sessions->map(function ($sess) use ($ratePerSession) {
            return [
                'id' => $sess->id,
                'date' => $sess->attendance_date->format('Y-m-d'),
                'group' => $sess->group->name ?? 'غير محدد',
                'rate' => $ratePerSession,
            ];
        })->toArray();

        // Fetch existing to preserve bonus/deduction/status
        $existing = static::withoutGlobalScopes()->where([
            'instructor_id' => $instructor->id,
            'tenant_id'     => $session->tenant_id,
            'period_start'  => $periodStartDate,
            'period_end'    => $periodEndDate,
        ])->first();

        $bonusAmount     = $existing?->bonus_amount ?? 0;
        $deductionAmount = $existing?->deduction_amount ?? 0;
        $status          = $existing?->status ?? 'pending';

        $shouldReopenPayment = static::shouldReopenAfterSessionChange($existing, (int) $completedSessions);

        if ($shouldReopenPayment) {
            $status = 'pending';
        }

        // Use updateOrCreate (atomic) to avoid duplicate key errors
        $payment = static::withoutGlobalScopes()->updateOrCreate(
            [
                'instructor_id' => $instructor->id,
                'tenant_id'     => $session->tenant_id,
                'period_start'  => $periodStartDate,
                'period_end'    => $periodEndDate,
            ],
            [
                'period_label'       => $periodStart->format('F Y'),
                'total_sessions'     => $totalSessions,
                'completed_sessions' => $completedSessions,
                'cancelled_sessions' => 0,
                'rate_per_session'   => $ratePerSession,
                'base_amount'        => $baseAmount,
                'sessions_amount'    => $sessionsAmount,
                'bonus_amount'       => $bonusAmount,
                'deduction_amount'   => $deductionAmount,
                'total_amount'       => $sessionsAmount + $baseAmount + $bonusAmount - $deductionAmount,
                'sessions_data'      => $sessionsData,
                'status'             => $status,
                'approved_at'        => $shouldReopenPayment ? null : ($existing?->approved_at),
                'approved_by'        => $shouldReopenPayment ? null : ($existing?->approved_by),
                'payment_date'       => $shouldReopenPayment ? null : ($existing?->payment_date),
                'payment_method'     => $shouldReopenPayment ? null : ($existing?->payment_method),
                'payment_reference'  => $shouldReopenPayment ? null : ($existing?->payment_reference),
                'paid_by'            => $shouldReopenPayment ? null : ($existing?->paid_by),
            ]
        );

        return $payment;
    }

    public static function shouldReopenAfterSessionChange(?self $existing, int $completedSessions): bool
    {
        if (!$existing) {
            return false;
        }

        return in_array((string) $existing->status, ['paid', 'cancelled'], true)
            && ((int) $existing->completed_sessions !== $completedSessions);
    }
}

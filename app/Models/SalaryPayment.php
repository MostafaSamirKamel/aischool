<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryPayment extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'employee_type',
        'instructor_id',
        'admin_id',
        'amount',
        'payment_date',
        'payment_method',
        'vault_id',
        'period_start',
        'period_end',
        'sessions_count',
        'status',
        'notes',
        'created_by',
        'paid_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->created_by)) {
                $payment->created_by = auth()->id();
            }
        });

        static::saving(function ($payment) {
            if ($payment->status === 'paid' && (float) $payment->amount > 0) {
                if (empty($payment->vault_id)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'vault_id' => 'الخزنة المنصرف منها إلزامية لصرف الراتب.',
                    ]);
                }

                $vault = Vault::find($payment->vault_id);
                if (!$vault || !$vault->is_active) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'vault_id' => 'الخزنة المحددة غير صالحة أو غير نشطة.',
                    ]);
                }

                if (!empty($payment->tenant_id) && !empty($vault->tenant_id) && (int) $payment->tenant_id !== (int) $vault->tenant_id) {
                    throw new \Illuminate\Auth\Access\AuthorizationException('الخزنة المحددة لا تنتمي لنفس فرع صرف الراتب.');
                }

                $originalAmount = 0.0;
                if ($payment->exists && $payment->getOriginal('vault_id') == $payment->vault_id && $payment->getOriginal('status') === 'paid') {
                    $originalAmount = (float) $payment->getOriginal('amount');
                }
                $vault->assertSufficientBalance((float) $payment->amount, $originalAmount);

                // Auto-set payment_method from vault type for audit consistency
                if (empty($payment->payment_method) || $payment->payment_method === 'cash') {
                    $payment->payment_method = match ($vault->type) {
                        'cash' => 'cash',
                        'vodafone', 'instapay', 'mobile_wallet', 'wallet' => 'mobile_wallet',
                        'bank' => 'bank_transfer',
                        default => 'cash',
                    };
                }
            } elseif ($payment->vault_id && (float) $payment->amount > 0) {
                $vault = Vault::find($payment->vault_id);
                if ($vault && !$vault->is_active) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'vault_id' => 'الخزنة المحددة غير نشطة.',
                    ]);
                }
            }
        });

        // When a payment is marked as paid, link attendance sessions to it
        static::saved(function ($payment) {
            if ($payment->status === 'paid'
                && $payment->employee_type === 'instructor'
                && $payment->instructor_id
            ) {
                static::syncInstructorPaymentsForPaidSalary($payment);
            }
        });

        // When a paid payment is cancelled or reversed, unlink sessions so they count again
        static::updating(function ($payment) {
            if ($payment->getOriginal('status') === 'paid'
                && $payment->status !== 'paid'
            ) {
                AttendanceSession::withoutGlobalScopes()
                    ->where('salary_payment_id', $payment->id)
                    ->update(['salary_payment_id' => null]);

                static::revertInstructorPaymentsForUnpaidSalary($payment);
            }
        });
    }

    private static function syncInstructorPaymentsForPaidSalary(self $payment): void
    {
        $paidDate = $payment->payment_date
            ? Carbon::parse($payment->payment_date)->toDateString()
            : now()->toDateString();

        $paidBy = $payment->paid_by ?? auth()->id();
        $reference = 'SALARY-' . $payment->id;

        if ($payment->period_start && $payment->period_end) {
            $startDate = Carbon::parse($payment->period_start)->toDateString();
            $endDate = Carbon::parse($payment->period_end)->toDateString();

            InstructorPayment::withoutGlobalScopes()
                ->where('tenant_id', $payment->tenant_id)
                ->where('instructor_id', $payment->instructor_id)
                ->whereDate('period_start', $startDate)
                ->whereDate('period_end', $endDate)
                ->update([
                    'status' => 'paid',
                    'payment_date' => $paidDate,
                    'payment_method' => $payment->payment_method,
                    'payment_reference' => $reference,
                    'paid_by' => $paidBy,
                ]);

            static::linkAttendanceSessionsToSalary($payment, Carbon::parse($startDate), Carbon::parse($endDate));

            return;
        }

        // Legacy fallback: old salary rows may not have period_start/period_end.
        $alreadyLinked = InstructorPayment::withoutGlobalScopes()
            ->where('tenant_id', $payment->tenant_id)
            ->where('instructor_id', $payment->instructor_id)
            ->where('payment_reference', $reference)
            ->exists();

        if ($alreadyLinked) {
            return;
        }

        $remainingAmount = (float) $payment->amount;
        if ($remainingAmount <= 0) {
            return;
        }

        $snapshots = InstructorPayment::withoutGlobalScopes()
            ->where('tenant_id', $payment->tenant_id)
            ->where('instructor_id', $payment->instructor_id)
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('period_start')
            ->get();

        $firstPaidPeriodStart = null;
        $lastPaidPeriodEnd = null;
        $totalSessions = 0;

        foreach ($snapshots as $snapshot) {
            $snapshotAmount = (float) $snapshot->total_amount;
            if ($snapshotAmount <= 0) {
                continue;
            }

            if ($remainingAmount + 0.0001 < $snapshotAmount) {
                break;
            }

            $snapshot->update([
                'status' => 'paid',
                'payment_date' => $paidDate,
                'payment_method' => $payment->payment_method,
                'payment_reference' => $reference,
                'paid_by' => $paidBy,
            ]);

            if ($snapshot->period_start && $snapshot->period_end) {
                static::linkAttendanceSessionsToSalary($payment, Carbon::parse($snapshot->period_start), Carbon::parse($snapshot->period_end));
            }

            if (!$firstPaidPeriodStart) {
                $firstPaidPeriodStart = $snapshot->period_start;
            }
            $lastPaidPeriodEnd = $snapshot->period_end;
            $totalSessions += (int) ($snapshot->completed_sessions ?? 0);

            $remainingAmount -= $snapshotAmount;
            if ($remainingAmount <= 0.0001) {
                break;
            }
        }

        if ($firstPaidPeriodStart && $lastPaidPeriodEnd && Schema::hasColumn('salary_payments', 'period_start') && Schema::hasColumn('salary_payments', 'period_end')) {
            $updates = [
                'period_start' => Carbon::parse($firstPaidPeriodStart)->toDateString(),
                'period_end' => Carbon::parse($lastPaidPeriodEnd)->toDateString(),
            ];

            if (Schema::hasColumn('salary_payments', 'sessions_count')) {
                $updates['sessions_count'] = $totalSessions;
            }

            $payment->updateQuietly($updates);
        }
    }

    private static function revertInstructorPaymentsForUnpaidSalary(self $payment): void
    {
        if ($payment->employee_type !== 'instructor' || !$payment->instructor_id) {
            return;
        }

        $baseQuery = InstructorPayment::withoutGlobalScopes()
            ->where('tenant_id', $payment->tenant_id)
            ->where('instructor_id', $payment->instructor_id)
            ->where('status', 'paid');

        if ($payment->period_start && $payment->period_end) {
            $baseQuery
                ->whereDate('period_start', Carbon::parse($payment->period_start)->toDateString())
                ->whereDate('period_end', Carbon::parse($payment->period_end)->toDateString());
        } else {
            $baseQuery->where('payment_reference', 'SALARY-' . $payment->id);
        }

        $baseQuery->update([
            'status' => 'approved',
            'payment_date' => null,
            'payment_method' => null,
            'payment_reference' => null,
            'paid_by' => null,
        ]);
    }

    private static function linkAttendanceSessionsToSalary(self $payment, Carbon $startDate, Carbon $endDate): void
    {
        if (!Schema::hasColumn('attendance_sessions', 'salary_payment_id')) {
            return;
        }

        $groupIds = Group::withoutGlobalScopes()
            ->where('tenant_id', $payment->tenant_id)
            ->where('instructor_id', $payment->instructor_id)
            ->pluck('id');

        if ($groupIds->isEmpty()) {
            return;
        }

        AttendanceSession::withoutGlobalScopes()
            ->whereIn('group_id', $groupIds)
            ->where('tenant_id', $payment->tenant_id)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereNull('salary_payment_id')
            ->update(['salary_payment_id' => $payment->id]);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function vault(): BelongsTo
    {
        return $this->belongsTo(Vault::class);
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function getEmployeeNameAttribute(): string
    {
        if ($this->employee_type === 'instructor') {
            return $this->instructor?->user?->name ?? 'غير محدد';
        }

        if ($this->employee_type === 'admin') {
            return $this->admin?->user?->name ?? 'غير محدد';
        }

        return 'غير محدد';
    }
}

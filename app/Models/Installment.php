<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Installment extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'enrollment_id',
        'installment_number',
        'amount',
        'due_date',
        'paid_amount',
        'paid_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'installment_number' => 'integer',
    ];

    /**
     * Get the enrollment for this installment
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Calculate the actual status based on current payment state and due state
     * @return string 'paid' | 'partial' | 'overdue' | 'pending' | 'cancelled'
     */
    public function getActualStatus(): string
    {
        // If marked as cancelled or waived, return that status
        if (in_array($this->status, ['cancelled', 'waived'])) {
            return $this->status;
        }

        // If parent enrollment is cancelled, obligation is noncollectible
        if ($this->enrollment?->status === 'cancelled') {
            return 'cancelled';
        }

        // Check if fully paid
        if ($this->isFullyPaid()) {
            return 'paid';
        }

        // Check if overdue (past due date or marked overdue, and not fully paid)
        if ($this->isOverdue()) {
            return 'overdue';
        }

        // Check if partially paid (and not overdue)
        if ($this->isPartiallyPaid()) {
            return 'partial';
        }

        // Default to pending
        return 'pending';
    }

    /**
     * Check if installment is overdue (past due date or marked overdue, and not fully settled)
     */
    public function isOverdue(): bool
    {
        if (in_array($this->status, ['cancelled', 'waived']) || $this->isFullyPaid() || $this->enrollment?->status === 'cancelled') {
            return false;
        }

        if ($this->status === 'overdue') {
            return true;
        }

        if ($this->due_date) {
            return now()->startOfDay()->gt(\Carbon\Carbon::parse($this->due_date)->startOfDay());
        }

        return false;
    }

    /**
     * Get distinct Payment State: 'unpaid' | 'partial' | 'paid'
     */
    public function getPaymentState(): string
    {
        if ($this->isFullyPaid()) {
            return 'paid';
        }

        if ($this->isPartiallyPaid()) {
            return 'partial';
        }

        return 'unpaid';
    }

    /**
     * Get distinct Due State: 'settled' | 'overdue' | 'due' | 'upcoming' | 'cancelled'
     */
    public function getDueState(): string
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        if ($this->isFullyPaid()) {
            return 'settled';
        }

        if ($this->isOverdue()) {
            return 'overdue';
        }

        if ($this->due_date && now()->startOfDay()->equalTo(\Carbon\Carbon::parse($this->due_date)->startOfDay())) {
            return 'due';
        }

        return 'upcoming';
    }

    /**
     * Get remaining amount
     */
    public function getRemainingAmount(): float
    {
        return max(0, $this->amount - $this->paid_amount);
    }

    /**
     * Get remaining amount attribute
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->getRemainingAmount();
    }

    /**
     * Check if fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->amount;
    }

    /**
     * Get is_paid attribute
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->isFullyPaid();
    }

    /**
     * Check if partially paid
     */
    public function isPartiallyPaid(): bool
    {
        return $this->paid_amount > 0 && $this->paid_amount < $this->amount;
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(float $amount = null, $date = null): void
    {
        $this->paid_amount = $amount ?? $this->amount;
        $this->paid_date = $date ?? now();
        $this->status = $this->isFullyPaid() ? 'paid' : ($this->isOverdue() ? 'overdue' : 'partial');
        $this->save();
    }

    /**
     * Update status based on current payment and due state
     */
    public function updateStatus(): void
    {
        if ($this->isFullyPaid()) {
            $this->status = 'paid';
        } elseif ($this->isOverdue()) {
            $this->status = 'overdue';
        } elseif ($this->isPartiallyPaid()) {
            $this->status = 'partial';
        } else {
            $this->status = 'pending';
        }
        $this->save();
    }

    /**
     * Scope for overdue installments (not cancelled, not waived, not fully paid, active enrollment, past due date or status=overdue)
     */
    public function scopeOverdue($query)
    {
        return $query
            ->whereNotIn('status', ['cancelled', 'paid', 'waived'])
            ->whereColumn('paid_amount', '<', 'amount')
            ->whereHas('enrollment', fn($q) => $q->where('status', '!=', 'cancelled'))
            ->where(function ($q) {
                $q->where('due_date', '<', now()->startOfDay())
                  ->orWhere('status', 'overdue');
            });
    }

    /**
     * Scope for open / unpaid collectible installments
     */
    public function scopeOpen($query)
    {
        return $query
            ->whereNotIn('status', ['cancelled', 'paid', 'waived'])
            ->whereColumn('paid_amount', '<', 'amount')
            ->whereHas('enrollment', fn($q) => $q->where('status', '!=', 'cancelled'));
    }

    /**
     * Scope for open collectible installments due today
     */
    public function scopeDueToday($query)
    {
        return $query
            ->whereNotIn('status', ['cancelled', 'paid', 'waived'])
            ->whereColumn('paid_amount', '<', 'amount')
            ->whereHas('enrollment', fn($q) => $q->where('status', '!=', 'cancelled'))
            ->whereDate('due_date', now()->format('Y-m-d'));
    }

    /**
     * Scope for upcoming open collectible installments
     */
    public function scopeUpcoming($query)
    {
        return $query
            ->whereNotIn('status', ['cancelled', 'paid', 'waived'])
            ->whereColumn('paid_amount', '<', 'amount')
            ->whereHas('enrollment', fn($q) => $q->where('status', '!=', 'cancelled'))
            ->whereDate('due_date', '>', now()->format('Y-m-d'));
    }

    /**
     * Scope for fully paid installments (historical records)
     */
    public function scopePaid($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'paid')
              ->orWhereColumn('paid_amount', '>=', 'amount');
        });
    }

    /**
     * Scope for pending installments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for paid installments
     */
    public function reminderLogs()
    {
        return $this->hasMany(InstallmentReminderLog::class);
    }

    /**
     * Send payment reminder (Phase 11)
     */
    public function sendReminder(string $channel = 'in_app'): ?InstallmentReminderLog
    {
        $student = $this->enrollment?->student;
        if (!$student) {
            return null;
        }

        $reminderType = $this->isOverdue()
            ? 'overdue'
            : ($this->due_date && now()->startOfDay()->equalTo(\Carbon\Carbon::parse($this->due_date)->startOfDay()) ? 'due_today' : 'upcoming');

        return InstallmentReminderLog::create([
            'tenant_id' => $this->tenant_id ?? $student->tenant_id,
            'installment_id' => $this->id,
            'recipient_type' => 'student',
            'recipient_id' => $student->id,
            'reminder_type' => $reminderType,
            'channel' => $channel,
            'scheduled_at' => now(),
            'sent_at' => now(),
            'status' => 'sent',
        ]);
    }
}

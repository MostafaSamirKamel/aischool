<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vault extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'opening_balance',
        'description',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function booted()
    {
        static::deleting(function ($vault) {
            // التحقق من وجود مدفوعات
            if ($vault->payments()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف الخزنة. يوجد :count مدفوعة مرتبطة بهذه الخزنة.',
                    ['count' => $vault->payments()->count()]
                ));
            }

            // التحقق من وجود تسجيلات
            if ($vault->enrollments()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف الخزنة. يوجد :count تسجيل مرتبط بهذه الخزنة.',
                    ['count' => $vault->enrollments()->count()]
                ));
            }

            // التحقق من وجود إيرادات
            if ($vault->revenues()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف الخزنة. يوجد :count إيراد مرتبط بهذه الخزنة.',
                    ['count' => $vault->revenues()->count()]
                ));
            }

            // التحقق من وجود مصروفات
            if ($vault->expenses()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف الخزنة. يوجد :count مصروف مرتبط بهذه الخزنة.',
                    ['count' => $vault->expenses()->count()]
                ));
            }

            // التحقق من وجود تحويلات
            if ($vault->transfersFrom()->exists() || $vault->transfersTo()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف الخزنة. يوجد تحويلات مرتبطة بهذه الخزنة.'
                ));
            }

            // التحقق من وجود مدفوعات رواتب
            if ($vault->salaryPayments()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف الخزنة. يوجد :count صرف راتب مرتبط بهذه الخزنة.',
                    ['count' => $vault->salaryPayments()->count()]
                ));
            }
        });
    }

    // Relations
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function revenues()
    {
        return $this->hasMany(Revenue::class);
    }

    public function studentPayments()
    {
        return $this->hasMany(StudentPayment::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function transfersFrom()
    {
        return $this->hasMany(VaultTransfer::class, 'from_vault_id');
    }

    public function transfersTo()
    {
        return $this->hasMany(VaultTransfer::class, 'to_vault_id');
    }

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function refundedEnrollments()
    {
        return $this->hasMany(Enrollment::class, 'refund_vault_id');
    }

    // Scope for active vaults
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Calculate current balance
    public function getCurrentBalance(): float
    {
        // 1. الرصيد الافتتاحي
        $balance = (float) $this->opening_balance;

        // 2. الإيرادات المباشرة المستلمة في الخزنة (غير المرتبطة بمدفوعات لتجنب الازدواج)
        $standaloneRevenues = (float) $this->revenues()
            ->whereNull('payment_id')
            ->sum('amount');

        // 3. المدفوعات المكتملة في جدول payments المرتبطة بهذه الخزنة
        $payments = (float) $this->payments()
            ->where('status', 'completed')
            ->sum('amount');

        // 4. الدفعات المقدمة المباشرة في التسجيلات
        $today = now()->toDateString();
        $enrollmentUpfronts = 0.0;
        $enrollments = $this->enrollments()
            ->where(function ($q) use ($today) {
                $q->whereDate('enrollment_date', '<=', $today)
                    ->orWhereNull('enrollment_date');
            })
            ->get();

        foreach ($enrollments as $enrollment) {
            if ($enrollment->payment_plan === 'installments' && $enrollment->installments()->exists()) {
                $enrollmentUpfronts += $enrollment->getCalculatedUpfrontAmount();
            } else {
                $enrollmentUpfronts += (float) $enrollment->paid_amount;
            }
        }

        // 5. المدفوعات القديمة من جدول student_payments (إذا كان موجوداً)
        $studentPayments = 0.0;
        if (\Schema::hasTable('student_payments')) {
            $studentPayments = (float) $this->studentPayments()
                ->where('status', 'completed')
                ->sum('net_amount');
        }

        // 6. التحويلات الواردة
        $transfersIn = (float) $this->transfersTo()
            ->where('status', 'completed')
            ->sum('amount');

        // 7. المصروفات المدفوعة فعلياً
        $expenses = (float) $this->expenses()->whereIn('status', ['paid', 'completed'])->sum('amount');

        // 8. مدفوعات المرتبات والأجور
        $salaryPayments = (float) $this->salaryPayments()
            ->where('status', 'paid')
            ->sum('amount');

        // 9. التحويلات الصادرة
        $transfersOut = (float) $this->transfersFrom()
            ->where('status', 'completed')
            ->sum('amount');

        // 10. مبالغ الاستردادات المصروفة للطلاب من هذه الخزنة
        $refunds = (float) $this->refundedEnrollments()
            ->where('refund_status', 'refunded')
            ->sum('refunded_amount');

        return round($balance + $standaloneRevenues + $payments + $enrollmentUpfronts + $studentPayments + $transfersIn - $expenses - $salaryPayments - $transfersOut - $refunds, 2);
    }

    // Get current balance as attribute
    public function getCurrentBalanceAttribute()
    {
        return $this->getCurrentBalance();
    }

    public function getBalanceAttribute()
    {
        return $this->getCurrentBalance();
    }

    /**
     * Absolute negative balance prevention.
     * Ensures vault balance cannot become negative under any circumstance.
     * Respects view_vault_balances permission:
     * - If authorized: reveals current available vs requested.
     * - If unauthorized: outputs generic Arabic error without leaking balance figures.
     */
    public function assertSufficientBalance(float $amount, ?float $originalAmount = 0.0): void
    {
        $currentBalance = $this->getCurrentBalance();
        $available = round($currentBalance + (float) $originalAmount, 2);

        if (round($amount, 2) > $available) {
            $canView = auth()->check() && (auth()->user()->is_super_admin || auth()->user()->can('view_vault_balances'));

            if ($canView) {
                $msg = 'لا يوجد رصيد كافٍ في الخزنة المختارة لإتمام العملية. الرصيد المتاح: ' . number_format($available, 2) . ' ج.م والمطلوب: ' . number_format($amount, 2) . ' ج.م.';
            } else {
                $msg = 'لا يوجد رصيد كافٍ في الخزنة المختارة لإتمام العملية.';
            }

            throw \Illuminate\Validation\ValidationException::withMessages([
                'vault_id' => $msg,
                'amount' => $msg,
            ]);
        }
    }

    /**
     * Get display name for dropdowns.
     * Appends balance only if current user has view_vault_balances permission.
     */
    public function getDisplayNameWithBalance(): string
    {
        if (auth()->check() && (auth()->user()->is_super_admin || auth()->user()->can('view_vault_balances'))) {
            return "{$this->name} (الرصيد: " . number_format($this->getCurrentBalance(), 2) . " ج.م)";
        }

        return $this->name;
    }
}

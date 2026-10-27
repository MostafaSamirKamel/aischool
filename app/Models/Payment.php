<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'enrollment_id',
        'payment_code',
        'amount',
        'vault_id',
        'payment_method',
        'payment_date',
        'due_date',
        'status',
        'transaction_reference',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'completed',
        'payment_method' => 'cash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_code)) {
                $payment->payment_code = static::generatePaymentCode();
            }
        });

        static::saving(function ($payment) {
            if ($payment->status === 'completed') {
                if (empty($payment->vault_id)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'vault_id' => 'الخزنة المستلمة إلزامية لتسجيل أي دفعة مالية مكتملة.',
                    ]);
                }

                $vault = Vault::find($payment->vault_id);
                if (!$vault || !$vault->is_active) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'vault_id' => 'الخزنة المحددة غير صالحة أو غير نشطة.',
                    ]);
                }

                if (!empty($payment->tenant_id) && !empty($vault->tenant_id) && (int) $payment->tenant_id !== (int) $vault->tenant_id) {
                    throw new \Illuminate\Auth\Access\AuthorizationException('الخزنة المحددة لا تنتمي لنفس فرع الدفعة المالية.');
                }
            }
        });

        static::forceDeleting(function ($payment) {
            throw new \Exception('لا يمكن الحذف النهائي للعمليات المالية. العمليات المالية غير قابلة للحذف الصلب حفاظاً على سجل التدقيق والنزاهة المالية.');
        });
    }

    /**
     * Get the enrollment for this payment
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the user who received this payment
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the vault for this payment
     */
    public function vault()
    {
        return $this->belongsTo(Vault::class);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status === 'pending';
    }

    /**
     * Generate unique payment code
     */
    public static function generatePaymentCode(): string
    {
        $year = date('Y');
        $month = date('m');
        $prefix = 'PAY' . $year . $month;

        $lastPayment = static::withoutGlobalScopes()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->withTrashed()
            ->where('payment_code', 'like', $prefix . '%')
            ->orderBy('payment_code', 'desc')
            ->first();
        
        $number = 1;
        if ($lastPayment) {
            $number = intval(substr($lastPayment->payment_code, -5)) + 1;
        }

        do {
            $code = $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
            $exists = static::withoutGlobalScopes()
                ->withTrashed()
                ->where('payment_code', $code)
                ->exists();
            if ($exists) {
                $number++;
            }
        } while ($exists);
        
        return $code;
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnrollmentCancellation extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'enrollment_id',
        'student_id',
        'cancelled_by',
        'cancellation_reason',
        'cancellation_notes',
        'settlement_type',
        'settlement_status',
        'original_price',
        'discount_applied',
        'net_price',
        'total_paid',
        'outstanding_before',
        'retained_amount',
        'waived_amount',
        'remaining_debt',
        'refundable_amount',
        'refunded_amount',
        'refund_status',
        'refund_vault_id',
        'refund_payment_method',
        'refunded_at',
        'refunded_by',
        'financial_snapshot',
        'settlement_payload',
    ];

    protected $casts = [
        'original_price' => 'decimal:2',
        'discount_applied' => 'decimal:2',
        'net_price' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'outstanding_before' => 'decimal:2',
        'retained_amount' => 'decimal:2',
        'waived_amount' => 'decimal:2',
        'remaining_debt' => 'decimal:2',
        'refundable_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'refunded_at' => 'datetime',
        'financial_snapshot' => 'array',
        'settlement_payload' => 'array',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function refundVault(): BelongsTo
    {
        return $this->belongsTo(Vault::class, 'refund_vault_id');
    }

    public function getSettlementTypeLabelAttribute(): string
    {
        return match ($this->settlement_type) {
            'keep_debt' => 'إلغاء مع بقاء المديونية المستحقة',
            'waive_debt' => 'إلغاء وإسقاط المتبقي (إعفاء)',
            'session_based' => 'تسوية حسب الجلسات المحضورة',
            'full_refund' => 'استرداد كامل المبلغ المدفوع',
            'partial_refund' => 'استرداد جزئي',
            'custom' => 'تسوية مالية مخصصة',
            default => $this->settlement_type ?? 'غير محدد',
        };
    }

    public function getSettlementStatusLabelAttribute(): string
    {
        return match ($this->settlement_status) {
            'settled' => 'تمت التسوية',
            'pending_refund' => 'قيد انتظار الاسترداد',
            'completed' => 'مكتمل بالكامل',
            default => $this->settlement_status ?? 'قيد المعالجة',
        };
    }

    public function getRefundStatusLabelAttribute(): string
    {
        return match ($this->refund_status) {
            'not_applicable' => 'لا يتطلب استرداد',
            'pending' => 'قيد انتظار الصرف',
            'partially_refunded' => 'مسترد جزئياً',
            'completed' => 'تم استرداد المبلغ',
            default => $this->refund_status ?? 'غير محدد',
        };
    }
}

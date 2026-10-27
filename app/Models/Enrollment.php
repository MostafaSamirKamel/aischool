<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes, BelongsToBranch;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'group_id',
        'track_id',
        'enrollment_code',
        'enrollment_date',
        'total_amount',
        'paid_amount',
        'vault_id',
        'discount',
        'discount_reason',
        'payment_plan',
        'installments_count',
        'status',
        'start_date',
        'end_date',
        'notes',
        'cancellation_reason',
        'cancellation_notes',
        'cancelled_at',
        'cancelled_by',
        'settlement_type',
        'settlement_status',
        'waived_amount',
        'retained_amount',
        'refundable_amount',
        'refunded_amount',
        'refund_status',
        'refund_vault_id',
        'refund_payment_method',
        'refunded_at',
        'refunded_by',
        'settlement_details',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'waived_amount' => 'decimal:2',
        'retained_amount' => 'decimal:2',
        'refundable_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'installments_count' => 'integer',
        'settlement_details' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function booted()
    {
        static::saving(function ($enrollment) {
            $isNewRecord = !$enrollment->exists;

            // 1. Initial upfront payment check: only required when registering an upfront down payment
            if ($isNewRecord && (float) $enrollment->paid_amount > 0) {
                if (empty($enrollment->vault_id)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'vault_id' => 'الخزنة المستلمة إلزامية عند تسجيل دفعة مقدمة.',
                    ]);
                }
            }

            if (!empty($enrollment->vault_id)) {
                $vault = Vault::find($enrollment->vault_id);
                if (!$vault || !$vault->is_active) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'vault_id' => 'الخزنة المحددة غير صالحة أو غير نشطة.',
                    ]);
                }

                if (!empty($enrollment->tenant_id) && !empty($vault->tenant_id) && (int) $enrollment->tenant_id !== (int) $vault->tenant_id) {
                    throw new \Illuminate\Auth\Access\AuthorizationException('الخزنة المحددة لا تنتمي لنفس فرع التسجيل.');
                }
            }

            // 2. Refund check: mandatory reimbursing vault when refund is disbursed
            $isRefundDirty = $enrollment->isDirty('refunded_amount');
            if (($isNewRecord || $isRefundDirty) && (float) $enrollment->refunded_amount > 0) {
                if (empty($enrollment->refund_vault_id)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'refund_vault_id' => 'الخزنة المنصرف منها إلزامية لصرف مبالغ الاسترداد.',
                    ]);
                }
            }

            if (!empty($enrollment->refund_vault_id)) {
                $refundVault = Vault::find($enrollment->refund_vault_id);
                if (!$refundVault || !$refundVault->is_active) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'refund_vault_id' => 'خزنة الاسترداد المحددة غير صالحة أو غير نشطة.',
                    ]);
                }

                if (!empty($enrollment->tenant_id) && !empty($refundVault->tenant_id) && (int) $enrollment->tenant_id !== (int) $refundVault->tenant_id) {
                    throw new \Illuminate\Auth\Access\AuthorizationException('خزنة الاسترداد لا تنتمي لنفس فرع التسجيل.');
                }
            }
        });

        static::saved(function ($enrollment) {
            if (!empty($enrollment->group_id)) {
                Group::find($enrollment->group_id)?->syncCapacity();
            }
        });

        static::deleting(function ($enrollment) {
            // التحقق من وجود مدفوعات
            if ($enrollment->payments()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف التسجيل. يوجد :count مدفوعة مرتبطة بهذا التسجيل.',
                    ['count' => $enrollment->payments()->count()]
                ));
            }

            // التحقق من وجود أقساط
            if ($enrollment->installments()->exists()) {
                throw new \Exception(__(
                    'لا يمكن حذف التسجيل. يوجد :count قسط مرتبط بهذا التسجيل.',
                    ['count' => $enrollment->installments()->count()]
                ));
            }
        });
    }

    /**
     * Get the student for this enrollment
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the groups for this enrollment (many-to-many)
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'enrollment_group')
            ->withTimestamps()
            ->withPivot('enrolled_at');
    }

    /**
     * Get the primary group for this enrollment
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the track for this enrollment (optional)
     */
    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    /**
     * Get the vault for this enrollment
     */
    public function vault()
    {
        return $this->belongsTo(Vault::class);
    }

    /**
     * Get the courses for this enrollment (many-to-many)
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollment_courses')
            ->withoutGlobalScopes()
            ->withPivot('course_price', 'status', 'current_session', 'group_id', 'total_sessions')
            ->withTimestamps();
    }

    /**
     * Get the enrollment courses (with all details)
     */
    public function enrollmentCourses()
    {
        return $this->hasMany(EnrollmentCourse::class)->withoutGlobalScopes();
    }

    /**
     * Get group transfers for this enrollment
     */
    public function transfers()
    {
        return $this->hasMany(StudentGroupTransfer::class)->orderBy('transferred_at', 'desc');
    }

    /**
     * Calculate total amount from enrolled courses
     */
    public function calculateTotalFromCourses(): float
    {
        return $this->courses()->sum('enrollment_courses.course_price');
    }

    /**
     * Update total amount from courses
     */
    public function updateTotalFromCourses(): void
    {
        $this->attributes['total_amount'] = $this->calculateTotalFromCourses();
        $this->save();
    }

    /**
     * Get all payments for this enrollment
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get all installments for this enrollment
     */
    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    /**
     * Check if has overdue installments
     */
    public function hasOverdueInstallments(): bool
    {
        return $this->installments()->overdue()->exists();
    }

    /**
     * Get overdue installments
     */
    public function getOverdueInstallments()
    {
        return $this->installments()->overdue()->get();
    }

    /**
     * Get remaining balance
     * 
     * المعادلة الموحدة لحساب المتبقي:
     * الخطوة 1: إجمالي الكورسات (total_amount)
     * الخطوة 2: ناقص الخصم (discount)
     * الخطوة 3: ناقص المدفوع (paid_amount)
     * ————————————
     * النتيجة: المتبقي
     * 
     * ⚠️ ملاحظة مهمة: paid_amount يشمل:
     * - المقدم (عند التسجيل - يُحفظ مباشرة)
     * - المدفوع من الأقساط (يتم تحديثه من PaymentObserver عند كل دفعة)
     * 
     * 🔑 القاعدة:
     * - عند التسجيل: paid_amount = المقدم فقط
     * - عند كل دفعة: paid_amount += payment->amount
     * - الأقساط منفصلة: paid_amount في الأقساط لا يشمل المقدم
     * 
     * @return float المبلغ المتبقي
     */
    public function getRemainingBalance(): float
    {
        // إذا كان الاشتراك ملغياً وتم إسقاط المديونية بالكامل أو استرداد كامل/جزئي، المتبقي = 0
        if ($this->status === 'cancelled' && in_array($this->settlement_type, ['waive_debt', 'full_refund', 'partial_refund'])) {
            return 0.0;
        }

        if ($this->payment_plan === 'installments' && $this->installments()->exists()) {
            $remainingFromInstallments = (float) $this->installments()
                ->where('status', '!=', 'cancelled')
                ->selectRaw('COALESCE(SUM(CASE WHEN amount - paid_amount > 0 THEN amount - paid_amount ELSE 0 END), 0) as total_remaining')
                ->value('total_remaining');

            return max(0, $remainingFromInstallments);
        }

        $extraPayments = (float) $this->payments()->where('status', 'completed')->sum('amount');
        $rawRemaining = (float) $this->total_amount - (float) $this->paid_amount - $extraPayments - (float) $this->discount - (float) ($this->waived_amount ?? 0);
        return max(0, $rawRemaining);
    }

    /**
     * Get remaining balance attribute
     */
    public function getRemainingBalanceAttribute(): float
    {
        return $this->getRemainingBalance();
    }

    /**
     * Get remaining amount attribute (alias for remaining_balance)
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->getRemainingBalance();
    }

    /**
     * Check if fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->getRemainingBalance() <= 0;
    }

    /**
     * Get payment progress percentage
     */
    public function getPaymentProgress(): int
    {
        $netAmount = $this->getNetAmount();

        if ($netAmount <= 0) {
            return 100;
        }

        return (int) min(100, round(($this->getEffectivePaidAmount() / $netAmount) * 100));
    }

    /**
     * Net amount after discount.
     */
    public function getNetAmount(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->discount);
    }

    /**
     * Paid amount from installments only.
     */
    public function getInstallmentsPaidAmount(): float
    {
        if ($this->payment_plan !== 'installments' || !$this->installments()->exists()) {
            return 0.0;
        }

        return (float) $this->installments()->sum('paid_amount');
    }

    /**
     * Upfront amount inferred from net amount and total installment amount.
     */
    public function getCalculatedUpfrontAmount(): float
    {
        if ($this->payment_plan !== 'installments' || !$this->installments()->exists()) {
            return max(0, (float) $this->paid_amount);
        }

        $netAmount = $this->getNetAmount();
        $totalInstallmentsAmount = (float) $this->installments()->sum('amount');

        return max(0, $netAmount - $totalInstallmentsAmount);
    }

    /**
     * Effective paid amount for UI and calculations.
     */
    public function getEffectivePaidAmount(): float
    {
        if ($this->payment_plan === 'installments' && $this->installments()->exists()) {
            return min(
                $this->getNetAmount(),
                $this->getCalculatedUpfrontAmount() + $this->getInstallmentsPaidAmount()
            );
        }

        $extraPayments = (float) $this->payments()->where('status', 'completed')->sum('amount');
        return min($this->getNetAmount(), max(0, (float) $this->paid_amount + $extraPayments));
    }

    /**
     * Generate unique enrollment code
     */
    public static function generateEnrollmentCode(): string
    {
        $year = date('Y');
        $prefix = 'ENR' . $year;

        // Get the last enrollment code for this year across all branches
        $lastEnrollment = static::withoutGlobalScopes()
            ->where('enrollment_code', 'like', $prefix . '%')
            ->withTrashed()
            ->orderBy('enrollment_code', 'desc')
            ->first();

        if ($lastEnrollment) {
            // Extract the number part (last 4 digits)
            $lastNumber = intval(substr($lastEnrollment->enrollment_code, strlen($prefix)));
            $number = $lastNumber + 1;
        } else {
            $number = 1;
        }

        // Generate unique code with loop to ensure uniqueness
        do {
            $code = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
            $exists = static::withoutGlobalScopes()
                ->where('enrollment_code', $code)
                ->withTrashed()
                ->exists();
            if ($exists) {
                $number++;
            }
        } while ($exists);

        return $code;
    }

    /**
     * Check if enrollment is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Latest cancellation record
     */
    public function cancellation()
    {
        return $this->hasOne(EnrollmentCancellation::class)->latestOfMany();
    }

    /**
     * All cancellation records
     */
    public function cancellations()
    {
        return $this->hasMany(EnrollmentCancellation::class);
    }

    /**
     * User who cancelled the enrollment
     */
    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * User who executed the refund
     */
    public function refundedBy()
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    /**
     * Vault used for refund disbursement
     */
    public function refundVault()
    {
        return $this->belongsTo(Vault::class, 'refund_vault_id');
    }

    /**
     * Settlement type in Arabic
     */
    public function getSettlementTypeLabelAttribute(): ?string
    {
        return match ($this->settlement_type) {
            'keep_debt' => 'إلغاء مع بقاء المديونية المستحقة',
            'waive_debt' => 'إلغاء وإسقاط المتبقي (إعفاء)',
            'session_based' => 'تسوية حسب الجلسات المحضورة',
            'full_refund' => 'استرداد كامل المبلغ المدفوع',
            'partial_refund' => 'استرداد جزئي',
            'custom' => 'تسوية مالية مخصصة',
            default => $this->settlement_type,
        };
    }

    /**
     * Settlement status in Arabic
     */
    public function getSettlementStatusLabelAttribute(): ?string
    {
        return match ($this->settlement_status) {
            'settled' => 'تمت التسوية',
            'pending_refund' => 'قيد انتظار الاسترداد',
            'completed' => 'مكتمل بالكامل',
            default => $this->settlement_status,
        };
    }

    /**
     * Refund status in Arabic
     */
    public function getRefundStatusLabelAttribute(): ?string
    {
        return match ($this->refund_status) {
            'not_applicable' => 'لا يتطلب استرداد',
            'pending' => 'قيد انتظار الصرف',
            'partially_refunded' => 'مسترد جزئياً',
            'completed' => 'تم استرداد المبلغ',
            default => $this->refund_status,
        };
    }
}

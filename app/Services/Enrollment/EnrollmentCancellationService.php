<?php

namespace App\Services\Enrollment;

use App\Models\AttendanceRecord;
use App\Models\Enrollment;
use App\Models\EnrollmentCancellation;
use App\Models\EnrollmentCourse;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vault;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentCancellationService
{
    /**
     * Get real financial snapshot for an enrollment.
     */
    public function getFinancialSnapshot(Enrollment $enrollment): array
    {
        $netAmount = $enrollment->getNetAmount();
        $totalPaid = $enrollment->getEffectivePaidAmount();
        $outstanding = $enrollment->getRemainingBalance();
        $upfront = $enrollment->getCalculatedUpfrontAmount();
        $installmentsPaid = $enrollment->getInstallmentsPaidAmount();

        $installments = $enrollment->installments()->orderBy('installment_number')->get();
        $paidInstallments = $installments->filter(fn($i) => $i->isFullyPaid());
        $partialInstallments = $installments->filter(fn($i) => $i->isPartiallyPaid());
        $overdueInstallments = $installments->filter(fn($i) => $i->isOverdue());
        $pendingInstallments = $installments->filter(fn($i) => $i->status === 'pending' && !$i->isOverdue());

        // Courses and session data
        $enrollmentCourses = $enrollment->enrollmentCourses()->with('course', 'group')->get();
        $totalSessions = 0;
        $completedSessions = 0;

        foreach ($enrollmentCourses as $ec) {
            $totalSessions += $ec->total_sessions ?? ($ec->course?->total_sessions ?? 0);
            $completedSessions += $ec->current_session ?? 0;
        }

        // Also check attendance records for actual attended sessions
        $attendedRecordsCount = AttendanceRecord::where('student_id', $enrollment->student_id)
            ->whereIn('status', ['present', 'late'])
            ->whereHas('session', function ($q) use ($enrollment) {
                $groupIds = $enrollment->groups()->pluck('groups.id')->toArray();
                if (!empty($groupIds)) {
                    $q->whereIn('group_id', $groupIds);
                }
            })
            ->count();

        // Chargeable sessions is at least the recorded attendance or current_session
        $chargeableSessions = max($completedSessions, $attendedRecordsCount);

        return [
            'original_price' => (float) $enrollment->total_amount,
            'discount' => (float) $enrollment->discount,
            'net_price' => (float) $netAmount,
            'total_paid' => (float) $totalPaid,
            'upfront_amount' => (float) $upfront,
            'installments_paid' => (float) $installmentsPaid,
            'outstanding_balance' => (float) $outstanding,
            'payment_plan' => $enrollment->payment_plan,
            'installments_total_count' => $installments->count(),
            'paid_installments_count' => $paidInstallments->count(),
            'paid_installments_amount' => (float) $paidInstallments->sum('paid_amount'),
            'partial_installments_count' => $partialInstallments->count(),
            'partial_installments_amount' => (float) $partialInstallments->sum('paid_amount'),
            'overdue_installments_count' => $overdueInstallments->count(),
            'overdue_installments_amount' => (float) $overdueInstallments->sum(fn($i) => $i->getRemainingAmount()),
            'pending_installments_count' => $pendingInstallments->count(),
            'pending_installments_amount' => (float) $pendingInstallments->sum(fn($i) => $i->getRemainingAmount()),
            'total_sessions' => $totalSessions,
            'completed_sessions' => $completedSessions,
            'attended_sessions_count' => $attendedRecordsCount,
            'chargeable_sessions' => $chargeableSessions,
            'courses_details' => $enrollmentCourses->map(fn($ec) => [
                'course_id' => $ec->course_id,
                'course_name' => $ec->course?->name ?? 'كورس',
                'group_name' => $ec->group?->name ?? 'عامة',
                'course_price' => (float) $ec->course_price,
                'total_sessions' => $ec->total_sessions,
                'current_session' => $ec->current_session,
                'status' => $ec->status,
            ])->toArray(),
        ];
    }

    /**
     * Preview settlement calculations.
     */
    public function previewSettlement(Enrollment $enrollment, string $settlementType, array $params = []): array
    {
        $snapshot = $this->getFinancialSnapshot($enrollment);
        $totalPaid = $snapshot['total_paid'];
        $outstanding = $snapshot['outstanding_balance'];
        $netPrice = $snapshot['net_price'];
        $totalSessions = $snapshot['total_sessions'];

        $retainedAmount = 0.0;
        $waivedAmount = 0.0;
        $remainingDebt = 0.0;
        $refundableAmount = 0.0;
        $refundStatus = 'not_applicable';
        $summaryText = '';
        $details = [];

        switch ($settlementType) {
            case 'keep_debt':
                // Option A: Keep outstanding debt
                $retainedAmount = $totalPaid;
                $waivedAmount = 0.0;
                $remainingDebt = $outstanding;
                $refundableAmount = 0.0;
                $refundStatus = 'not_applicable';
                $summaryText = 'إلغاء الاشتراك مع بقاء المديونية المستحقة (' . number_format($remainingDebt, 2) . ' ج.م) كالتزام مالي قائم.';
                break;

            case 'waive_debt':
                // Option B: Waive remaining debt
                $retainedAmount = $totalPaid;
                $waivedAmount = $outstanding;
                $remainingDebt = 0.0;
                $refundableAmount = 0.0;
                $refundStatus = 'not_applicable';
                $summaryText = 'إلغاء الاشتراك مع إعفاء الطالب وإسقاط كامل المتبقي (' . number_format($waivedAmount, 2) . ' ج.م). لا يوجد مديونية متبقية.';
                break;

            case 'session_based':
                // Option C: Settlement based on attended sessions
                $chargeableSessions = isset($params['chargeable_sessions']) && is_numeric($params['chargeable_sessions'])
                    ? (int) $params['chargeable_sessions']
                    : $snapshot['chargeable_sessions'];

                $chargeableSessions = max(0, min($chargeableSessions, $totalSessions > 0 ? $totalSessions : 999));
                $sessionPrice = ($totalSessions > 0) ? round($netPrice / $totalSessions, 2) : 0;
                $earnedAmount = round($sessionPrice * $chargeableSessions, 2);

                $details = [
                    'total_sessions' => $totalSessions,
                    'chargeable_sessions' => $chargeableSessions,
                    'session_price' => $sessionPrice,
                    'earned_amount' => $earnedAmount,
                ];

                if ($totalPaid > $earnedAmount) {
                    // School earned less than what was paid -> Refund difference to student
                    $retainedAmount = $earnedAmount;
                    $refundableAmount = round($totalPaid - $earnedAmount, 2);
                    $waivedAmount = $outstanding;
                    $remainingDebt = 0.0;
                    $refundStatus = $refundableAmount > 0 ? 'pending' : 'not_applicable';
                    $summaryText = "حساب مستحقات المدرسة عن ({$chargeableSessions}) جلسة بقيمة (" . number_format($earnedAmount, 2) . " ج.م). المدفوع (" . number_format($totalPaid, 2) . " ج.م) يزيد عن المستحق، يستحق الطالب استرداد (" . number_format($refundableAmount, 2) . " ج.م).";
                } else {
                    // School earned more than what was paid -> Student owes difference
                    $retainedAmount = $totalPaid;
                    $refundableAmount = 0.0;
                    $neededFromDebt = round($earnedAmount - $totalPaid, 2);
                    $remainingDebt = min($outstanding, $neededFromDebt);
                    $waivedAmount = max(0, round($outstanding - $remainingDebt, 2));
                    $refundStatus = 'not_applicable';
                    $summaryText = "حساب مستحقات المدرسة عن ({$chargeableSessions}) جلسة بقيمة (" . number_format($earnedAmount, 2) . " ج.م). المدفوع (" . number_format($totalPaid, 2) . " ج.م) يقل عن المستحق، يتبقى على الطالب (" . number_format($remainingDebt, 2) . " ج.م) وإسقاط (" . number_format($waivedAmount, 2) . " ج.م).";
                }
                break;

            case 'full_refund':
                // Option D: Full refund
                $retainedAmount = 0.0;
                $waivedAmount = $outstanding;
                $remainingDebt = 0.0;
                $refundableAmount = $totalPaid;
                $refundStatus = $refundableAmount > 0 ? 'pending' : 'not_applicable';
                $summaryText = 'إلغاء الاشتراك مع تسجيل التزام استرداد كامل المبلغ المدفوع (' . number_format($refundableAmount, 2) . ' ج.م) وإسقاط المديونية.';
                break;

            case 'partial_refund':
                // Option E: Partial refund
                $requestedRefund = isset($params['refund_amount']) ? (float) $params['refund_amount'] : 0.0;
                $requestedRefund = max(0, min($requestedRefund, $totalPaid));

                $refundableAmount = $requestedRefund;
                $retainedAmount = round($totalPaid - $refundableAmount, 2);
                $waivedAmount = $outstanding;
                $remainingDebt = 0.0;
                $refundStatus = $refundableAmount > 0 ? 'pending' : 'not_applicable';
                $summaryText = 'إلغاء الاشتراك مع استرداد جزئي بقيمة (' . number_format($refundableAmount, 2) . ' ج.م) واحتفاظ المدرسة بـ (' . number_format($retainedAmount, 2) . ' ج.م) وإسقاط المتبقي.';
                break;

            case 'custom':
                // Option F: Custom settlement
                $retainedAmount = isset($params['retained_amount']) ? (float) $params['retained_amount'] : 0.0;
                $refundableAmount = isset($params['refundable_amount']) ? (float) $params['refundable_amount'] : 0.0;
                $waivedAmount = isset($params['waived_amount']) ? (float) $params['waived_amount'] : 0.0;
                $remainingDebt = isset($params['remaining_debt']) ? (float) $params['remaining_debt'] : 0.0;

                // Validate mathematical integrity
                if (round($retainedAmount + $refundableAmount, 2) !== round($totalPaid, 2)) {
                    // auto-adjust retained amount to match total paid
                    $retainedAmount = max(0, round($totalPaid - $refundableAmount, 2));
                }

                if (round($waivedAmount + $remainingDebt, 2) !== round($outstanding, 2)) {
                    $waivedAmount = max(0, round($outstanding - $remainingDebt, 2));
                }

                $refundStatus = $refundableAmount > 0 ? 'pending' : 'not_applicable';
                $summaryText = 'تسوية مالية مخصصة: محتجز (' . number_format($retainedAmount, 2) . ' ج.م)، مسترد (' . number_format($refundableAmount, 2) . ' ج.م)، ساقط (' . number_format($waivedAmount, 2) . ' ج.م)، مديونية قائمة (' . number_format($remainingDebt, 2) . ' ج.م).';
                break;

            default:
                throw new \InvalidArgumentException('نوع التسوية المحدد غير صالح: ' . $settlementType);
        }

        return [
            'settlement_type' => $settlementType,
            'financial_snapshot' => $snapshot,
            'retained_amount' => round($retainedAmount, 2),
            'waived_amount' => round($waivedAmount, 2),
            'remaining_debt' => round($remainingDebt, 2),
            'refundable_amount' => round($refundableAmount, 2),
            'refund_status' => $refundStatus,
            'summary_text' => $summaryText,
            'details' => $details,
        ];
    }

    /**
     * Simplified student enrollment cancellation workflow.
     * Automatically waives remaining debt by school policy.
     * Optionally executes immediate refund from a vault if authorized.
     */
    public function cancelSimplified(Enrollment $enrollment, User $user, array $data): EnrollmentCancellation
    {
        return DB::transaction(function () use ($enrollment, $user, $data) {
            /** @var Enrollment $lockedEnrollment */
            $lockedEnrollment = Enrollment::query()
                ->where('id', $enrollment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedEnrollment->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'enrollment' => 'هذا الاشتراك ملغي بالفعل ولا يمكن إلغاؤه مرة أخرى.',
                ]);
            }

            // Tenant scope validation
            if (!empty($user->tenant_id) && !empty($lockedEnrollment->tenant_id) && (int) $user->tenant_id !== (int) $lockedEnrollment->tenant_id) {
                throw new AuthorizationException('لا يمكنك إلغاء اشتراك ينتمي لفرع آخر.');
            }

            // Verify cancellation permission
            if (!app(\App\Policies\EnrollmentPolicy::class)->cancel($user, $lockedEnrollment)) {
                throw new AuthorizationException('ليس لديك صلاحية إلغاء هذا الاشتراك.');
            }

            // Validate reason
            $reason = trim((string) ($data['reason'] ?? $data['cancellation_reason'] ?? ''));
            if ($reason === '') {
                throw ValidationException::withMessages([
                    'cancellation_reason' => 'سبب إلغاء التسجيل إجباري ولا يمكن تركه فارغاً.',
                ]);
            }

            // Recalculate financial snapshot
            $snapshot = $this->getFinancialSnapshot($lockedEnrollment);
            $originalPrice = (float) $snapshot['original_price'];
            $discount = (float) $snapshot['discount'];
            $netPrice = (float) $snapshot['net_price'];
            $totalPaid = (float) $snapshot['total_paid'];
            $outstandingBefore = (float) $snapshot['outstanding_balance'];

            // Eligible refund calculation
            $alreadyRefunded = (float) ($lockedEnrollment->refunded_amount ?? 0);
            $maxEligibleRefund = max(0, round($totalPaid - $alreadyRefunded, 2));

            // Parse refund amount
            $refundInput = $data['refund_amount'] ?? null;
            $hasRefund = ($refundInput !== null && $refundInput !== '' && (float) $refundInput > 0);
            $refundAmount = $hasRefund ? round((float) $refundInput, 2) : 0.0;

            if ($refundAmount < 0) {
                throw ValidationException::withMessages([
                    'refund_amount' => 'مبلغ الاسترداد لا يمكن أن يكون سالباً.',
                ]);
            }

            if ($refundAmount > $maxEligibleRefund) {
                throw ValidationException::withMessages([
                    'refund_amount' => 'مبلغ الاسترداد (' . number_format($refundAmount, 2) . ' ج.م) يتجاوز المبلغ القابل للاسترداد من المدفوعات السابقة (' . number_format($maxEligibleRefund, 2) . ' ج.م).',
                ]);
            }

            // If refund requested, validate refund permission & vault
            $vault = null;
            if ($refundAmount > 0) {
                if (!app(\App\Policies\EnrollmentPolicy::class)->executeRefund($user, $lockedEnrollment)) {
                    throw new AuthorizationException('ليس لديك صلاحية صرف واسترداد المبالغ المالية من الخزنة.');
                }

                $vaultId = (int) ($data['vault_id'] ?? $data['refund_vault_id'] ?? 0);
                if ($vaultId <= 0) {
                    throw ValidationException::withMessages([
                        'vault_id' => 'يجب اختيار الخزنة المنصرف منها المبلغ المسترد.',
                    ]);
                }

                /** @var Vault|null $vault */
                $vault = Vault::query()->where('id', $vaultId)->where('is_active', true)->first();
                if (!$vault) {
                    throw ValidationException::withMessages([
                        'vault_id' => 'الخزنة المحددة غير موجودة أو غير نشطة.',
                    ]);
                }

                if (!empty($user->tenant_id) && (int) $vault->tenant_id !== (int) $user->tenant_id) {
                    throw new AuthorizationException('الخزنة المحددة لا تنتمي لنفس فرع المستخدم.');
                }

                $vault->assertSufficientBalance($refundAmount);
            }

            // Automatic debt waiver by school policy
            $waivedAmount = $outstandingBefore;
            $remainingDebt = 0.0;
            $retainedAmount = max(0, round($totalPaid - $refundAmount, 2));

            // Update installments
            $unpaidInstallments = $lockedEnrollment->installments()
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) {
                    $q->where('paid_amount', 0)
                      ->orWhereColumn('paid_amount', '<', 'amount');
                })
                ->get();

            foreach ($unpaidInstallments as $inst) {
                if ((float) $inst->paid_amount <= 0) {
                    $inst->status = 'cancelled';
                    $inst->notes = trim(($inst->notes ? $inst->notes . "\n" : '') . "تم إسقاط القسط بموجب إلغاء التسجيل بتاريخ " . now()->format('Y-m-d'));
                    $inst->save();
                } else {
                    $originalAmount = $inst->amount;
                    $inst->amount = $inst->paid_amount;
                    $inst->status = 'paid';
                    $inst->notes = trim(($inst->notes ? $inst->notes . "\n" : '') . "تم تعديل قيمة القسط من {$originalAmount} إلى {$inst->paid_amount} بموجب إسقاط متبقي التسجيل بتاريخ " . now()->format('Y-m-d'));
                    $inst->save();
                }
            }

            // Remove from active group membership
            $lockedEnrollment->enrollmentCourses()
                ->where('status', 'active')
                ->update(['status' => 'inactive']);

            // Execute refund if requested
            $rawMethod = $data['payment_method'] ?? $data['refund_payment_method'] ?? 'cash';
            $paymentMethod = in_array($rawMethod, ['cash', 'card', 'bank_transfer', 'mobile_wallet', 'other']) ? $rawMethod : 'cash';
            if ($refundAmount > 0 && $vault) {
                $expenseCategory = ExpenseCategory::firstOrCreate(
                    [
                        'tenant_id' => $lockedEnrollment->tenant_id,
                        'name' => 'استرداد رسوم اشتراك',
                    ],
                    [
                        'code' => 'EXP-REFUND-T' . $lockedEnrollment->tenant_id,
                        'description' => 'المبالغ المستردة للطلاب عند إلغاء الاشتراكات',
                        'is_active' => true,
                    ]
                );

                $studentName = $lockedEnrollment->student?->user?->name ?? 'طالب';

                $expense = Expense::create([
                    'tenant_id' => $lockedEnrollment->tenant_id,
                    'expense_code' => Expense::generateExpenseCode(),
                    'expense_category_id' => $expenseCategory->id,
                    'vault_id' => $vault->id,
                    'title' => 'استرداد رسوم اشتراك - ' . $studentName,
                    'description' => 'استرداد لاشتراك رقم ' . $lockedEnrollment->enrollment_code . ' - ' . $reason,
                    'amount' => $refundAmount,
                    'expense_date' => now()->toDateString(),
                    'payment_method' => $paymentMethod,
                    'status' => 'paid',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'created_by' => $user->id,
                ]);

                $refundPayment = Payment::create([
                    'tenant_id' => $lockedEnrollment->tenant_id,
                    'enrollment_id' => $lockedEnrollment->id,
                    'payment_code' => Payment::generatePaymentCode(),
                    'amount' => $refundAmount,
                    'vault_id' => $vault->id,
                    'payment_method' => $paymentMethod,
                    'payment_date' => now()->toDateString(),
                    'status' => 'refunded',
                    'transaction_reference' => 'REFUND-' . $lockedEnrollment->enrollment_code . '-' . time(),
                    'notes' => 'صرف استرداد للإلغاء بموجب سند الصرف ' . $expense->expense_code,
                    'received_by' => $user->id,
                ]);
            }

            $refundStatus = ($refundAmount > 0) ? 'completed' : 'not_applicable';
            $settlementStatus = ($refundAmount > 0) ? 'completed' : 'settled';
            $settlementType = ($refundAmount > 0 ? ($refundAmount >= $totalPaid ? 'full_refund' : 'partial_refund') : 'waive_debt');

            // Update enrollment record
            $lockedEnrollment->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancellation_notes' => $data['notes'] ?? null,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'settlement_type' => $settlementType,
                'settlement_status' => $settlementStatus,
                'retained_amount' => $retainedAmount,
                'waived_amount' => $waivedAmount,
                'refundable_amount' => $refundAmount,
                'refunded_amount' => $refundAmount,
                'refund_status' => $refundStatus,
                'refund_vault_id' => $hasRefund && $vault ? $vault->id : null,
                'refund_payment_method' => $hasRefund ? $paymentMethod : null,
                'refunded_at' => $hasRefund ? now() : null,
                'refunded_by' => $hasRefund ? $user->id : null,
                'settlement_details' => [
                    'mode' => 'simplified',
                    'reason' => $reason,
                    'refund_amount' => $refundAmount,
                    'waived_amount' => $waivedAmount,
                    'retained_amount' => $retainedAmount,
                    'vault_id' => $hasRefund && $vault ? $vault->id : null,
                    'applied_at' => now()->toIso8601String(),
                    'applied_by' => $user->id,
                    'applied_by_name' => $user->name,
                ],
            ]);

            // Create cancellation record
            $cancellation = EnrollmentCancellation::create([
                'tenant_id' => $lockedEnrollment->tenant_id,
                'enrollment_id' => $lockedEnrollment->id,
                'student_id' => $lockedEnrollment->student_id,
                'cancelled_by' => $user->id,
                'cancellation_reason' => $reason,
                'cancellation_notes' => $data['notes'] ?? null,
                'settlement_type' => $settlementType,
                'settlement_status' => $settlementStatus,
                'original_price' => $originalPrice,
                'discount_applied' => $discount,
                'net_price' => $netPrice,
                'total_paid' => $totalPaid,
                'outstanding_before' => $outstandingBefore,
                'retained_amount' => $retainedAmount,
                'waived_amount' => $waivedAmount,
                'remaining_debt' => 0.0,
                'refundable_amount' => $refundAmount,
                'refunded_amount' => $refundAmount,
                'refund_status' => $refundStatus,
                'refund_vault_id' => $hasRefund && $vault ? $vault->id : null,
                'refund_payment_method' => $hasRefund ? $paymentMethod : null,
                'refunded_at' => $hasRefund ? now() : null,
                'refunded_by' => $hasRefund ? $user->id : null,
                'financial_snapshot' => $snapshot,
                'settlement_payload' => $data,
            ]);

            // Audit log
            AuditLogService::log(
                event: 'subscription_cancelled',
                subject: $lockedEnrollment,
                description: "إلغاء اشتراك الطالب {$lockedEnrollment->student?->user?->name} في التسجيل {$lockedEnrollment->enrollment_code} وإسقاط المديونية المتبقية تلقائياً",
                properties: [
                    'cancellation_id' => $cancellation->id,
                    'retained_amount' => $retainedAmount,
                    'waived_amount' => $waivedAmount,
                    'refund_amount' => $refundAmount,
                    'reason' => $reason,
                ]
            );

            if ($waivedAmount > 0) {
                AuditLogService::log(
                    event: 'debt_waived',
                    subject: $lockedEnrollment,
                    description: "إسقاط مديونية بقيمة {$waivedAmount} ج.م للطالب {$lockedEnrollment->student?->user?->name} في التسجيل {$lockedEnrollment->enrollment_code}",
                    properties: [
                        'waived_amount' => $waivedAmount,
                        'reason' => $reason,
                    ]
                );
            }

            if ($refundAmount > 0 && $vault) {
                AuditLogService::log(
                    event: 'refund_executed',
                    subject: $lockedEnrollment,
                    description: "صرف استرداد فوري بقيمة {$refundAmount} ج.م للطالب {$lockedEnrollment->student?->user?->name} من الخزنة {$vault->name}",
                    properties: [
                        'amount' => $refundAmount,
                        'vault_id' => $vault->id,
                        'payment_method' => $paymentMethod,
                    ]
                );
            }

            return $cancellation;
        });
    }

    /**
     * Cancel subscription with financial settlement.
     */
    public function cancelSubscription(Enrollment $enrollment, User $user, array $data): EnrollmentCancellation
    {
        if (isset($data['simplified']) && $data['simplified']) {
            return $this->cancelSimplified($enrollment, $user, $data);
        }

        return DB::transaction(function () use ($enrollment, $user, $data) {
            // Lock enrollment row to prevent concurrent duplicate cancellation
            /** @var Enrollment $lockedEnrollment */
            $lockedEnrollment = Enrollment::query()
                ->where('id', $enrollment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedEnrollment->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'enrollment' => 'هذا الاشتراك ملغي بالفعل ولا يمكن إلغاؤه مرة أخرى.',
                ]);
            }

            // Tenant scope validation
            if (!empty($user->tenant_id) && !empty($lockedEnrollment->tenant_id) && (int) $user->tenant_id !== (int) $lockedEnrollment->tenant_id) {
                throw new AuthorizationException('لا يمكنك إلغاء اشتراك ينتمي لفرع آخر.');
            }

            // Verify cancellation permission
            if (!app(\App\Policies\EnrollmentPolicy::class)->cancel($user, $lockedEnrollment)) {
                throw new AuthorizationException('ليس لديك صلاحية إلغاء هذا الاشتراك.');
            }

            $settlementType = $data['settlement_type'] ?? 'keep_debt';

            // Verify settlement permission if settlement involves adjustments (waiving debt, refunds, custom)
            if ($settlementType !== 'keep_debt') {
                if (!app(\App\Policies\EnrollmentPolicy::class)->settle($user, $lockedEnrollment)) {
                    throw new AuthorizationException('ليس لديك صلاحية إجراء تسوية مالية أو إسقاط مديونية. يمكنك فقط اختيار "إلغاء مع بقاء المديونية".');
                }
            }

            $reason = trim($data['reason'] ?? '');
            if (empty($reason)) {
                throw ValidationException::withMessages([
                    'reason' => 'سبب إلغاء الاشتراك إجباري.',
                ]);
            }

            // Calculate settlement numbers
            $preview = $this->previewSettlement($lockedEnrollment, $settlementType, $data);
            $retainedAmount = $preview['retained_amount'];
            $waivedAmount = $preview['waived_amount'];
            $remainingDebt = $preview['remaining_debt'];
            $refundableAmount = $preview['refundable_amount'];
            $refundStatus = $preview['refund_status'];
            $snapshot = $preview['financial_snapshot'];

            // 1. Update unpaid installments according to settlement
            if ($waivedAmount > 0 || in_array($settlementType, ['waive_debt', 'full_refund', 'partial_refund'])) {
                // If entire debt is waived or full refund
                if ($remainingDebt <= 0) {
                    $unpaidInstallments = $lockedEnrollment->installments()
                        ->where('status', '!=', 'cancelled')
                        ->where(function ($q) {
                            $q->where('paid_amount', 0)
                              ->orWhereColumn('paid_amount', '<', 'amount');
                        })
                        ->get();

                    foreach ($unpaidInstallments as $inst) {
                        if ($inst->paid_amount <= 0) {
                            $inst->status = 'cancelled';
                            $inst->notes = ($inst->notes ? $inst->notes . "\n" : '') . "تم إسقاط القسط بالكامل بموجب تسوية الإلغاء (" . now()->format('Y-m-d') . ")";
                            $inst->save();
                        } else {
                            // Partially paid: keep paid portion, waive the rest
                            $originalAmount = $inst->amount;
                            $inst->amount = $inst->paid_amount;
                            $inst->status = 'paid';
                            $inst->notes = ($inst->notes ? $inst->notes . "\n" : '') . "تم تعديل قيمة القسط من {$originalAmount} إلى {$inst->paid_amount} بموجب تسوية الإلغاء وإسقاط المتبقي (" . now()->format('Y-m-d') . ")";
                            $inst->save();
                        }
                    }
                } else {
                    // Some remaining debt is kept, some is waived: adjust installments
                    $debtToKeep = $remainingDebt;
                    $unpaidInstallments = $lockedEnrollment->installments()
                        ->where('status', '!=', 'cancelled')
                        ->where(function ($q) {
                            $q->where('paid_amount', 0)
                              ->orWhereColumn('paid_amount', '<', 'amount');
                        })
                        ->orderBy('due_date')
                        ->get();

                    foreach ($unpaidInstallments as $inst) {
                        $remainingOnInst = (float) $inst->amount - (float) $inst->paid_amount;
                        if ($debtToKeep >= $remainingOnInst) {
                            // Keep full installment
                            $debtToKeep -= $remainingOnInst;
                        } elseif ($debtToKeep > 0) {
                            // Partially keep, waive remainder
                            $newAmount = (float) $inst->paid_amount + $debtToKeep;
                            $inst->amount = $newAmount;
                            $inst->notes = ($inst->notes ? $inst->notes . "\n" : '') . "تم تعديل قيمة القسط للاحتفاظ بمديونية قدرها {$debtToKeep} بموجب تسوية الإلغاء";
                            $inst->save();
                            $debtToKeep = 0;
                        } else {
                            // Fully waive this installment
                            if ($inst->paid_amount <= 0) {
                                $inst->status = 'cancelled';
                                $inst->notes = ($inst->notes ? $inst->notes . "\n" : '') . "تم إسقاط القسط بموجب تسوية الإلغاء";
                                $inst->save();
                            } else {
                                $inst->amount = $inst->paid_amount;
                                $inst->status = 'paid';
                                $inst->save();
                            }
                        }
                    }
                }
            }

            // 2. Update EnrollmentCourse records (set active to inactive)
            $lockedEnrollment->enrollmentCourses()
                ->where('status', 'active')
                ->update(['status' => 'inactive']);

            // 3. Update Enrollment model
            $settlementStatus = $refundableAmount > 0 ? 'pending_refund' : 'settled';

            $lockedEnrollment->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancellation_notes' => $data['notes'] ?? null,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'settlement_type' => $settlementType,
                'settlement_status' => $settlementStatus,
                'retained_amount' => $retainedAmount,
                'waived_amount' => $waivedAmount,
                'refundable_amount' => $refundableAmount,
                'refunded_amount' => 0.0,
                'refund_status' => $refundStatus,
                'settlement_details' => [
                    'preview' => $preview,
                    'applied_at' => now()->toIso8601String(),
                    'applied_by' => $user->id,
                    'applied_by_name' => $user->name,
                ],
            ]);

            // 4. Create EnrollmentCancellation audit record
            $cancellation = EnrollmentCancellation::create([
                'tenant_id' => $lockedEnrollment->tenant_id,
                'enrollment_id' => $lockedEnrollment->id,
                'student_id' => $lockedEnrollment->student_id,
                'cancelled_by' => $user->id,
                'cancellation_reason' => $reason,
                'cancellation_notes' => $data['notes'] ?? null,
                'settlement_type' => $settlementType,
                'settlement_status' => $settlementStatus,
                'original_price' => $snapshot['original_price'],
                'discount_applied' => $snapshot['discount'],
                'net_price' => $snapshot['net_price'],
                'total_paid' => $snapshot['total_paid'],
                'outstanding_before' => $snapshot['outstanding_balance'],
                'retained_amount' => $retainedAmount,
                'waived_amount' => $waivedAmount,
                'remaining_debt' => $remainingDebt,
                'refundable_amount' => $refundableAmount,
                'refunded_amount' => 0.0,
                'refund_status' => $refundStatus,
                'financial_snapshot' => $snapshot,
                'settlement_payload' => $data,
            ]);

            // 5. Audit Log Entry
            AuditLogService::log(
                event: 'subscription_cancelled',
                subject: $lockedEnrollment,
                description: "إلغاء اشتراك الطالب {$lockedEnrollment->student?->user?->name} في التسجيل {$lockedEnrollment->enrollment_code} بنوع تسوية: {$lockedEnrollment->settlement_type_label}",
                properties: [
                    'cancellation_id' => $cancellation->id,
                    'settlement_type' => $settlementType,
                    'retained_amount' => $retainedAmount,
                    'waived_amount' => $waivedAmount,
                    'remaining_debt' => $remainingDebt,
                    'refundable_amount' => $refundableAmount,
                    'reason' => $reason,
                ]
            );

            if ($waivedAmount > 0) {
                AuditLogService::log(
                    event: 'debt_waived',
                    subject: $lockedEnrollment,
                    description: "إسقاط مديونية بقيمة {$waivedAmount} ج.م للطالب {$lockedEnrollment->student?->user?->name} في التسجيل {$lockedEnrollment->enrollment_code}",
                    properties: [
                        'waived_amount' => $waivedAmount,
                        'reason' => $reason,
                    ]
                );
            }

            // 6. Optional immediate refund execution if requested and user is authorized
            if (!empty($data['execute_refund_now']) && $refundableAmount > 0) {
                if (app(\App\Policies\EnrollmentPolicy::class)->executeRefund($user, $lockedEnrollment)) {
                    $vaultId = (int) ($data['refund_vault_id'] ?? 0);
                    $paymentMethod = $data['refund_payment_method'] ?? 'cash';
                    $this->executeRefund($lockedEnrollment, $user, $refundableAmount, $vaultId, $paymentMethod, 'صرف فوري عند إلغاء الاشتراك');
                }
            }

            return $cancellation;
        });
    }

    /**
     * Execute authorized refund disbursement from a vault.
     */
    public function executeRefund(
        Enrollment $enrollment,
        User $user,
        float $amount,
        int $vaultId,
        string $paymentMethod = 'cash',
        ?string $notes = null
    ): array {
        return DB::transaction(function () use ($enrollment, $user, $amount, $vaultId, $paymentMethod, $notes) {
            $paymentMethod = in_array($paymentMethod, ['cash', 'card', 'bank_transfer', 'mobile_wallet', 'other']) ? $paymentMethod : 'cash';
            /** @var Enrollment $lockedEnrollment */
            $lockedEnrollment = Enrollment::query()
                ->where('id', $enrollment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedEnrollment->status !== 'cancelled') {
                throw new \Exception('لا يمكن صرف استرداد لاشتراك غير ملغي.');
            }

            // Verify Refund Execution Permission
            if (!app(\App\Policies\EnrollmentPolicy::class)->executeRefund($user, $lockedEnrollment)) {
                throw new AuthorizationException('ليس لديك صلاحية تنفيذ وصرف الاسترداد المالي.');
            }

            // Tenant scope validation
            if (!empty($user->tenant_id) && !empty($lockedEnrollment->tenant_id) && (int) $user->tenant_id !== (int) $lockedEnrollment->tenant_id) {
                throw new AuthorizationException('لا يمكنك صرف استرداد من فرع مختلف.');
            }

            $pendingRefundable = round((float) $lockedEnrollment->refundable_amount - (float) $lockedEnrollment->refunded_amount, 2);

            if ($amount <= 0 || round($amount, 2) > $pendingRefundable) {
                throw ValidationException::withMessages([
                    'amount' => 'مبلغ الاسترداد (' . number_format($amount, 2) . ' ج.م) غير صحيح أو يتجاوز المبلغ المتبقي القابل للاسترداد (' . number_format($pendingRefundable, 2) . ' ج.م).',
                ]);
            }

            // Vault verification
            /** @var Vault $vault */
            $vault = Vault::query()
                ->where('id', $vaultId)
                ->where('is_active', true)
                ->first();

            if (!$vault) {
                throw ValidationException::withMessages([
                    'vault_id' => 'الخزنة المحددة غير موجودة أو غير نشطة.',
                ]);
            }

            if (!empty($user->tenant_id) && (int) $vault->tenant_id !== (int) $user->tenant_id) {
                throw new AuthorizationException('الخزنة المحددة لا تنتمي لنفس فرع المستخدم.');
            }

            // Concurrency & balance check
            $vault->assertSufficientBalance($amount);

            // 1. Get or create ExpenseCategory for Student Refunds
            $expenseCategory = ExpenseCategory::firstOrCreate(
                [
                    'tenant_id' => $lockedEnrollment->tenant_id,
                    'name' => 'استرداد رسوم اشتراك',
                ],
                [
                    'code' => 'EXP-REFUND-T' . $lockedEnrollment->tenant_id,
                    'description' => 'المبالغ المستردة للطلاب عند إلغاء الاشتراكات',
                    'is_active' => true,
                ]
            );

            $studentName = $lockedEnrollment->student?->user?->name ?? 'طالب';

            // 2. Create Expense to record cash deduction in vault
            $expense = Expense::create([
                'tenant_id' => $lockedEnrollment->tenant_id,
                'expense_code' => Expense::generateExpenseCode(),
                'expense_category_id' => $expenseCategory->id,
                'vault_id' => $vault->id,
                'title' => 'استرداد رسوم اشتراك - ' . $studentName,
                'description' => 'استرداد لاشتراك رقم ' . $lockedEnrollment->enrollment_code . ($notes ? ' - ' . $notes : ''),
                'amount' => $amount,
                'expense_date' => now()->toDateString(),
                'payment_method' => $paymentMethod,
                'status' => 'paid',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'created_by' => $user->id,
            ]);

            // 3. Create Payment record with status 'refunded' for reporting trace
            $refundPayment = Payment::create([
                'tenant_id' => $lockedEnrollment->tenant_id,
                'enrollment_id' => $lockedEnrollment->id,
                'payment_code' => Payment::generatePaymentCode(),
                'amount' => $amount,
                'vault_id' => $vault->id,
                'payment_method' => $paymentMethod,
                'payment_date' => now()->toDateString(),
                'status' => 'refunded',
                'transaction_reference' => 'REFUND-' . $lockedEnrollment->enrollment_code . '-' . time(),
                'notes' => 'صرف استرداد للإلغاء بموجب سند الصرف ' . $expense->expense_code,
                'received_by' => $user->id,
            ]);

            // 4. Update Enrollment record
            $newRefundedAmount = round((float) $lockedEnrollment->refunded_amount + $amount, 2);
            $newRefundStatus = ($newRefundedAmount >= (float) $lockedEnrollment->refundable_amount)
                ? 'completed'
                : 'partially_refunded';

            $newSettlementStatus = ($newRefundStatus === 'completed') ? 'completed' : 'pending_refund';

            $lockedEnrollment->update([
                'refunded_amount' => $newRefundedAmount,
                'refund_status' => $newRefundStatus,
                'settlement_status' => $newSettlementStatus,
                'refund_vault_id' => $vault->id,
                'refund_payment_method' => $paymentMethod,
                'refunded_at' => now(),
                'refunded_by' => $user->id,
            ]);

            // 5. Update latest EnrollmentCancellation record
            $cancellation = $lockedEnrollment->cancellations()->latest()->first();
            if ($cancellation) {
                $cancellation->update([
                    'refunded_amount' => $newRefundedAmount,
                    'refund_status' => $newRefundStatus,
                    'settlement_status' => $newSettlementStatus,
                    'refund_vault_id' => $vault->id,
                    'refund_payment_method' => $paymentMethod,
                    'refunded_at' => now(),
                    'refunded_by' => $user->id,
                ]);
            }

            // 6. Audit Log Entry
            AuditLogService::log(
                event: 'refund_executed',
                subject: $lockedEnrollment,
                description: "صرف استرداد مالي بقيمة {$amount} ج.م للطالب {$studentName} من الخزنة {$vault->name}",
                properties: [
                    'amount' => $amount,
                    'vault_id' => $vault->id,
                    'vault_name' => $vault->name,
                    'payment_method' => $paymentMethod,
                    'expense_id' => $expense->id,
                    'expense_code' => $expense->expense_code,
                    'payment_id' => $refundPayment->id,
                    'new_refund_status' => $newRefundStatus,
                ]
            );

            return [
                'success' => true,
                'amount' => $amount,
                'new_refunded_total' => $newRefundedAmount,
                'refund_status' => $newRefundStatus,
                'expense_code' => $expense->expense_code,
                'vault_id' => $vault->id,
                'vault_balance_after' => $vault->fresh()->getCurrentBalance(),
            ];
        });
    }
}

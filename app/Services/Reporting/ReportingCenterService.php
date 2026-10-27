<?php

namespace App\Services\Reporting;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCancellation;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Expense;
use App\Models\Group;
use App\Models\Installment;
use App\Models\Instructor;
use App\Models\InstructorEvaluation;
use App\Models\InstructorPayment;
use App\Models\Payment;
use App\Models\ReportSetting;
use App\Models\Revenue;
use App\Models\SalaryPayment;
use App\Models\SessionSchedule;
use App\Models\Student;
use App\Models\StudentFollowUpContact;
use App\Models\StudentFollowUpTask;
use App\Models\SupervisionReport;
use App\Models\SupervisoryObservation;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultTransfer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportingCenterService
{
    /**
     * Resolve effective date range based on filters.
     */
    public function resolveDateRange(array $filters): array
    {
        $preset = $filters['period_preset'] ?? 'this_month';
        $now = Carbon::now();

        switch ($preset) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'yesterday':
                $start = $now->copy()->subDay()->startOfDay();
                $end = $now->copy()->subDay()->endOfDay();
                break;
            case 'this_week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                break;
            case 'last_week':
                $start = $now->copy()->subWeek()->startOfWeek();
                $end = $now->copy()->subWeek()->endOfWeek();
                break;
            case 'this_month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'this_quarter':
                $start = $now->copy()->firstOfQuarter()->startOfDay();
                $end = $now->copy()->lastOfQuarter()->endOfDay();
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                break;
            case 'custom':
            default:
                $start = !empty($filters['date_from'])
                    ? Carbon::parse($filters['date_from'])->startOfDay()
                    : $now->copy()->startOfMonth();
                $end = !empty($filters['date_to'])
                    ? Carbon::parse($filters['date_to'])->endOfDay()
                    : $now->copy()->endOfDay();
                break;
        }

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }

        $asOfDate = !empty($filters['as_of_date'])
            ? Carbon::parse($filters['as_of_date'])->endOfDay()
            : $end->copy();

        return [
            'start' => $start,
            'end' => $end,
            'as_of_date' => $asOfDate,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'as_of_str' => $asOfDate->toDateString(),
            'preset' => $preset,
        ];
    }

    /**
     * Resolve effective tenant scope for the querying user.
     */
    public function resolveTenantScope(?int $filterTenantId, ?User $user = null): ?int
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return $filterTenantId;
        }

        // Super Admin or Owner without tenant_id can switch branches or see all
        if (($user->is_super_admin || $user->hasRole('super_admin') || $user->hasRole('owner')) && is_null($user->tenant_id)) {
            return $filterTenantId ?: null;
        }

        // Branch-scoped users must strictly view their own tenant
        return (int) $user->tenant_id;
    }

    /**
     * 1. EXECUTIVE OVERVIEW METRICS
     */
    public function getExecutiveOverview(array $filters, ?User $user = null): array
    {
        $range = $this->resolveDateRange($filters);
        $tenantId = $this->resolveTenantScope($filters['branch_id'] ?? null, $user);

        // 1. New unique students in period
        $newStudentsCount = Student::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->count();

        // 2. Total Enrollments & Renewals (Combined approved metric)
        $enrollmentsQuery = Enrollment::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('enrollment_date', [$range['start_date'], $range['end_date']]);

        $totalEnrollmentsCount = (clone $enrollmentsQuery)->where('status', '!=', 'cancelled')->count();
        $totalContractedValue = (float) (clone $enrollmentsQuery)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('COALESCE(SUM(total_amount - discount), 0) as net_val')
            ->value('net_val');

        // 3. Actual cash collections in period
        $actualCollections = (float) Payment::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$range['start_date'], $range['end_date']])
            ->sum('amount');

        // 4. Active students & Active groups as of reference date
        $activeStudentsCount = Student::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->count();

        $activeGroupsCount = Group::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->count();

        // 5. Debt Components as of reference date (Separated: Overdue, Due Today, Future)
        $debtBreakdown = $this->calculateStudentDebtBreakdown($range['as_of_str'], $tenantId);

        // 6. Cash Inflows vs Cash Outflows in period
        $cashFlow = $this->calculateCashMovement($range['start_date'], $range['end_date'], $tenantId);

        // 7. Academic: Completed sessions & unrecorded attendance compliance
        $academicSummary = $this->calculateAcademicExecutiveSummary($range['start_date'], $range['end_date'], $tenantId);

        // 8. Supervision: Open observations & Pending reports
        $supervisionSummary = $this->calculateSupervisionExecutiveSummary($tenantId);

        // 9. Management Exceptions Count
        $alertsCount = $this->getManagementAlertsCount($tenantId, $range['as_of_str']);

        return [
            'period' => $range,
            'tenant_id' => $tenantId,
            'new_students_count' => $newStudentsCount,
            'total_enrollments_count' => $totalEnrollmentsCount,
            'contracted_enrollment_value' => round($totalContractedValue, 2),
            'actual_collections' => round($actualCollections, 2),
            'active_students_count' => $activeStudentsCount,
            'active_groups_count' => $activeGroupsCount,
            'total_outstanding_debt' => $debtBreakdown['total_debt'],
            'overdue_debt' => $debtBreakdown['overdue_debt'],
            'currently_due_debt' => $debtBreakdown['currently_due_debt'],
            'future_installments_debt' => $debtBreakdown['future_debt'],
            'actual_cash_inflows' => $cashFlow['total_inflows'],
            'actual_cash_disbursements' => $cashFlow['total_disbursements'],
            'net_cash_movement' => $cashFlow['net_movement'],
            'completed_sessions_count' => $academicSummary['completed_sessions'],
            'unrecorded_attendance_sessions_count' => $academicSummary['unrecorded_sessions'],
            'attendance_compliance_rate' => $academicSummary['compliance_rate'],
            'open_supervisory_observations' => $supervisionSummary['open_observations'],
            'management_alerts_count' => $alertsCount,
        ];
    }

    /**
     * 2. STUDENT GROWTH & ENROLLMENT REPORTS
     */
    public function getStudentGrowthReport(array $filters, ?User $user = null): array
    {
        $range = $this->resolveDateRange($filters);
        $tenantId = $this->resolveTenantScope($filters['branch_id'] ?? null, $user);

        // Count unique students registered during the period
        $studentsQuery = Student::withoutGlobalScopes()
            ->with(['user', 'tenant'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('created_at', [$range['start'], $range['end']]);

        $totalUniqueStudents = (clone $studentsQuery)->count();

        // Breakdown by branch
        $byBranch = Student::withoutGlobalScopes()
            ->join('tenants', 'tenants.id', '=', 'students.tenant_id')
            ->when($tenantId, fn($q) => $q->where('students.tenant_id', $tenantId))
            ->whereBetween('students.created_at', [$range['start'], $range['end']])
            ->groupBy('tenants.id', 'tenants.name')
            ->selectRaw('tenants.name as branch_name, COUNT(DISTINCT students.id) as count')
            ->get()
            ->toArray();

        // Detailed student records (paginated/limited for performance)
        $detailedStudents = (clone $studentsQuery)
            ->withCount('enrollments')
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'student_code' => $s->student_code ?? '—',
                'name' => $s->user?->name ?? 'طالب #' . $s->id,
                'phone' => $s->phone ?? $s->phone_2 ?? '—',
                'branch_name' => $s->tenant?->name ?? 'المركز الرئيسي',
                'registration_date' => $s->created_at?->format('Y-m-d H:i') ?? '—',
                'enrollments_count' => $s->enrollments_count,
                'status' => $s->status,
            ])
            ->toArray();

        return [
            'total_unique_students' => $totalUniqueStudents,
            'by_branch' => $byBranch,
            'records' => $detailedStudents,
            'period' => $range,
        ];
    }

    /**
     * 3. COMBINED ENROLLMENTS & RENEWALS REPORT
     * APPROVED RULE: New enrollments and renewals MUST be combined into ONE main total.
     */
    public function getEnrollmentsAndRenewalsReport(array $filters, ?User $user = null): array
    {
        $range = $this->resolveDateRange($filters);
        $tenantId = $this->resolveTenantScope($filters['branch_id'] ?? null, $user);
        $courseId = $filters['course_id'] ?? null;
        $groupId = $filters['group_id'] ?? null;

        $baseQuery = Enrollment::withoutGlobalScopes()
            ->with(['student.user', 'tenant', 'courses', 'groups'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($courseId, fn($q) => $q->whereHas('courses', fn($cq) => $cq->where('courses.id', $courseId)))
            ->when($groupId, fn($q) => $q->where('group_id', $groupId))
            ->whereBetween('enrollment_date', [$range['start_date'], $range['end_date']]);

        // Combined Total
        $totalEventsCount = (clone $baseQuery)->where('status', '!=', 'cancelled')->count();
        $totalContractedValue = (float) (clone $baseQuery)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('COALESCE(SUM(total_amount - discount), 0) as val')
            ->value('val');

        $totalDiscountGiven = (float) (clone $baseQuery)
            ->where('status', '!=', 'cancelled')
            ->sum('discount');

        $totalPaidAmount = (float) (clone $baseQuery)
            ->where('status', '!=', 'cancelled')
            ->sum('paid_amount');

        $cancelledCount = (clone $baseQuery)->where('status', 'cancelled')->count();

        // Optional breakdown: New Enrollments vs Renewals
        // An enrollment is classified as renewal if the student had an earlier enrollment in the system
        $records = (clone $baseQuery)
            ->orderBy('enrollment_date', 'desc')
            ->take(100)
            ->get();

        $newCount = 0;
        $renewalsCount = 0;
        $detailedRecords = [];

        foreach ($records as $enr) {
            // Check if student had an enrollment before this one
            $priorEnrollmentsExist = Enrollment::withoutGlobalScopes()
                ->where('student_id', $enr->student_id)
                ->where('id', '!=', $enr->id)
                ->where('enrollment_date', '<', $enr->enrollment_date)
                ->exists();

            $opType = $priorEnrollmentsExist ? 'تجديد اشتراك' : 'تسجيل جديد';
            if ($enr->status !== 'cancelled') {
                if ($priorEnrollmentsExist) {
                    $renewalsCount++;
                } else {
                    $newCount++;
                }
            }

            $courseNames = $enr->courses->pluck('name')->implode(', ') ?: ($enr->group?->course?->name ?? '—');
            $groupNames = $enr->groups->pluck('name')->implode(', ') ?: ($enr->group?->name ?? '—');

            $detailedRecords[] = [
                'id' => $enr->id,
                'enrollment_code' => $enr->enrollment_code ?? 'ENR-' . $enr->id,
                'student_name' => $enr->student?->user?->name ?? 'طالب #' . $enr->student_id,
                'courses' => $courseNames,
                'groups' => $groupNames,
                'branch_name' => $enr->tenant?->name ?? 'الفرع الرئيسي',
                'operation_type' => $opType,
                'date' => $enr->enrollment_date?->format('Y-m-d') ?? '—',
                'total_amount' => (float) $enr->total_amount,
                'discount' => (float) $enr->discount,
                'net_amount' => $enr->getNetAmount(),
                'paid_amount' => (float) $enr->paid_amount,
                'remaining_amount' => $enr->getRemainingBalance(),
                'status' => $enr->status,
            ];
        }

        return [
            'total_enrollment_and_renewal_events' => $totalEventsCount,
            'new_enrollments_count' => $newCount,
            'renewals_count' => $renewalsCount,
            'cancelled_events_count' => $cancelledCount,
            'total_contracted_value' => round($totalContractedValue, 2),
            'total_discount_given' => round($totalDiscountGiven, 2),
            'total_paid_amount' => round($totalPaidAmount, 2),
            'total_outstanding_amount' => round(max(0, $totalContractedValue - $totalPaidAmount), 2),
            'records' => $detailedRecords,
            'period' => $range,
        ];
    }

    /**
     * 4. ACTUAL FINANCIAL COLLECTIONS REPORT
     * APPROVED RULE: Money actually collected in vaults, not contracted.
     * Payments are NOT double counted across multiple installment allocations.
     */
    public function getActualCollectionsReport(array $filters, ?User $user = null): array
    {
        $range = $this->resolveDateRange($filters);
        $tenantId = $this->resolveTenantScope($filters['branch_id'] ?? null, $user);
        $vaultId = $filters['vault_id'] ?? null;
        $paymentMethod = $filters['payment_method'] ?? null;

        $baseQuery = Payment::withoutGlobalScopes()
            ->with(['enrollment.student.user', 'vault', 'receivedBy', 'tenant'])
            ->when($tenantId, fn($q) => $q->where('payments.tenant_id', $tenantId))
            ->when($vaultId, fn($q) => $q->where('payments.vault_id', $vaultId))
            ->when($paymentMethod, fn($q) => $q->where('payments.payment_method', $paymentMethod))
            ->where('payments.status', 'completed')
            ->whereBetween('payments.payment_date', [$range['start_date'], $range['end_date']]);

        $totalCollections = (float) (clone $baseQuery)->sum('payments.amount');
        $collectionsCount = (clone $baseQuery)->count();

        // Breakdown by payment method
        $byMethod = (clone $baseQuery)
            ->selectRaw('payments.payment_method, COUNT(*) as count, SUM(payments.amount) as total')
            ->groupBy('payments.payment_method')
            ->get()
            ->map(fn($row) => [
                'method' => $row->payment_method,
                'method_label' => match($row->payment_method) {
                    'cash' => 'نقداً',
                    'bank_transfer' => 'تحويل بنكي',
                    'visa', 'card' => 'بطاقة ائتمان / فيزا',
                    'vodafone_cash' => 'فودافون كاش / محافظ إلكترونية',
                    default => $row->payment_method ?? 'أخرى'
                },
                'count' => (int) $row->count,
                'total' => round((float) $row->total, 2),
            ])
            ->toArray();

        // Breakdown by Vault
        $byVault = (clone $baseQuery)
            ->join('vaults', 'vaults.id', '=', 'payments.vault_id')
            ->selectRaw('vaults.name as vault_name, COUNT(payments.id) as count, SUM(payments.amount) as total')
            ->groupBy('vaults.id', 'vaults.name')
            ->get()
            ->map(fn($v) => [
                'vault_name' => $v->vault_name,
                'count' => (int) $v->count,
                'total' => round((float) $v->total, 2),
            ])
            ->toArray();

        // Detail records
        $records = (clone $baseQuery)
            ->orderBy('payments.payment_date', 'desc')
            ->orderBy('payments.id', 'desc')
            ->take(100)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'payment_code' => $p->payment_code ?? 'PAY-' . $p->id,
                'payment_date' => $p->payment_date?->format('Y-m-d') ?? '—',
                'student_name' => $p->enrollment?->student?->user?->name ?? 'طالب #' . ($p->enrollment?->student_id ?? '—'),
                'enrollment_code' => $p->enrollment?->enrollment_code ?? '—',
                'amount' => (float) $p->amount,
                'payment_method' => $p->payment_method,
                'vault_name' => $p->vault?->name ?? '—',
                'received_by' => $p->receivedBy?->name ?? '—',
                'transaction_reference' => $p->transaction_reference ?? '—',
                'status' => $p->status,
            ])
            ->toArray();

        return [
            'total_collections' => round($totalCollections, 2),
            'collections_count' => $collectionsCount,
            'by_method' => $byMethod,
            'by_vault' => $byVault,
            'records' => $records,
            'period' => $range,
        ];
    }

    /**
     * 5. INSTALLMENTS AND RECEIVABLES REPORT
     * APPROVED RULE: Clean partition into Overdue, Currently Due, and Future installment balances.
     */
    public function getInstallmentsReport(array $filters, ?User $user = null): array
    {
        $range = $this->resolveDateRange($filters);
        $tenantId = $this->resolveTenantScope($filters['branch_id'] ?? null, $user);
        $asOfStr = $range['as_of_str'];
        $courseId = $filters['course_id'] ?? null;

        $baseQuery = Installment::withoutGlobalScopes()
            ->with(['enrollment.student.user', 'enrollment.courses', 'tenant'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($courseId, fn($q) => $q->whereHas('enrollment.courses', fn($cq) => $cq->where('courses.id', $courseId)))
            ->whereNotIn('status', ['cancelled', 'waived'])
            ->whereHas('enrollment', fn($q) => $q->where('status', '!=', 'cancelled'));

        // Overdue: due_date < as_of and paid_amount < amount
        $overdueQuery = (clone $baseQuery)
            ->whereDate('due_date', '<', $asOfStr)
            ->whereRaw('paid_amount < amount');

        $overdueCount = (clone $overdueQuery)->count();
        $overdueAmount = (float) (clone $overdueQuery)->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as rem')->value('rem');

        // Currently Due: due_date = as_of and paid_amount < amount
        $currentlyDueQuery = (clone $baseQuery)
            ->whereDate('due_date', '=', $asOfStr)
            ->whereRaw('paid_amount < amount');

        $currentlyDueCount = (clone $currentlyDueQuery)->count();
        $currentlyDueAmount = (float) (clone $currentlyDueQuery)->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as rem')->value('rem');

        // Future: due_date > as_of and paid_amount < amount
        $futureQuery = (clone $baseQuery)
            ->whereDate('due_date', '>', $asOfStr)
            ->whereRaw('paid_amount < amount');

        $futureCount = (clone $futureQuery)->count();
        $futureAmount = (float) (clone $futureQuery)->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as rem')->value('rem');

        // Fully Paid Installments
        $paidQuery = (clone $baseQuery)->whereRaw('paid_amount >= amount');
        $paidCount = (clone $paidQuery)->count();
        $paidAmount = (float) (clone $paidQuery)->sum('paid_amount');

        // Aging Buckets for Overdue Debt
        $aging = [
            '1_7_days' => 0.0,
            '8_30_days' => 0.0,
            '31_60_days' => 0.0,
            'over_60_days' => 0.0,
        ];

        $overdueRows = (clone $overdueQuery)->select('due_date', 'amount', 'paid_amount')->get();
        $asOfCarbon = Carbon::parse($asOfStr)->startOfDay();

        foreach ($overdueRows as $row) {
            $dueCarbon = Carbon::parse($row->due_date)->startOfDay();
            $days = $dueCarbon->diffInDays($asOfCarbon, false);
            $rem = max(0, (float) $row->amount - (float) $row->paid_amount);

            if ($days >= 1 && $days <= 7) {
                $aging['1_7_days'] += $rem;
            } elseif ($days >= 8 && $days <= 30) {
                $aging['8_30_days'] += $rem;
            } elseif ($days >= 31 && $days <= 60) {
                $aging['31_60_days'] += $rem;
            } elseif ($days > 60) {
                $aging['over_60_days'] += $rem;
            }
        }

        // Detailed installment records
        $records = (clone $baseQuery)
            ->whereRaw('paid_amount < amount')
            ->orderBy('due_date', 'asc')
            ->take(100)
            ->get()
            ->map(function($inst) use ($asOfCarbon) {
                $dueCarbon = Carbon::parse($inst->due_date)->startOfDay();
                $isOverdue = $dueCarbon->lessThan($asOfCarbon);
                $isDueToday = $dueCarbon->equalTo($asOfCarbon);

                $dueStatusLabel = $isOverdue ? 'متأخر' : ($isDueToday ? 'مستحق اليوم' : 'قادم');
                $paymentStatusLabel = $inst->paid_amount > 0 ? 'مسدد جزئياً' : 'غير مسدد';

                return [
                    'id' => $inst->id,
                    'installment_number' => $inst->installment_number,
                    'student_name' => $inst->enrollment?->student?->user?->name ?? 'طالب #' . $inst->enrollment?->student_id,
                    'enrollment_code' => $inst->enrollment?->enrollment_code ?? '—',
                    'due_date' => $inst->due_date?->format('Y-m-d') ?? '—',
                    'original_amount' => (float) $inst->amount,
                    'paid_amount' => (float) $inst->paid_amount,
                    'remaining_amount' => max(0, (float) $inst->amount - (float) $inst->paid_amount),
                    'due_status' => $dueStatusLabel,
                    'payment_status' => $paymentStatusLabel,
                    'days_overdue' => $isOverdue ? $dueCarbon->diffInDays($asOfCarbon, false) : 0,
                    'branch_name' => $inst->tenant?->name ?? '—',
                ];
            })
            ->toArray();

        return [
            'total_outstanding_debt' => round($overdueAmount + $currentlyDueAmount + $futureAmount, 2),
            'overdue_amount' => round($overdueAmount, 2),
            'overdue_count' => $overdueCount,
            'currently_due_amount' => round($currentlyDueAmount, 2),
            'currently_due_count' => $currentlyDueCount,
            'future_amount' => round($futureAmount, 2),
            'future_count' => $futureCount,
            'paid_amount' => round($paidAmount, 2),
            'paid_count' => $paidCount,
            'aging_buckets' => [
                '1_7_days' => round($aging['1_7_days'], 2),
                '8_30_days' => round($aging['8_30_days'], 2),
                '31_60_days' => round($aging['31_60_days'], 2),
                'over_60_days' => round($aging['over_60_days'], 2),
            ],
            'records' => $records,
            'as_of_date' => $asOfStr,
        ];
    }

    /**
     * 6. VAULT AND CASH MOVEMENT REPORT
     * APPROVED RULE: Transfers between vaults do NOT create new income or expense for the school.
     */
    public function getVaultReport(array $filters, ?User $user = null): array
    {
        $range = $this->resolveDateRange($filters);
        $tenantId = $this->resolveTenantScope($filters['branch_id'] ?? null, $user);
        $vaultId = $filters['vault_id'] ?? null;

        $vaults = Vault::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($vaultId, fn($q) => $q->where('id', $vaultId))
            ->where('is_active', true)
            ->get();

        $vaultReports = [];
        $schoolTotalOpening = 0.0;
        $schoolTotalInflows = 0.0;
        $schoolTotalOutflows = 0.0;
        $schoolTotalClosing = 0.0;

        foreach ($vaults as $v) {
            $opening = $this->calculateVaultBalanceAsOf($v->id, $range['start']);

            // Cash collections in period
            $receipts = (float) Payment::withoutGlobalScopes()
                ->where('vault_id', $v->id)
                ->where('status', 'completed')
                ->whereBetween('payment_date', [$range['start_date'], $range['end_date']])
                ->sum('amount');

            // Operational expenses
            $expenses = (float) Expense::withoutGlobalScopes()
                ->where('vault_id', $v->id)
                ->whereIn('status', ['paid', 'completed'])
                ->whereBetween('expense_date', [$range['start_date'], $range['end_date']])
                ->sum('amount');

            // Salary payments
            $salaries = (float) SalaryPayment::withoutGlobalScopes()
                ->where('vault_id', $v->id)
                ->whereIn('status', ['paid', 'completed'])
                ->whereBetween('payment_date', [$range['start_date'], $range['end_date']])
                ->sum('amount');

            // Refunds disbursed
            $refunds = (float) EnrollmentCancellation::withoutGlobalScopes()
                ->where('refund_vault_id', $v->id)
                ->where('refund_status', 'refunded')
                ->whereBetween('refunded_at', [$range['start'], $range['end']])
                ->sum('refunded_amount');

            // Transfers In to this vault
            $transfersIn = (float) VaultTransfer::withoutGlobalScopes()
                ->where('to_vault_id', $v->id)
                ->where('status', 'completed')
                ->whereBetween('transfer_date', [$range['start_date'], $range['end_date']])
                ->sum('amount');

            // Transfers Out from this vault
            $transfersOut = (float) VaultTransfer::withoutGlobalScopes()
                ->where('from_vault_id', $v->id)
                ->where('status', 'completed')
                ->whereBetween('transfer_date', [$range['start_date'], $range['end_date']])
                ->sum('amount');

            $inflows = $receipts + $transfersIn;
            $outflows = $expenses + $salaries + $refunds + $transfersOut;
            $closing = $opening + $inflows - $outflows;

            $schoolTotalOpening += $opening;
            $schoolTotalInflows += $receipts; // Internal transfers excluded from school-wide net
            $schoolTotalOutflows += ($expenses + $salaries + $refunds);
            $schoolTotalClosing += $closing;

            $vaultReports[] = [
                'vault_id' => $v->id,
                'vault_name' => $v->name,
                'opening_balance' => round($opening, 2),
                'cash_receipts' => round($receipts, 2),
                'transfers_in' => round($transfersIn, 2),
                'total_inflows' => round($inflows, 2),
                'expenses' => round($expenses, 2),
                'salaries' => round($salaries, 2),
                'refunds' => round($refunds, 2),
                'transfers_out' => round($transfersOut, 2),
                'total_outflows' => round($outflows, 2),
                'closing_balance' => round($closing, 2),
                'current_system_balance' => round((float) $v->balance, 2),
            ];
        }

        return [
            'vaults' => $vaultReports,
            'school_summary' => [
                'total_opening_balance' => round($schoolTotalOpening, 2),
                'total_external_inflows' => round($schoolTotalInflows, 2),
                'total_external_outflows' => round($schoolTotalOutflows, 2),
                'net_cash_movement' => round($schoolTotalInflows - $schoolTotalOutflows, 2),
                'total_closing_balance' => round($schoolTotalClosing, 2),
            ],
            'period' => $range,
        ];
    }

    /**
     * 7. REVENUE, EXPENSES AND PROFIT & LOSS REPORT
     * APPROVED RULE: P&L is separate from Cash Flow.
     * Cash collection != Revenue. Clearly document accounting policy gaps.
     */
    public function getRevenueAndExpenseReport(array $filters, ?User $user = null): array
    {
        $range = $this->resolveDateRange($filters);
        $tenantId = $this->resolveTenantScope($filters['branch_id'] ?? null, $user);

        // 1. Operating Expenses by Category
        $expenses = Expense::withoutGlobalScopes()
            ->with('category')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'completed')
            ->whereBetween('expense_date', [$range['start_date'], $range['end_date']])
            ->get();

        $totalExpenses = (float) $expenses->sum('amount');
        $expensesByCategory = $expenses->groupBy('expense_category_id')->map(function($items) {
            $catName = $items->first()->category?->name ?? 'مصروفات تشغيلية عامة';
            return [
                'category_name' => $catName,
                'count' => $items->count(),
                'total' => round((float) $items->sum('amount'), 2),
            ];
        })->values()->toArray();

        // 2. Instructor Compensation Earned in period
        $instructorCompensationEarned = (float) InstructorPayment::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('period_start', [$range['start_date'], $range['end_date']])
            ->sum('total_amount');

        // 3. Recognized Revenues if any in revenues table
        $recognizedRevenues = (float) Revenue::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'completed')
            ->whereBetween('revenue_date', [$range['start_date'], $range['end_date']])
            ->sum('amount');

        // 4. Contracted Enrollment Net Value
        $contractedEnrollmentValue = (float) Enrollment::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', '!=', 'cancelled')
            ->whereBetween('enrollment_date', [$range['start_date'], $range['end_date']])
            ->selectRaw('COALESCE(SUM(total_amount - discount), 0) as net_val')
            ->value('net_val');

        // 5. Actual Cash Collections
        $actualCollections = (float) Payment::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$range['start_date'], $range['end_date']])
            ->sum('amount');

        // 6. Actual Salary Disbursements
        $actualSalaryDisbursements = (float) SalaryPayment::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$range['start_date'], $range['end_date']])
            ->sum('amount');

        return [
            'period' => $range,
            'cash_flow_view' => [
                'cash_collections' => round($actualCollections, 2),
                'cash_expenses_paid' => round($totalExpenses, 2),
                'salaries_paid' => round($actualSalaryDisbursements, 2),
                'net_cash_flow' => round($actualCollections - ($totalExpenses + $actualSalaryDisbursements), 2),
            ],
            'operating_statement_view' => [
                'status' => 'operational_accrual_analysis',
                'contracted_enrollments_value' => round($contractedEnrollmentValue, 2),
                'direct_revenues_recorded' => round($recognizedRevenues, 2),
                'instructor_compensation_earned' => round($instructorCompensationEarned, 2),
                'operating_expenses_recorded' => round($totalExpenses, 2),
                'operating_result_analytic' => round(($contractedEnrollmentValue + $recognizedRevenues) - ($instructorCompensationEarned + $totalExpenses), 2),
                'accounting_policy_notice' => 'تنبيه محاسبي: هذا التقرير يوضح المركز التشغيلي التحليلي المقارن بين التدفق النقدي والالتزامات التعاقدية. النظام لا يعتمد معادلة الكاش بديلاً عن قائمة الأرباح والخسائر الرسمية.',
            ],
            'expenses_by_category' => $expensesByCategory,
        ];
    }

    /**
     * 8. INSTRUCTOR SALARY REPORTS
     * APPROVED RULE: Ratings (1-5) strictly have 0 automatic salary deduction/bonus impact.
     */
    public function getInstructorSalaryReport(array $filters, ?User $user = null): array
    {
        $range = $this->resolveDateRange($filters);
        $tenantId = $this->resolveTenantScope($filters['branch_id'] ?? null, $user);
        $instructorId = $filters['instructor_id'] ?? null;

        $paymentsQuery = InstructorPayment::withoutGlobalScopes()
            ->with(['instructor.user', 'tenant'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($instructorId, fn($q) => $q->where('instructor_id', $instructorId))
            ->whereBetween('period_start', [$range['start_date'], $range['end_date']]);

        $totalEarned = (float) (clone $paymentsQuery)->sum('total_amount');
        $totalSessionsCount = (int) (clone $paymentsQuery)->sum('completed_sessions');
        $totalBonus = (float) (clone $paymentsQuery)->sum('bonus_amount');
        $totalDeductions = (float) (clone $paymentsQuery)->sum('deduction_amount');

        // Actual disbursements from salary_payments table
        $disbursementsQuery = SalaryPayment::withoutGlobalScopes()
            ->with(['vault', 'tenant'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($instructorId, fn($q) => $q->where('instructor_id', $instructorId))
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$range['start_date'], $range['end_date']]);

        $totalDisbursed = (float) (clone $disbursementsQuery)->sum('amount');
        $outstandingBalance = max(0, $totalEarned - $totalDisbursed);

        $records = (clone $paymentsQuery)
            ->orderBy('period_start', 'desc')
            ->take(100)
            ->get()
            ->map(fn($ip) => [
                'id' => $ip->id,
                'instructor_name' => $ip->instructor?->user?->name ?? 'مهندس #' . $ip->instructor_id,
                'period_label' => $ip->period_label ?? ($ip->period_start?->format('Y-m-d') . ' إلى ' . $ip->period_end?->format('Y-m-d')),
                'completed_sessions' => $ip->completed_sessions,
                'rate_per_session' => (float) $ip->rate_per_session,
                'base_amount' => (float) $ip->base_amount,
                'bonus_amount' => (float) $ip->bonus_amount,
                'deduction_amount' => (float) $ip->deduction_amount,
                'total_amount' => (float) $ip->total_amount,
                'status' => $ip->status,
                'payment_date' => $ip->payment_date?->format('Y-m-d') ?? '—',
                'branch_name' => $ip->tenant?->name ?? '—',
            ])
            ->toArray();

        return [
            'total_earned' => round($totalEarned, 2),
            'total_disbursed' => round($totalDisbursed, 2),
            'outstanding_salary' => round($outstandingBalance, 2),
            'total_completed_sessions' => $totalSessionsCount,
            'total_bonus' => round($totalBonus, 2),
            'total_deductions' => round($totalDeductions, 2),
            'records' => $records,
            'period' => $range,
        ];
    }

    /**
     * 9. ENROLLMENT CANCELLATION, SETTLEMENT & REFUND REPORTS
     * APPROVED RULE: Independent separation between cancellation, settlement, and disbursement.
     */
    public function getCancellationAndRefundReport(array $filters, ?User $user = null): array
    {
        $range = $this->resolveDateRange($filters);
        $tenantId = $this->resolveTenantScope($filters['branch_id'] ?? null, $user);

        $baseQuery = EnrollmentCancellation::withoutGlobalScopes()
            ->with(['enrollment.student.user', 'cancelledBy', 'tenant'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('created_at', [$range['start'], $range['end']]);

        $totalCancellationsCount = (clone $baseQuery)->count();
        $totalOriginalPrice = (float) (clone $baseQuery)->sum('original_price');
        $totalNetPrice = (float) (clone $baseQuery)->sum('net_price');
        $totalPaidBeforeCancellation = (float) (clone $baseQuery)->sum('total_paid');

        // Settlement breakdown
        $totalWaivedDebt = (float) (clone $baseQuery)->sum('waived_amount');
        $totalRetainedAmount = (float) (clone $baseQuery)->sum('retained_amount');
        $totalRefundableLiability = (float) (clone $baseQuery)->sum('refundable_amount');
        $totalActuallyRefunded = (float) (clone $baseQuery)->where('refund_status', 'refunded')->sum('refunded_amount');
        $pendingRefundsLiability = max(0, $totalRefundableLiability - $totalActuallyRefunded);

        $records = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'enrollment_id' => $c->enrollment_id,
                'student_name' => $c->enrollment?->student?->user?->name ?? 'طالب #' . $c->student_id,
                'cancelled_at' => $c->created_at?->format('Y-m-d H:i') ?? '—',
                'cancelled_by' => $c->cancelledBy?->name ?? '—',
                'cancellation_reason' => $c->cancellation_reason ?? '—',
                'settlement_type' => match($c->settlement_type) {
                    'waive_debt' => 'إسقاط مديونية',
                    'full_refund' => 'استرداد كامل',
                    'partial_refund' => 'استرداد جزئي',
                    'retained_all' => 'احتفاظ كامل بالمبلغ',
                    default => $c->settlement_type ?? 'معلق'
                },
                'settlement_status' => $c->settlement_status ?? 'pending',
                'net_price' => (float) $c->net_price,
                'total_paid' => (float) $c->total_paid,
                'waived_amount' => (float) $c->waived_amount,
                'retained_amount' => (float) $c->retained_amount,
                'refundable_amount' => (float) $c->refundable_amount,
                'refunded_amount' => (float) $c->refunded_amount,
                'refund_status' => $c->refund_status ?? 'none',
                'branch_name' => $c->tenant?->name ?? '—',
            ])
            ->toArray();

        return [
            'cancellations_count' => $totalCancellationsCount,
            'contracted_value_cancelled' => round($totalNetPrice, 2),
            'cash_paid_on_cancelled' => round($totalPaidBeforeCancellation, 2),
            'waived_debt_total' => round($totalWaivedDebt, 2),
            'retained_amount_total' => round($totalRetainedAmount, 2),
            'total_refund_liability' => round($totalRefundableLiability, 2),
            'actual_refunds_disbursed' => round($totalActuallyRefunded, 2),
            'pending_refund_liability' => round($pendingRefundsLiability, 2),
            'records' => $records,
            'period' => $range,
        ];
    }

    /**
     * 10. ACADEMIC PERFORMANCE REPORTS
     * APPROVED RULE: Separate indicators per course. Unrecorded attendance != student absence.
     * Low score alert strictly < 50% on official published grades.
     */
    public function getAcademicPerformanceReport(array $filters, ?User $user = null): array
    {
        $range = $this->resolveDateRange($filters);
        $tenantId = $this->resolveTenantScope($filters['branch_id'] ?? null, $user);
        $courseId = $filters['course_id'] ?? null;
        $groupId = $filters['group_id'] ?? null;

        // 1. Session Schedules & Completion
        $sessionsQuery = SessionSchedule::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($courseId, fn($q) => $q->where('course_id', $courseId))
            ->when($groupId, fn($q) => $q->where('group_id', $groupId))
            ->whereBetween('date', [$range['start_date'], $range['end_date']]);

        $totalScheduled = (clone $sessionsQuery)->count();
        $completedSessions = (clone $sessionsQuery)->where('status', 'completed')->count();
        $postponedSessions = (clone $sessionsQuery)->where('status', 'postponed')->count();
        $cancelledSessions = (clone $sessionsQuery)->where('status', 'cancelled')->count();

        // 2. Attendance Compliance: Sessions WITHOUT attendance records
        $unrecordedAttendanceSessions = AttendanceSession::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($courseId, fn($q) => $q->where('course_id', $courseId))
            ->when($groupId, fn($q) => $q->where('group_id', $groupId))
            ->whereBetween('attendance_date', [$range['start_date'], $range['end_date']])
            ->whereDoesntHave('records')
            ->count();

        // Attendance records breakdown
        $attendanceStats = AttendanceRecord::withoutGlobalScopes()
            ->whereHas('attendanceSession', function($q) use ($tenantId, $courseId, $groupId, $range) {
                $q->when($tenantId, fn($sq) => $sq->where('tenant_id', $tenantId))
                    ->when($courseId, fn($sq) => $sq->where('course_id', $courseId))
                    ->when($groupId, fn($sq) => $sq->where('group_id', $groupId))
                    ->whereBetween('attendance_date', [$range['start_date'], $range['end_date']]);
            })
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $presentCount = $attendanceStats['present'] ?? 0;
        $absentCount = $attendanceStats['absent'] ?? 0;
        $lateCount = $attendanceStats['late'] ?? 0;
        $excusedCount = $attendanceStats['excused'] ?? 0;
        $totalRecorded = $presentCount + $absentCount + $lateCount + $excusedCount;

        // 3. Homework & Submissions
        $homeworkSubmissionsQuery = AssignmentSubmission::withoutGlobalScopes()
            ->with(['assignment.group', 'student.user'])
            ->whereHas('assignment', function($q) use ($tenantId, $courseId, $groupId, $range) {
                $q->when($tenantId, fn($sq) => $sq->where('tenant_id', $tenantId))
                    ->when($groupId, fn($sq) => $sq->where('group_id', $groupId))
                    ->whereBetween('due_date', [$range['start_date'], $range['end_date']]);
            });

        $totalSubmissions = (clone $homeworkSubmissionsQuery)->whereNotNull('submitted_at')->count();
        $gradedPublishedSubmissions = (clone $homeworkSubmissionsQuery)->whereNotNull('published_at')->count();
        $pendingGradingSubmissions = (clone $homeworkSubmissionsQuery)->whereNotNull('submitted_at')->whereNull('score')->count();

        // Low homework scores strictly < 50% of max score
        $lowHomeworkScores = (clone $homeworkSubmissionsQuery)
            ->whereNotNull('assignment_submissions.published_at')
            ->whereNotNull('assignment_submissions.score')
            ->join('assignments', 'assignments.id', '=', 'assignment_submissions.assignment_id')
            ->whereRaw('assignment_submissions.score < (assignments.max_score * 0.5)')
            ->count();

        // 4. Exams & Attempts
        $examAttemptsQuery = ExamAttempt::withoutGlobalScopes()
            ->with(['exam.course', 'student.user'])
            ->whereHas('exam', function($q) use ($tenantId, $courseId, $groupId, $range) {
                $q->when($tenantId, fn($sq) => $sq->where('tenant_id', $tenantId))
                    ->when($courseId, fn($sq) => $sq->where('course_id', $courseId))
                    ->when($groupId, fn($sq) => $sq->where('group_id', $groupId))
                    ->where('status', 'published')
                    ->whereBetween('exam_date', [$range['start_date'], $range['end_date']]);
            });

        $totalExamAttempts = (clone $examAttemptsQuery)->count();
        $passedExamAttempts = (clone $examAttemptsQuery)->where('passed', true)->count();

        // Published exams strictly < 50%
        $lowExamScores = (clone $examAttemptsQuery)
            ->whereNotNull('score')
            ->where('percentage', '<', 50.00)
            ->count();

        return [
            'sessions' => [
                'total_scheduled' => $totalScheduled,
                'completed' => $completedSessions,
                'postponed' => $postponedSessions,
                'cancelled' => $cancelledSessions,
            ],
            'attendance' => [
                'unrecorded_sessions_count' => $unrecordedAttendanceSessions,
                'total_recorded_entries' => $totalRecorded,
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'late_count' => $lateCount,
                'excused_count' => $excusedCount,
                'attendance_rate' => $totalRecorded > 0 ? round(($presentCount / $totalRecorded) * 100, 1) : 0,
            ],
            'homework' => [
                'total_submissions' => $totalSubmissions,
                'graded_published' => $gradedPublishedSubmissions,
                'pending_grading' => $pendingGradingSubmissions,
                'low_scores_count' => $lowHomeworkScores,
            ],
            'exams' => [
                'total_attempts' => $totalExamAttempts,
                'passed_attempts' => $passedExamAttempts,
                'pass_rate' => $totalExamAttempts > 0 ? round(($passedExamAttempts / $totalExamAttempts) * 100, 1) : 0,
                'low_scores_count' => $lowExamScores,
            ],
            'period' => $range,
        ];
    }

    /**
     * 11. EDUCATIONAL SUPERVISION REPORTS
     * APPROVED RULE: Primary states: جديدة, بانتظار مراجعة المشرف, مكتملة.
     * Supervisor-only completion sign-off.
     */
    public function getEducationalSupervisionReport(array $filters, ?User $user = null): array
    {
        $tenantId = $this->resolveTenantScope($filters['branch_id'] ?? null, $user);

        $obsQuery = SupervisoryObservation::withoutGlobalScopes()
            ->with(['supervisor', 'instructor.user', 'group', 'tenant'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        $totalObs = (clone $obsQuery)->count();
        $newObs = (clone $obsQuery)->where('status', SupervisoryObservation::STATUS_NEW)->count();
        $waitingReviewObs = (clone $obsQuery)->where('status', SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW)->count();
        $completedObs = (clone $obsQuery)->where('status', SupervisoryObservation::STATUS_COMPLETED)->count();
        $returnedObs = (clone $obsQuery)->where(fn($q) => $q->where('status', 'returned')->orWhereNotNull('returned_at'))->count();

        // Monthly evaluations
        $evalQuery = InstructorEvaluation::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        $totalEvaluations = (clone $evalQuery)->count();
        $publishedEvaluations = (clone $evalQuery)->where('status', InstructorEvaluation::STATUS_PUBLISHED)->count();
        $draftEvaluations = (clone $evalQuery)->where('status', InstructorEvaluation::STATUS_DRAFT)->count();

        // Official supervision reports (Weekly / Monthly)
        $reportsQuery = SupervisionReport::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));

        $publishedReports = (clone $reportsQuery)->where('status', SupervisionReport::STATUS_PUBLISHED)->count();
        $draftReports = (clone $reportsQuery)->where('status', SupervisionReport::STATUS_DRAFT)->count();

        $recentObservations = (clone $obsQuery)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->map(fn($o) => [
                'id' => $o->id,
                'observation_code' => $o->observation_code ?? 'OBS-' . $o->id,
                'title' => $o->title,
                'instructor_name' => $o->instructor?->user?->name ?? '—',
                'supervisor_name' => $o->supervisor?->name ?? '—',
                'group_name' => $o->group?->name ?? '—',
                'priority' => $o->priority,
                'status' => $o->status,
                'status_label' => match($o->status) {
                    SupervisoryObservation::STATUS_NEW => 'جديدة',
                    SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW => 'بانتظار مراجعة المشرف',
                    SupervisoryObservation::STATUS_COMPLETED => 'مكتملة',
                    SupervisoryObservation::STATUS_RETURNED => 'معادة للتعديل',
                    default => $o->status
                },
                'created_at' => $o->created_at?->format('Y-m-d') ?? '—',
                'expected_date' => $o->expected_resolution_date?->format('Y-m-d') ?? '—',
                'branch_name' => $o->tenant?->name ?? '—',
            ])
            ->toArray();

        return [
            'observations' => [
                'total' => $totalObs,
                'new' => $newObs,
                'waiting_review' => $waitingReviewObs,
                'completed' => $completedObs,
                'returned' => $returnedObs,
            ],
            'evaluations' => [
                'total' => $totalEvaluations,
                'published' => $publishedEvaluations,
                'draft' => $draftEvaluations,
            ],
            'supervision_reports' => [
                'published' => $publishedReports,
                'draft' => $draftReports,
            ],
            'recent_observations' => $recentObservations,
        ];
    }

    /**
     * 12. EMPLOYEE OPERATIONAL REPORTS
     * APPROVED RULE: Operational metrics depend on role; no universal composite score.
     */
    public function getEmployeeOperationalReport(array $filters, ?User $user = null): array
    {
        $range = $this->resolveDateRange($filters);
        $tenantId = $this->resolveTenantScope($filters['branch_id'] ?? null, $user);

        // 1. Registration Employees (Distinct created students & enrollments)
        $registrationStaff = User::role('registration')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->get()
            ->map(function($emp) use ($range) {
                $studentsRegistered = Student::withoutGlobalScopes()
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();

                $enrollmentsCount = Enrollment::withoutGlobalScopes()
                    ->whereBetween('enrollment_date', [$range['start_date'], $range['end_date']])
                    ->count();

                return [
                    'id' => $emp->id,
                    'name' => $emp->name,
                    'role' => 'مسؤول تسجيل',
                    'students_registered' => $studentsRegistered,
                    'enrollments_recorded' => $enrollmentsCount,
                    'branch' => $emp->tenant?->name ?? '—',
                ];
            })
            ->toArray();

        // 2. Accountants (Payments received, Vault transactions, Expenses recorded)
        $accountants = User::role('accountant')
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->get()
            ->map(function($acc) use ($range) {
                $paymentsCount = Payment::withoutGlobalScopes()
                    ->where('received_by', $acc->id)
                    ->whereBetween('payment_date', [$range['start_date'], $range['end_date']])
                    ->count();

                $collectedAmount = (float) Payment::withoutGlobalScopes()
                    ->where('received_by', $acc->id)
                    ->whereBetween('payment_date', [$range['start_date'], $range['end_date']])
                    ->sum('amount');

                return [
                    'id' => $acc->id,
                    'name' => $acc->name,
                    'role' => 'محاسب',
                    'payments_recorded_count' => $paymentsCount,
                    'collections_amount' => round($collectedAmount, 2),
                    'branch' => $acc->tenant?->name ?? '—',
                ];
            })
            ->toArray();

        // 3. Student Follow-Up Staff (Tasks, Contacts)
        $assignedUserIds = StudentFollowUpTask::withoutGlobalScopes()->whereNotNull('assigned_to')->pluck('assigned_to')->unique()->toArray();
        $followUpStaff = User::whereIn('id', $assignedUserIds)
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->take(20)
            ->get()
            ->map(function($fu) use ($range) {
                $assignedTasks = StudentFollowUpTask::withoutGlobalScopes()
                    ->where('assigned_to', $fu->id)
                    ->whereBetween('created_at', [$range['start'], $range['end']])
                    ->count();

                $contactAttempts = StudentFollowUpContact::withoutGlobalScopes()
                    ->where('recorded_by', $fu->id)
                    ->whereBetween('contacted_at', [$range['start'], $range['end']])
                    ->count();

                return [
                    'id' => $fu->id,
                    'name' => $fu->name,
                    'role' => 'متابعة طلاب',
                    'tasks_assigned' => $assignedTasks,
                    'contacts_logged' => $contactAttempts,
                    'branch' => $fu->tenant?->name ?? '—',
                ];
            })
            ->toArray();

        return [
            'registration_staff' => $registrationStaff,
            'accountants' => $accountants,
            'follow_up_staff' => $followUpStaff,
            'period' => $range,
        ];
    }

    /**
     * 13. MULTI-BRANCH COMPARISON REPORTS
     */
    public function getBranchComparisonReport(array $filters, ?User $user = null): array
    {
        $range = $this->resolveDateRange($filters);
        $user = $user ?? auth()->user();

        // Only super_admin or users with permission can compare all branches
        $tenants = Tenant::all();
        $comparison = [];

        foreach ($tenants as $branch) {
            $tid = $branch->id;

            $newStudents = Student::withoutGlobalScopes()
                ->where('tenant_id', $tid)
                ->whereBetween('created_at', [$range['start'], $range['end']])
                ->count();

            $enrollmentsCount = Enrollment::withoutGlobalScopes()
                ->where('tenant_id', $tid)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('enrollment_date', [$range['start_date'], $range['end_date']])
                ->count();

            $contractedValue = (float) Enrollment::withoutGlobalScopes()
                ->where('tenant_id', $tid)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('enrollment_date', [$range['start_date'], $range['end_date']])
                ->selectRaw('COALESCE(SUM(total_amount - discount), 0) as val')
                ->value('val');

            $collections = (float) Payment::withoutGlobalScopes()
                ->where('tenant_id', $tid)
                ->where('status', 'completed')
                ->whereBetween('payment_date', [$range['start_date'], $range['end_date']])
                ->sum('amount');

            $debt = $this->calculateStudentDebtBreakdown($range['as_of_str'], $tid);

            $activeStudents = Student::withoutGlobalScopes()
                ->where('tenant_id', $tid)
                ->where('status', 'active')
                ->count();

            $activeGroups = Group::withoutGlobalScopes()
                ->where('tenant_id', $tid)
                ->where('status', 'active')
                ->count();

            $unrecordedSessions = AttendanceSession::withoutGlobalScopes()
                ->where('tenant_id', $tid)
                ->whereBetween('attendance_date', [$range['start_date'], $range['end_date']])
                ->whereDoesntHave('records')
                ->count();

            $comparison[] = [
                'branch_id' => $tid,
                'branch_name' => $branch->name,
                'new_students' => $newStudents,
                'enrollments_count' => $enrollmentsCount,
                'contracted_value' => round($contractedValue, 2),
                'actual_collections' => round($collections, 2),
                'total_debt' => $debt['total_debt'],
                'overdue_debt' => $debt['overdue_debt'],
                'active_students' => $activeStudents,
                'active_groups' => $activeGroups,
                'unrecorded_attendance_sessions' => $unrecordedSessions,
            ];
        }

        return [
            'branches' => $comparison,
            'period' => $range,
        ];
    }

    /**
     * 14. MANAGEMENT ALERTS AND EXCEPTIONS
     * APPROVED RULE: Real data only. No automated executions (no auto-debit, no auto-messages).
     */
    public function getManagementAlerts(?int $tenantId = null, ?string $asOfDate = null): Collection
    {
        $asOf = $asOfDate ? Carbon::parse($asOfDate)->startOfDay() : Carbon::now()->startOfDay();
        $asOfStr = $asOf->toDateString();
        $alerts = collect();

        // 1. High Overdue Student Debt (> 1000 EGP or setting threshold)
        $threshold = (float) (ReportSetting::where('tenant_id', $tenantId)->value('alert_threshold_debt') ?? 1000.00);

        $highDebtInstallments = Installment::withoutGlobalScopes()
            ->with(['enrollment.student.user', 'tenant'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', '!=', 'cancelled')
            ->whereDate('due_date', '<', $asOfStr)
            ->whereRaw('(amount - paid_amount) >= ?', [$threshold])
            ->take(15)
            ->get();

        foreach ($highDebtInstallments as $inst) {
            $rem = (float) $inst->amount - (float) $inst->paid_amount;
            $alerts->push([
                'category' => 'financial_debt',
                'type' => 'overdue_debt',
                'severity' => 'danger',
                'title' => "قسط متأخر بمبلغ مرتفع: {$rem} ج.م",
                'description' => "الطالب {$inst->enrollment?->student?->user?->name} لديه قسط متأخر مستحق بتاريخ {$inst->due_date?->format('Y-m-d')}.",
                'date' => $inst->due_date?->format('Y-m-d'),
                'branch_name' => $inst->tenant?->name ?? '—',
                'record_id' => $inst->id,
            ]);
        }

        // 2. Unrecorded Attendance Sessions (Compliance issue)
        $unrecorded = AttendanceSession::withoutGlobalScopes()
            ->with(['group.instructor.user', 'tenant'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereDoesntHave('records')
            ->orderBy('attendance_date', 'desc')
            ->take(15)
            ->get();

        foreach ($unrecorded as $session) {
            $alerts->push([
                'category' => 'academic_compliance',
                'type' => 'unrecorded_attendance',
                'severity' => 'warning',
                'title' => "جلسة غير مسجل بها الحضور: {$session->group?->name}",
                'description' => "انتهت جلسة المجموعة بتدريس المهندس {$session->group?->instructor?->user?->name} دون حفظ كشف الحضور والغياب.",
                'date' => $session->attendance_date?->format('Y-m-d'),
                'branch_name' => $session->tenant?->name ?? '—',
                'record_id' => $session->id,
            ]);
        }

        // 3. Overdue Homework Grading (> 48h)
        $overdueGrading = AssignmentSubmission::withoutGlobalScopes()
            ->with(['assignment.group.instructor.user', 'tenant'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereNotNull('submitted_at')
            ->whereNull('score')
            ->where('submitted_at', '<=', now()->subHours(48))
            ->take(15)
            ->get();

        foreach ($overdueGrading as $sub) {
            $alerts->push([
                'category' => 'academic_compliance',
                'type' => 'overdue_grading',
                'severity' => 'warning',
                'title' => "تأخر تصحيح واجب: {$sub->assignment?->title}",
                'description' => "تم تسليم الواجب منذ أكثر من 48 ساعة بواسطة الطالب ولم ترصد درجته بعد.",
                'date' => $sub->submitted_at?->format('Y-m-d H:i'),
                'branch_name' => $sub->tenant?->name ?? '—',
                'record_id' => $sub->id,
            ]);
        }

        // 4. Published Low Academic Scores (< 50%)
        $lowExamAttempts = ExamAttempt::withoutGlobalScopes()
            ->with(['exam', 'student.user', 'tenant'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereHas('exam', fn($q) => $q->where('status', 'published'))
            ->whereNotNull('score')
            ->where('percentage', '<', 50.00)
            ->take(15)
            ->get();

        foreach ($lowExamAttempts as $attempt) {
            $alerts->push([
                'category' => 'academic_alert',
                'type' => 'low_exam_score',
                'severity' => 'danger',
                'title' => "درجة منخفضة في الامتحان (< 50%): {$attempt->exam?->title}",
                'description' => "حصل الطالب {$attempt->student?->user?->name} على نسبة {$attempt->percentage}% في الامتحان الرسمي المنشور.",
                'date' => $attempt->created_at?->format('Y-m-d'),
                'branch_name' => $attempt->tenant?->name ?? '—',
                'record_id' => $attempt->id,
            ]);
        }

        // 5. Unsettled Canceled Enrollments
        $unsettledCancellations = EnrollmentCancellation::withoutGlobalScopes()
            ->with(['enrollment.student.user', 'tenant'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where(function($q) {
                $q->whereNull('settlement_status')->orWhere('settlement_status', 'pending');
            })
            ->take(15)
            ->get();

        foreach ($unsettledCancellations as $canc) {
            $alerts->push([
                'category' => 'financial_administrative',
                'type' => 'unsettled_cancellation',
                'severity' => 'warning',
                'title' => "اشتراك ملغي بانتظار التسوية المالية",
                'description' => "اشتراك الطالب {$canc->enrollment?->student?->user?->name} أُلغي وبانتظار اعتماد قرار التسوية المالية.",
                'date' => $canc->created_at?->format('Y-m-d'),
                'branch_name' => $canc->tenant?->name ?? '—',
                'record_id' => $canc->id,
            ]);
        }

        // 6. Approved Refunds Awaiting Actual Disbursement
        $pendingRefunds = EnrollmentCancellation::withoutGlobalScopes()
            ->with(['enrollment.student.user', 'tenant'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('settlement_status', 'settled')
            ->where('refundable_amount', '>', 0)
            ->where(function($q) {
                $q->whereNull('refund_status')->orWhere('refund_status', 'pending');
            })
            ->take(15)
            ->get();

        foreach ($pendingRefunds as $pref) {
            $alerts->push([
                'category' => 'financial_vault',
                'type' => 'pending_refund_disbursement',
                'severity' => 'danger',
                'title' => "مبلغ استرداد معتمد بانتظار الصرف من الخزنة: {$pref->refundable_amount} ج.م",
                'description' => "تمت تسوية استرداد الطالب {$pref->enrollment?->student?->user?->name} بمبلغ {$pref->refundable_amount} ج.م وبانتظار تسجيل حركة الصرف الفعلي.",
                'date' => $pref->created_at?->format('Y-m-d'),
                'branch_name' => $pref->tenant?->name ?? '—',
                'record_id' => $pref->id,
            ]);
        }

        // 7. Supervision Observations Awaiting Supervisor Review
        $waitingObs = SupervisoryObservation::withoutGlobalScopes()
            ->with(['instructor.user', 'group', 'tenant'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW)
            ->take(15)
            ->get();

        foreach ($waitingObs as $obs) {
            $alerts->push([
                'category' => 'supervision',
                'type' => 'observation_waiting_review',
                'severity' => 'info',
                'title' => "ملاحظة إشرافية بانتظار اعتماد المشرف: {$obs->title}",
                'description' => "قام المهندس {$obs->instructor?->user?->name} بالرد على الملاحظة وبانتظار مراجعة واعتماد المشرف التربوي.",
                'date' => $obs->instructor_responded_at?->format('Y-m-d H:i') ?? $obs->updated_at?->format('Y-m-d'),
                'branch_name' => $obs->tenant?->name ?? '—',
                'record_id' => $obs->id,
            ]);
        }

        return $alerts;
    }

    public function getManagementAlertsCount(?int $tenantId = null, ?string $asOfDate = null): int
    {
        return $this->getManagementAlerts($tenantId, $asOfDate)->count();
    }

    /**
     * INTERNAL HELPER: Calculate distinct debt breakdown as of date
     */
    protected function calculateStudentDebtBreakdown(string $asOfStr, ?int $tenantId = null): array
    {
        $baseQuery = Installment::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereNotIn('status', ['cancelled', 'waived'])
            ->whereHas('enrollment', fn($q) => $q->where('status', '!=', 'cancelled'))
            ->whereRaw('paid_amount < amount');

        $overdue = (float) (clone $baseQuery)
            ->whereDate('due_date', '<', $asOfStr)
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as rem')
            ->value('rem');

        $dueToday = (float) (clone $baseQuery)
            ->whereDate('due_date', '=', $asOfStr)
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as rem')
            ->value('rem');

        $future = (float) (clone $baseQuery)
            ->whereDate('due_date', '>', $asOfStr)
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as rem')
            ->value('rem');

        return [
            'total_debt' => round($overdue + $dueToday + $future, 2),
            'overdue_debt' => round($overdue, 2),
            'currently_due_debt' => round($dueToday, 2),
            'future_debt' => round($future, 2),
        ];
    }

    /**
     * INTERNAL HELPER: Calculate Cash Movement (Collections vs Disbursements)
     */
    protected function calculateCashMovement(string $startDate, string $endDate, ?int $tenantId = null): array
    {
        $inflows = (float) Payment::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        $expenses = (float) Expense::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'completed')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $salaries = (float) SalaryPayment::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        $refunds = (float) EnrollmentCancellation::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('refund_status', 'refunded')
            ->whereBetween('refunded_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
            ->sum('refunded_amount');

        $totalDisbursements = $expenses + $salaries + $refunds;

        return [
            'total_inflows' => round($inflows, 2),
            'total_disbursements' => round($totalDisbursements, 2),
            'net_movement' => round($inflows - $totalDisbursements, 2),
        ];
    }

    /**
     * INTERNAL HELPER: Calculate academic executive overview
     */
    protected function calculateAcademicExecutiveSummary(string $startDate, string $endDate, ?int $tenantId = null): array
    {
        $completed = SessionSchedule::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'completed')
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        $unrecorded = AttendanceSession::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->whereDoesntHave('records')
            ->count();

        $totalSessions = AttendanceSession::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->count();

        $complianceRate = $totalSessions > 0
            ? round((($totalSessions - $unrecorded) / $totalSessions) * 100, 1)
            : 100.0;

        return [
            'completed_sessions' => $completed,
            'unrecorded_sessions' => $unrecorded,
            'compliance_rate' => $complianceRate,
        ];
    }

    /**
     * INTERNAL HELPER: Calculate supervision summary
     */
    protected function calculateSupervisionExecutiveSummary(?int $tenantId = null): array
    {
        $open = SupervisoryObservation::withoutGlobalScopes()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where(function($q) {
                $q->whereIn('status', [
                    SupervisoryObservation::STATUS_NEW,
                    SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW,
                    'returned',
                ])->orWhereNotNull('returned_at');
            })
            ->count();

        return [
            'open_observations' => $open,
        ];
    }

    /**
     * INTERNAL HELPER: Calculate vault balance as of date
     */
    protected function calculateVaultBalanceAsOf(int $vaultId, Carbon $asOfDate): float
    {
        $vault = Vault::withoutGlobalScopes()->find($vaultId);
        if (!$vault) {
            return 0.0;
        }

        $openingBalance = (float) $vault->opening_balance;
        $dateStr = $asOfDate->toDateString();

        $paymentsBefore = (float) Payment::withoutGlobalScopes()
            ->where('vault_id', $vaultId)
            ->where('status', 'completed')
            ->where('payment_date', '<', $dateStr)
            ->sum('amount');

        $expensesBefore = (float) Expense::withoutGlobalScopes()
            ->where('vault_id', $vaultId)
            ->whereIn('status', ['paid', 'completed'])
            ->where('expense_date', '<', $dateStr)
            ->sum('amount');

        $salariesBefore = (float) SalaryPayment::withoutGlobalScopes()
            ->where('vault_id', $vaultId)
            ->whereIn('status', ['paid', 'completed'])
            ->where('payment_date', '<', $dateStr)
            ->sum('amount');

        $refundsBefore = (float) EnrollmentCancellation::withoutGlobalScopes()
            ->where('refund_vault_id', $vaultId)
            ->where('refund_status', 'refunded')
            ->where('refunded_at', '<', $asOfDate)
            ->sum('refunded_amount');

        $transfersInBefore = (float) VaultTransfer::withoutGlobalScopes()
            ->where('to_vault_id', $vaultId)
            ->where('status', 'completed')
            ->where('transfer_date', '<', $dateStr)
            ->sum('amount');

        $transfersOutBefore = (float) VaultTransfer::withoutGlobalScopes()
            ->where('from_vault_id', $vaultId)
            ->where('status', 'completed')
            ->where('transfer_date', '<', $dateStr)
            ->sum('amount');

        return $openingBalance + $paymentsBefore + $transfersInBefore - ($expensesBefore + $salariesBefore + $refundsBefore + $transfersOutBefore);
    }
}

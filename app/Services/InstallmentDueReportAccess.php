<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class InstallmentDueReportAccess
{
    /**
     * Check if a user can view installment reports.
     */
    public static function canViewReport(?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user || $user->status !== 'active') {
            return false;
        }

        if ($user->is_super_admin || $user->isOwner()) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasRole('branch_manager') || $user->isBranchManager()) {
            return true;
        }

        if ($user->hasRole('accountant')) {
            return true;
        }

        return $user->can('view_reports')
            || $user->can('view_financial_reports')
            || $user->can('view_student_payments')
            || $user->can('view_payments');
    }

    /**
     * Check if a user can collect/record installment payments.
     */
    public static function canCollectPayment(?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user || $user->status !== 'active') {
            return false;
        }

        if ($user->is_super_admin || $user->isOwner()) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasRole('branch_manager') || $user->isBranchManager()) {
            return true;
        }

        if ($user->hasRole('accountant')) {
            return true;
        }

        return $user->can('create_student_payments')
            || $user->can('create_payments')
            || $user->can('collect_payments');
    }

    /**
     * Check if a user can view vault balances in dropdowns/reports.
     */
    public static function canViewVaultBalances(?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user || $user->status !== 'active') {
            return false;
        }

        if ($user->is_super_admin || $user->isOwner()) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasRole('branch_manager') || $user->hasRole('accountant')) {
            return true;
        }

        return $user->can('view_vault_balances');
    }

    /**
     * Abort with 403 if user cannot collect payments.
     */
    public static function assertCanCollectPayment(?User $user = null): void
    {
        $user = $user ?? Auth::user();

        abort_unless(
            static::canCollectPayment($user),
            403,
            'غير مصرح لك بتحصيل المدفوعات وتسجيلها في الخزائن.'
        );
    }

    /**
     * Abort with 403 if user cannot view the reports.
     */
    public static function assertCanViewReport(?User $user = null): void
    {
        $user = $user ?? Auth::user();

        abort_unless(
            static::canViewReport($user),
            403,
            'غير مصرح لك بعرض تقارير الأقساط المستحقة.'
        );
    }
}

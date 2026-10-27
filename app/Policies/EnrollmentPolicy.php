<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Enrollment;
use Illuminate\Auth\Access\HandlesAuthorization;

class EnrollmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        if ($authUser->is_super_admin) {
            return true;
        }

        return $authUser->can('ViewAny:Enrollment')
            || $authUser->can('view_any_enrollments')
            || $authUser->hasRole(['super_admin', 'branch_manager', 'admin']);
    }

    public function view(AuthUser $authUser, Enrollment $enrollment): bool
    {
        if ($authUser->is_super_admin) {
            return true;
        }

        if (!empty($authUser->tenant_id) && !empty($enrollment->tenant_id) && (int) $authUser->tenant_id !== (int) $enrollment->tenant_id) {
            return false;
        }

        return $authUser->can('View:Enrollment')
            || $authUser->can('view_enrollments')
            || $authUser->hasRole(['super_admin', 'branch_manager', 'admin']);
    }

    public function create(AuthUser $authUser): bool
    {
        if ($authUser->is_super_admin) {
            return true;
        }

        return $authUser->can('Create:Enrollment')
            || $authUser->can('create_enrollments')
            || $authUser->hasRole(['super_admin', 'branch_manager', 'admin']);
    }

    public function update(AuthUser $authUser, Enrollment $enrollment): bool
    {
        if ($authUser->is_super_admin) {
            return true;
        }

        if (!empty($authUser->tenant_id) && !empty($enrollment->tenant_id) && (int) $authUser->tenant_id !== (int) $enrollment->tenant_id) {
            return false;
        }

        return $authUser->can('Update:Enrollment')
            || $authUser->can('update_enrollments')
            || $authUser->hasRole(['super_admin', 'branch_manager', 'admin']);
    }

    public function delete(AuthUser $authUser, Enrollment $enrollment): bool
    {
        return $authUser->can('Delete:Enrollment');
    }

    public function restore(AuthUser $authUser, Enrollment $enrollment): bool
    {
        return $authUser->can('Restore:Enrollment');
    }

    public function forceDelete(AuthUser $authUser, Enrollment $enrollment): bool
    {
        return $authUser->can('ForceDelete:Enrollment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Enrollment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Enrollment');
    }

    public function replicate(AuthUser $authUser, Enrollment $enrollment): bool
    {
        return $authUser->can('Replicate:Enrollment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Enrollment');
    }

    /**
     * Determine whether the user can cancel an enrollment/subscription.
     * No manager approval is needed if the employee has cancellation permission.
     */
    public function cancel(AuthUser $authUser, Enrollment $enrollment): bool
    {
        if ($authUser->is_super_admin) {
            return true;
        }

        // التحقق من نطاق الفرع / الـ tenant
        if (!empty($authUser->tenant_id) && !empty($enrollment->tenant_id) && (int) $authUser->tenant_id !== (int) $enrollment->tenant_id) {
            return false;
        }

        return $authUser->can('Cancel:Enrollment')
            || $authUser->can('cancel_enrollments')
            || $authUser->can('cancel_subscription')
            || $authUser->hasRole(['super_admin', 'branch_manager', 'admin', 'secretary']);
    }

    /**
     * Determine whether the user can perform financial settlements (waive debt, session-based settlement, custom adjustments).
     */
    public function settle(AuthUser $authUser, Enrollment $enrollment): bool
    {
        if ($authUser->is_super_admin) {
            return true;
        }

        if (!empty($authUser->tenant_id) && !empty($enrollment->tenant_id) && (int) $authUser->tenant_id !== (int) $enrollment->tenant_id) {
            return false;
        }

        return $authUser->can('Settle:Enrollment')
            || $authUser->can('settle_enrollments')
            || $authUser->can('settle_subscription')
            || $authUser->hasRole(['super_admin', 'branch_manager', 'admin']);
    }

    /**
     * Determine whether the user can disburse funds / execute actual refunds from vaults.
     */
    public function executeRefund(AuthUser $authUser, Enrollment $enrollment): bool
    {
        if ($authUser->is_super_admin) {
            return true;
        }

        if (!empty($authUser->tenant_id) && !empty($enrollment->tenant_id) && (int) $authUser->tenant_id !== (int) $enrollment->tenant_id) {
            return false;
        }

        return $authUser->can('Refund:Payment')
            || $authUser->can('refund_subscription')
            || $authUser->can('execute_refunds')
            || $authUser->hasRole(['super_admin', 'branch_manager']);
    }

    /**
     * Determine whether the user can transfer the enrollment to another group.
     * Permission to edit an enrollment does NOT grant permission to transfer group.
     * Requires dedicated 'transfer_student_group' permission.
     */
    public function transferGroup(AuthUser $authUser, Enrollment $enrollment): bool
    {
        // Branch isolation check: user cannot transfer an enrollment belonging to another branch
        if (!empty($authUser->tenant_id) && !empty($enrollment->tenant_id) && (int) $authUser->tenant_id !== (int) $enrollment->tenant_id) {
            return false;
        }

        // Must possess the dedicated transfer_student_group permission
        return $authUser->can('transfer_student_group');
    }
}
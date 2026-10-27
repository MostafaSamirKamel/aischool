<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SalaryPayment;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalaryPaymentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SalaryPayment');
    }

    public function view(AuthUser $authUser, SalaryPayment $salaryPayment): bool
    {
        return $authUser->can('View:SalaryPayment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SalaryPayment');
    }

    public function update(AuthUser $authUser, SalaryPayment $salaryPayment): bool
    {
        return $authUser->can('Update:SalaryPayment');
    }

    public function delete(AuthUser $authUser, SalaryPayment $salaryPayment): bool
    {
        return $authUser->can('Delete:SalaryPayment');
    }

    public function restore(AuthUser $authUser, SalaryPayment $salaryPayment): bool
    {
        return $authUser->can('Restore:SalaryPayment');
    }

    public function forceDelete(AuthUser $authUser, SalaryPayment $salaryPayment): bool
    {
        return $authUser->can('ForceDelete:SalaryPayment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SalaryPayment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SalaryPayment');
    }

    public function replicate(AuthUser $authUser, SalaryPayment $salaryPayment): bool
    {
        return $authUser->can('Replicate:SalaryPayment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SalaryPayment');
    }

}
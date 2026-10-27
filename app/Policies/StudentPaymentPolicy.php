<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\StudentPayment;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPaymentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StudentPayment');
    }

    public function view(AuthUser $authUser, StudentPayment $studentPayment): bool
    {
        return $authUser->can('View:StudentPayment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StudentPayment');
    }

    public function update(AuthUser $authUser, StudentPayment $studentPayment): bool
    {
        return $authUser->can('Update:StudentPayment');
    }

    public function delete(AuthUser $authUser, StudentPayment $studentPayment): bool
    {
        return $authUser->can('Delete:StudentPayment');
    }

    public function restore(AuthUser $authUser, StudentPayment $studentPayment): bool
    {
        return $authUser->can('Restore:StudentPayment');
    }

    public function forceDelete(AuthUser $authUser, StudentPayment $studentPayment): bool
    {
        return $authUser->can('ForceDelete:StudentPayment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StudentPayment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StudentPayment');
    }

    public function replicate(AuthUser $authUser, StudentPayment $studentPayment): bool
    {
        return $authUser->can('Replicate:StudentPayment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StudentPayment');
    }

}
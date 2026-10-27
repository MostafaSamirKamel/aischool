<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InstructorPayment;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstructorPaymentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InstructorPayment')
            || (bool) $authUser->instructor;
    }

    public function view(AuthUser $authUser, InstructorPayment $instructorPayment): bool
    {
        if ($authUser->can('View:InstructorPayment')) {
            return true;
        }

        if ($authUser->instructor) {
            return (int) $instructorPayment->instructor_id === (int) $authUser->instructor->id;
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InstructorPayment');
    }

    public function update(AuthUser $authUser, InstructorPayment $instructorPayment): bool
    {
        return $authUser->can('Update:InstructorPayment');
    }

    public function delete(AuthUser $authUser, InstructorPayment $instructorPayment): bool
    {
        return $authUser->can('Delete:InstructorPayment');
    }

    public function restore(AuthUser $authUser, InstructorPayment $instructorPayment): bool
    {
        return $authUser->can('Restore:InstructorPayment');
    }

    public function forceDelete(AuthUser $authUser, InstructorPayment $instructorPayment): bool
    {
        return $authUser->can('ForceDelete:InstructorPayment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InstructorPayment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InstructorPayment');
    }

    public function replicate(AuthUser $authUser, InstructorPayment $instructorPayment): bool
    {
        return $authUser->can('Replicate:InstructorPayment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InstructorPayment');
    }

}
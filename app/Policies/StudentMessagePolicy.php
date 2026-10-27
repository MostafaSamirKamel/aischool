<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\StudentMessage;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentMessagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StudentMessage')
            || (bool) $authUser->instructor
            || $authUser->hasRole('parent')
            || $authUser->hasRole('student');
    }

    public function view(AuthUser $authUser, StudentMessage $studentMessage): bool
    {
        return $authUser->can('View:StudentMessage')
            || (bool) $authUser->instructor;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StudentMessage')
            || (bool) $authUser->instructor;
    }

    public function update(AuthUser $authUser, StudentMessage $studentMessage): bool
    {
        return $authUser->can('Update:StudentMessage');
    }

    public function delete(AuthUser $authUser, StudentMessage $studentMessage): bool
    {
        return $authUser->can('Delete:StudentMessage');
    }

    public function restore(AuthUser $authUser, StudentMessage $studentMessage): bool
    {
        return $authUser->can('Restore:StudentMessage');
    }

    public function forceDelete(AuthUser $authUser, StudentMessage $studentMessage): bool
    {
        return $authUser->can('ForceDelete:StudentMessage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StudentMessage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StudentMessage');
    }

    public function replicate(AuthUser $authUser, StudentMessage $studentMessage): bool
    {
        return $authUser->can('Replicate:StudentMessage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StudentMessage');
    }

}
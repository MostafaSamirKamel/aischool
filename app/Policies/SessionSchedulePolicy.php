<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SessionSchedule;
use Illuminate\Auth\Access\HandlesAuthorization;

class SessionSchedulePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SessionSchedule')
            || (bool) $authUser->instructor;
    }

    public function view(AuthUser $authUser, SessionSchedule $sessionSchedule): bool
    {
        return $authUser->can('View:SessionSchedule')
            || (bool) $authUser->instructor;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SessionSchedule');
    }

    public function update(AuthUser $authUser, SessionSchedule $sessionSchedule): bool
    {
        return $authUser->can('Update:SessionSchedule');
    }

    public function delete(AuthUser $authUser, SessionSchedule $sessionSchedule): bool
    {
        return $authUser->can('Delete:SessionSchedule');
    }

    public function restore(AuthUser $authUser, SessionSchedule $sessionSchedule): bool
    {
        return $authUser->can('Restore:SessionSchedule');
    }

    public function forceDelete(AuthUser $authUser, SessionSchedule $sessionSchedule): bool
    {
        return $authUser->can('ForceDelete:SessionSchedule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SessionSchedule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SessionSchedule');
    }

    public function replicate(AuthUser $authUser, SessionSchedule $sessionSchedule): bool
    {
        return $authUser->can('Replicate:SessionSchedule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SessionSchedule');
    }

}
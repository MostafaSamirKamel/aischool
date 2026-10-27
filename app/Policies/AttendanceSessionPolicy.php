<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AttendanceSession;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendanceSessionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AttendanceSession')
            || (bool) $authUser->instructor
            || (bool) $authUser->supervisor;
    }

    public function view(AuthUser $authUser, AttendanceSession $attendanceSession): bool
    {
        if ($authUser->can('View:AttendanceSession')) {
            return true;
        }

        if ($authUser->instructor) {
            return (int) $attendanceSession->group?->instructor_id === (int) $authUser->instructor->id;
        }

        if ($authUser->supervisor) {
            return (int) $attendanceSession->group?->supervisor_id === (int) $authUser->supervisor->id;
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AttendanceSession')
            || (bool) $authUser->instructor
            || (bool) $authUser->supervisor;
    }

    public function update(AuthUser $authUser, AttendanceSession $attendanceSession): bool
    {
        if ($authUser->can('Update:AttendanceSession')) {
            return true;
        }

        if ($authUser->instructor) {
            return (int) $attendanceSession->group?->instructor_id === (int) $authUser->instructor->id;
        }

        if ($authUser->supervisor) {
            return (int) $attendanceSession->group?->supervisor_id === (int) $authUser->supervisor->id;
        }

        return false;
    }

    public function delete(AuthUser $authUser, AttendanceSession $attendanceSession): bool
    {
        return $authUser->can('Delete:AttendanceSession');
    }

    public function restore(AuthUser $authUser, AttendanceSession $attendanceSession): bool
    {
        return $authUser->can('Restore:AttendanceSession');
    }

    public function forceDelete(AuthUser $authUser, AttendanceSession $attendanceSession): bool
    {
        return $authUser->can('ForceDelete:AttendanceSession');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AttendanceSession');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AttendanceSession');
    }

    public function replicate(AuthUser $authUser, AttendanceSession $attendanceSession): bool
    {
        return $authUser->can('Replicate:AttendanceSession');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AttendanceSession');
    }

}
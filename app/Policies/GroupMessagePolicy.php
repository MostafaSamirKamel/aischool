<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GroupMessage;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupMessagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GroupMessage')
            || (bool) $authUser->instructor;
    }

    public function view(AuthUser $authUser, GroupMessage $groupMessage): bool
    {
        return $authUser->can('View:GroupMessage')
            || (bool) $authUser->instructor;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GroupMessage')
            || (bool) $authUser->instructor;
    }

    public function update(AuthUser $authUser, GroupMessage $groupMessage): bool
    {
        return $authUser->can('Update:GroupMessage');
    }

    public function delete(AuthUser $authUser, GroupMessage $groupMessage): bool
    {
        return $authUser->can('Delete:GroupMessage');
    }

    public function restore(AuthUser $authUser, GroupMessage $groupMessage): bool
    {
        return $authUser->can('Restore:GroupMessage');
    }

    public function forceDelete(AuthUser $authUser, GroupMessage $groupMessage): bool
    {
        return $authUser->can('ForceDelete:GroupMessage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GroupMessage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GroupMessage');
    }

    public function replicate(AuthUser $authUser, GroupMessage $groupMessage): bool
    {
        return $authUser->can('Replicate:GroupMessage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GroupMessage');
    }

}
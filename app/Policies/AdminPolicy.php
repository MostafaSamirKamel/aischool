<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Admin');
    }

    public function view(AuthUser $authUser, Admin $admin): bool
    {
        return $authUser->can('View:Admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Admin');
    }

    public function update(AuthUser $authUser, Admin $admin): bool
    {
        return $authUser->can('Update:Admin');
    }

    public function delete(AuthUser $authUser, Admin $admin): bool
    {
        return $authUser->can('Delete:Admin');
    }

    public function restore(AuthUser $authUser, Admin $admin): bool
    {
        return $authUser->can('Restore:Admin');
    }

    public function forceDelete(AuthUser $authUser, Admin $admin): bool
    {
        return $authUser->can('ForceDelete:Admin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Admin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Admin');
    }

    public function replicate(AuthUser $authUser, Admin $admin): bool
    {
        return $authUser->can('Replicate:Admin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Admin');
    }

}
<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Occupation;
use Illuminate\Auth\Access\HandlesAuthorization;

class OccupationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Occupation');
    }

    public function view(AuthUser $authUser, Occupation $occupation): bool
    {
        return $authUser->can('View:Occupation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Occupation');
    }

    public function update(AuthUser $authUser, Occupation $occupation): bool
    {
        return $authUser->can('Update:Occupation');
    }

    public function delete(AuthUser $authUser, Occupation $occupation): bool
    {
        return $authUser->can('Delete:Occupation');
    }

    public function restore(AuthUser $authUser, Occupation $occupation): bool
    {
        return $authUser->can('Restore:Occupation');
    }

    public function forceDelete(AuthUser $authUser, Occupation $occupation): bool
    {
        return $authUser->can('ForceDelete:Occupation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Occupation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Occupation');
    }

    public function replicate(AuthUser $authUser, Occupation $occupation): bool
    {
        return $authUser->can('Replicate:Occupation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Occupation');
    }

}
<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Track;
use Illuminate\Auth\Access\HandlesAuthorization;

class TrackPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Track');
    }

    public function view(AuthUser $authUser, Track $track): bool
    {
        return $authUser->can('View:Track');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Track');
    }

    public function update(AuthUser $authUser, Track $track): bool
    {
        return $authUser->can('Update:Track');
    }

    public function delete(AuthUser $authUser, Track $track): bool
    {
        return $authUser->can('Delete:Track');
    }

    public function restore(AuthUser $authUser, Track $track): bool
    {
        return $authUser->can('Restore:Track');
    }

    public function forceDelete(AuthUser $authUser, Track $track): bool
    {
        return $authUser->can('ForceDelete:Track');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Track');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Track');
    }

    public function replicate(AuthUser $authUser, Track $track): bool
    {
        return $authUser->can('Replicate:Track');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Track');
    }

}
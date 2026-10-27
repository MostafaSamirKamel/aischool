<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\VaultTransfer;
use Illuminate\Auth\Access\HandlesAuthorization;

class VaultTransferPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VaultTransfer');
    }

    public function view(AuthUser $authUser, VaultTransfer $vaultTransfer): bool
    {
        return $authUser->can('View:VaultTransfer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VaultTransfer');
    }

    public function update(AuthUser $authUser, VaultTransfer $vaultTransfer): bool
    {
        return $authUser->can('Update:VaultTransfer');
    }

    public function delete(AuthUser $authUser, VaultTransfer $vaultTransfer): bool
    {
        return $authUser->can('Delete:VaultTransfer');
    }

    public function restore(AuthUser $authUser, VaultTransfer $vaultTransfer): bool
    {
        return $authUser->can('Restore:VaultTransfer');
    }

    public function forceDelete(AuthUser $authUser, VaultTransfer $vaultTransfer): bool
    {
        return $authUser->can('ForceDelete:VaultTransfer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VaultTransfer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VaultTransfer');
    }

    public function replicate(AuthUser $authUser, VaultTransfer $vaultTransfer): bool
    {
        return $authUser->can('Replicate:VaultTransfer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VaultTransfer');
    }

}
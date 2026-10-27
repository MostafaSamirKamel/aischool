<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\VaultReconciliation;
use Illuminate\Auth\Access\HandlesAuthorization;

class VaultReconciliationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VaultReconciliation');
    }

    public function view(AuthUser $authUser, VaultReconciliation $vaultReconciliation): bool
    {
        return $authUser->can('View:VaultReconciliation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VaultReconciliation');
    }

    public function update(AuthUser $authUser, VaultReconciliation $vaultReconciliation): bool
    {
        return $authUser->can('Update:VaultReconciliation');
    }

    public function delete(AuthUser $authUser, VaultReconciliation $vaultReconciliation): bool
    {
        return $authUser->can('Delete:VaultReconciliation');
    }

    public function restore(AuthUser $authUser, VaultReconciliation $vaultReconciliation): bool
    {
        return $authUser->can('Restore:VaultReconciliation');
    }

    public function forceDelete(AuthUser $authUser, VaultReconciliation $vaultReconciliation): bool
    {
        return $authUser->can('ForceDelete:VaultReconciliation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VaultReconciliation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VaultReconciliation');
    }

    public function replicate(AuthUser $authUser, VaultReconciliation $vaultReconciliation): bool
    {
        return $authUser->can('Replicate:VaultReconciliation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VaultReconciliation');
    }

}
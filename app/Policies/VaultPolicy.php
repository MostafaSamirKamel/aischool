<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Vault;
use Illuminate\Auth\Access\HandlesAuthorization;

class VaultPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Vault');
    }

    public function view(AuthUser $authUser, Vault $vault): bool
    {
        return $authUser->can('View:Vault');
    }

    public function viewBalances(AuthUser $authUser, ?Vault $vault = null): bool
    {
        return $authUser->can('view_vault_balances') || $authUser->can('ViewBalances:Vault');
    }

    public function viewTransactions(AuthUser $authUser, ?Vault $vault = null): bool
    {
        return $authUser->can('view_vault_transactions') || $authUser->can('ViewTransactions:Vault');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Vault');
    }

    public function update(AuthUser $authUser, Vault $vault): bool
    {
        return $authUser->can('Update:Vault');
    }

    public function delete(AuthUser $authUser, Vault $vault): bool
    {
        return $authUser->can('Delete:Vault');
    }

    public function restore(AuthUser $authUser, Vault $vault): bool
    {
        return $authUser->can('Restore:Vault');
    }

    public function forceDelete(AuthUser $authUser, Vault $vault): bool
    {
        return $authUser->can('ForceDelete:Vault');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Vault');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Vault');
    }

    public function replicate(AuthUser $authUser, Vault $vault): bool
    {
        return $authUser->can('Replicate:Vault');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Vault');
    }

}
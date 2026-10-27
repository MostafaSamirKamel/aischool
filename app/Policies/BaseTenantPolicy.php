<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Base Policy for Tenant Resources
 * Super Admin can only view, not create/update/delete
 */
abstract class BaseTenantPolicy
{
    use HandlesAuthorization;

    /**
     * Check if user can create records
     * Super Admin cannot create in tenant resources
     */
    protected function canCreate(AuthUser $authUser, string $permission): bool
    {
        if ($authUser->is_super_admin) {
            return false;
        }
        return $authUser->can($permission);
    }

    /**
     * Check if user can update records
     * Super Admin cannot update in tenant resources
     */
    protected function canUpdate(AuthUser $authUser, string $permission): bool
    {
        if ($authUser->is_super_admin) {
            return false;
        }
        return $authUser->can($permission);
    }

    /**
     * Check if user can delete records
     * Super Admin cannot delete in tenant resources
     */
    protected function canDelete(AuthUser $authUser, string $permission): bool
    {
        if ($authUser->is_super_admin) {
            return false;
        }
        return $authUser->can($permission);
    }

    /**
     * Check if user can restore records
     * Super Admin cannot restore in tenant resources
     */
    protected function canRestore(AuthUser $authUser, string $permission): bool
    {
        if ($authUser->is_super_admin) {
            return false;
        }
        return $authUser->can($permission);
    }

    /**
     * Check if user can force delete records
     * Super Admin cannot force delete in tenant resources
     */
    protected function canForceDelete(AuthUser $authUser, string $permission): bool
    {
        if ($authUser->is_super_admin) {
            return false;
        }
        return $authUser->can($permission);
    }
}
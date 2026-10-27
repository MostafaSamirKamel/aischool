<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SmsConfig;
use Illuminate\Auth\Access\HandlesAuthorization;

class SmsConfigPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SmsConfig');
    }

    public function view(AuthUser $authUser, SmsConfig $smsConfig): bool
    {
        return $authUser->can('View:SmsConfig');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SmsConfig');
    }

    public function update(AuthUser $authUser, SmsConfig $smsConfig): bool
    {
        return $authUser->can('Update:SmsConfig');
    }

    public function delete(AuthUser $authUser, SmsConfig $smsConfig): bool
    {
        return $authUser->can('Delete:SmsConfig');
    }

    public function restore(AuthUser $authUser, SmsConfig $smsConfig): bool
    {
        return $authUser->can('Restore:SmsConfig');
    }

    public function forceDelete(AuthUser $authUser, SmsConfig $smsConfig): bool
    {
        return $authUser->can('ForceDelete:SmsConfig');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SmsConfig');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SmsConfig');
    }

    public function replicate(AuthUser $authUser, SmsConfig $smsConfig): bool
    {
        return $authUser->can('Replicate:SmsConfig');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SmsConfig');
    }

}
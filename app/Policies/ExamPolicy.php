<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Exam;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Exam')
            || (bool) $authUser->instructor;
    }

    public function view(AuthUser $authUser, Exam $exam): bool
    {
        if ($authUser->can('View:Exam')) {
            return true;
        }

        if ($authUser->instructor) {
            return (int) $exam->instructor_id === (int) $authUser->instructor->id;
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Exam')
            || (bool) $authUser->instructor;
    }

    public function update(AuthUser $authUser, Exam $exam): bool
    {
        if ($authUser->can('Update:Exam')) {
            return true;
        }

        if ($authUser->instructor) {
            return (int) $exam->instructor_id === (int) $authUser->instructor->id;
        }

        return false;
    }

    public function delete(AuthUser $authUser, Exam $exam): bool
    {
        return $authUser->can('Delete:Exam');
    }

    public function restore(AuthUser $authUser, Exam $exam): bool
    {
        return $authUser->can('Restore:Exam');
    }

    public function forceDelete(AuthUser $authUser, Exam $exam): bool
    {
        return $authUser->can('ForceDelete:Exam');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Exam');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Exam');
    }

    public function replicate(AuthUser $authUser, Exam $exam): bool
    {
        return $authUser->can('Replicate:Exam');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Exam');
    }

}
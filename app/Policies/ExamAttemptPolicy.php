<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ExamAttempt;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamAttemptPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ExamAttempt')
            || (bool) $authUser->instructor
            || (bool) $authUser->student;
    }

    public function view(AuthUser $authUser, ExamAttempt $examAttempt): bool
    {
        if ($authUser->can('View:ExamAttempt')) {
            return true;
        }

        if ($authUser->instructor) {
            return (int) $examAttempt->exam?->instructor_id === (int) $authUser->instructor->id;
        }

        if ($authUser->student) {
            return (int) $examAttempt->student_id === (int) $authUser->student->id;
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ExamAttempt');
    }

    public function update(AuthUser $authUser, ExamAttempt $examAttempt): bool
    {
        return $authUser->can('Update:ExamAttempt');
    }

    public function delete(AuthUser $authUser, ExamAttempt $examAttempt): bool
    {
        return $authUser->can('Delete:ExamAttempt');
    }

    public function restore(AuthUser $authUser, ExamAttempt $examAttempt): bool
    {
        return $authUser->can('Restore:ExamAttempt');
    }

    public function forceDelete(AuthUser $authUser, ExamAttempt $examAttempt): bool
    {
        return $authUser->can('ForceDelete:ExamAttempt');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ExamAttempt');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ExamAttempt');
    }

    public function replicate(AuthUser $authUser, ExamAttempt $examAttempt): bool
    {
        return $authUser->can('Replicate:ExamAttempt');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ExamAttempt');
    }

}
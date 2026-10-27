<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AssignmentSubmission;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssignmentSubmissionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AssignmentSubmission')
            || (bool) $authUser->instructor
            || (bool) $authUser->student;
    }

    public function view(AuthUser $authUser, AssignmentSubmission $assignmentSubmission): bool
    {
        if ($authUser->can('View:AssignmentSubmission')) {
            return true;
        }

        if ($authUser->instructor) {
            return (int) $assignmentSubmission->assignment?->instructor_id === (int) $authUser->instructor->id;
        }

        if ($authUser->student) {
            return (int) $assignmentSubmission->student_id === (int) $authUser->student->id;
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AssignmentSubmission')
            || (bool) $authUser->student;
    }

    public function update(AuthUser $authUser, AssignmentSubmission $assignmentSubmission): bool
    {
        if ($authUser->can('Update:AssignmentSubmission')) {
            return true;
        }

        if ($authUser->instructor) {
            return (int) $assignmentSubmission->assignment?->instructor_id === (int) $authUser->instructor->id;
        }

        if ($authUser->student) {
            return (int) $assignmentSubmission->student_id === (int) $authUser->student->id;
        }

        return false;
    }

    public function delete(AuthUser $authUser, AssignmentSubmission $assignmentSubmission): bool
    {
        return $authUser->can('Delete:AssignmentSubmission');
    }

    public function restore(AuthUser $authUser, AssignmentSubmission $assignmentSubmission): bool
    {
        return $authUser->can('Restore:AssignmentSubmission');
    }

    public function forceDelete(AuthUser $authUser, AssignmentSubmission $assignmentSubmission): bool
    {
        return $authUser->can('ForceDelete:AssignmentSubmission');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AssignmentSubmission');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AssignmentSubmission');
    }

    public function replicate(AuthUser $authUser, AssignmentSubmission $assignmentSubmission): bool
    {
        return $authUser->can('Replicate:AssignmentSubmission');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AssignmentSubmission');
    }

}
<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InstructorReview;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstructorReviewPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InstructorReview')
            || (bool) $authUser->instructor
            || (bool) $authUser->student;
    }

    public function view(AuthUser $authUser, InstructorReview $instructorReview): bool
    {
        if ($authUser->can('View:InstructorReview')) {
            return true;
        }

        if ($authUser->instructor) {
            return (int) $instructorReview->instructor_id === (int) $authUser->instructor->id;
        }

        if ($authUser->student) {
            return (int) $instructorReview->student_id === (int) $authUser->student->id;
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InstructorReview')
            || (bool) $authUser->student;
    }

    public function update(AuthUser $authUser, InstructorReview $instructorReview): bool
    {
        return $authUser->can('Update:InstructorReview');
    }

    public function delete(AuthUser $authUser, InstructorReview $instructorReview): bool
    {
        return $authUser->can('Delete:InstructorReview');
    }

    public function restore(AuthUser $authUser, InstructorReview $instructorReview): bool
    {
        return $authUser->can('Restore:InstructorReview');
    }

    public function forceDelete(AuthUser $authUser, InstructorReview $instructorReview): bool
    {
        return $authUser->can('ForceDelete:InstructorReview');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InstructorReview');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InstructorReview');
    }

    public function replicate(AuthUser $authUser, InstructorReview $instructorReview): bool
    {
        return $authUser->can('Replicate:InstructorReview');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InstructorReview');
    }

}
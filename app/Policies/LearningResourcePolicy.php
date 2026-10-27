<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LearningResource;
use Illuminate\Auth\Access\HandlesAuthorization;

class LearningResourcePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LearningResource')
            || (bool) $authUser->instructor
            || (bool) $authUser->student;
    }

    public function view(AuthUser $authUser, LearningResource $learningResource): bool
    {
        if ($authUser->can('View:LearningResource')) {
            return true;
        }

        if ($authUser->instructor) {
            return (int) $learningResource->instructor_id === (int) $authUser->instructor->id;
        }

        if ($authUser->student) {
            return $this->viewAsStudent($authUser, $learningResource);
        }

        return false;
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LearningResource')
            || (bool) $authUser->instructor;
    }

    public function update(AuthUser $authUser, LearningResource $learningResource): bool
    {
        if ($authUser->can('Update:LearningResource')) {
            return true;
        }

        if ($authUser->instructor) {
            return (int) $learningResource->instructor_id === (int) $authUser->instructor->id;
        }

        return false;
    }

    public function delete(AuthUser $authUser, LearningResource $learningResource): bool
    {
        if ($authUser->can('Delete:LearningResource')) {
            return true;
        }

        if ($authUser->instructor) {
            return (int) $learningResource->instructor_id === (int) $authUser->instructor->id;
        }

        return false;
    }

    public function restore(AuthUser $authUser, LearningResource $learningResource): bool
    {
        return $authUser->can('Restore:LearningResource');
    }

    public function forceDelete(AuthUser $authUser, LearningResource $learningResource): bool
    {
        return $authUser->can('ForceDelete:LearningResource');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LearningResource');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LearningResource');
    }

    public function replicate(AuthUser $authUser, LearningResource $learningResource): bool
    {
        return $authUser->can('Replicate:LearningResource');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LearningResource');
    }

    /**
     * Determine if student can view this learning resource
     */
    public function viewAsStudent(AuthUser $authUser, LearningResource $resource): bool
    {
        // Get student record
        $student = $authUser->student;
        if (!$student) {
            return false;
        }

        // Check if resource is active
        if (!$resource->is_active) {
            return false;
        }

        // Check tenant match
        if ($resource->tenant_id !== $student->tenant_id) {
            return false;
        }

        // Resource without course mapping can only be seen if explicitly public for tenant
        if (!$resource->course_id) {
            return $resource->visibility === 'all';
        }

        // Validate the student has this course through enrollment_courses
        $studentCourseQuery = \App\Models\EnrollmentCourse::query()
            ->where('course_id', $resource->course_id)
            ->whereIn('status', ['active', 'completed'])
            ->whereHas('enrollment', function ($query) use ($student) {
                $query->where('student_id', $student->id)
                    ->whereIn('status', ['active', 'completed']);
            });

        if (!$studentCourseQuery->exists()) {
            return false;
        }

        // Public resources (visibility: all) for enrolled students in this course
        if ($resource->visibility === 'all') {
            return true;
        }

        // Course-level visibility
        if ($resource->visibility === 'course') {
            return true;
        }

        // Group-level visibility
        if ($resource->visibility === 'group' && $resource->group_id) {
            return (clone $studentCourseQuery)
                ->where('group_id', $resource->group_id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine if student can download this learning resource
     */
    public function downloadAsStudent(AuthUser $authUser, LearningResource $resource): bool
    {
        // First check if they can view it
        if (!$this->viewAsStudent($authUser, $resource)) {
            return false;
        }

        // Check if download is allowed
        return $resource->is_downloadable;
    }
}
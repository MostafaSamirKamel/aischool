<?php

namespace App\Observers;

use App\Models\AttendanceSession;
use App\Models\EnrollmentCourse;
use App\Models\InstructorPayment;

class AttendanceSessionObserver
{
    /**
     * Handle the AttendanceSession "created" event.
     * 
     * When a new attendance session is created, increment the session counter
     * for all students enrolled in that course/group and check for completion.
     */
    public function created(AttendanceSession $session): void
    {
        $this->incrementCourses($session->group_id, $session->course_id, $session->id);
        $this->updateInstructorPayment($session);
    }

    /**
     * Handle the AttendanceSession "updated" event.
     * 
     * If group/course changes, move the session count accordingly.
     */
    public function updated(AttendanceSession $session): void
    {
        $originalGroupId = $session->getOriginal('group_id');
        $originalCourseId = $session->getOriginal('course_id');

        if ($originalGroupId == $session->group_id && $originalCourseId == $session->course_id) {
            return;
        }

        $this->decrementCourses($originalGroupId, $originalCourseId, $session->id);
        $this->incrementCourses($session->group_id, $session->course_id, $session->id);
        $this->updateInstructorPayment($session);
    }

    /**
     * Handle the AttendanceSession "deleted" event.
     * 
     * When an attendance session is deleted, decrement the session counter
     * for all students enrolled in that course/group.
     */
    public function deleted(AttendanceSession $session): void
    {
        $this->decrementCourses($session->group_id, $session->course_id, $session->id);
        $this->updateInstructorPayment($session, true);
    }

    /**
     * Handle the AttendanceSession "restored" event.
     */
    public function restored(AttendanceSession $session): void
    {
        $this->incrementCourses($session->group_id, $session->course_id, $session->id);
        $this->updateInstructorPayment($session);
    }

    private function incrementCourses(?int $groupId, ?int $courseId, int $sessionId): void
    {
        if (!$groupId || !$courseId) {
            return;
        }

        $enrollmentCourses = EnrollmentCourse::where('group_id', $groupId)
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->get();

        foreach ($enrollmentCourses as $enrollmentCourse) {
            /** @var EnrollmentCourse $enrollmentCourse */
            $enrollmentCourse->incrementSession();

            \Log::info('Session incremented for enrollment course', [
                'enrollment_id' => $enrollmentCourse->enrollment_id,
                'course_id' => $enrollmentCourse->course_id,
                'current_session' => $enrollmentCourse->current_session,
                'total_sessions' => $enrollmentCourse->total_sessions,
                'status' => $enrollmentCourse->status,
                'attendance_session_id' => $sessionId,
            ]);
        }
    }

    private function decrementCourses(?int $groupId, ?int $courseId, int $sessionId): void
    {
        if (!$groupId || !$courseId) {
            return;
        }

        $enrollmentCourses = EnrollmentCourse::where('group_id', $groupId)
            ->where('course_id', $courseId)
            ->get();

        foreach ($enrollmentCourses as $enrollmentCourse) {
            /** @var EnrollmentCourse $enrollmentCourse */
            if ($enrollmentCourse->current_session > 0) {
                $enrollmentCourse->current_session--;

                if (
                    $enrollmentCourse->status === 'completed' &&
                    $enrollmentCourse->current_session < $enrollmentCourse->total_sessions) {
                    $enrollmentCourse->status = 'active';
                }

                $enrollmentCourse->save();

                \Log::info('Session decremented for enrollment course', [
                    'enrollment_id' => $enrollmentCourse->enrollment_id,
                    'course_id' => $enrollmentCourse->course_id,
                    'current_session' => $enrollmentCourse->current_session,
                    'total_sessions' => $enrollmentCourse->total_sessions,
                    'status' => $enrollmentCourse->status,
                    'attendance_session_id' => $sessionId,
                ]);
            }
        }
    }

    /**
     * Update or create instructor payment for the session's month
     */
    private function updateInstructorPayment(AttendanceSession $session, bool $isDeleted = false): void
    {
        InstructorPayment::updateOrCreateForSession($session);
    }
}

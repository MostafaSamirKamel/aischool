<?php

namespace App\Observers;

use App\Models\AttendanceRecord;
use App\Models\EnrollmentCourse;

class AttendanceRecordObserver
{
    /**
     * Handle the AttendanceRecord "created" event.
     */
    public function created(AttendanceRecord $record): void
    {
        $this->syncEnrollmentCourseProgress($record);
    }

    /**
     * Handle the AttendanceRecord "updated" event.
     */
    public function updated(AttendanceRecord $record): void
    {
        $this->syncEnrollmentCourseProgress($record);
    }

    /**
     * Handle the AttendanceRecord "deleted" event.
     */
    public function deleted(AttendanceRecord $record): void
    {
        $this->syncEnrollmentCourseProgress($record);
    }

    private function syncEnrollmentCourseProgress(AttendanceRecord $record): void
    {
        $session = $record->session;

        if (!$session || !$session->group_id || !$session->course_id || !$record->student_id) {
            return;
        }

        $enrollmentCourse = EnrollmentCourse::where('course_id', $session->course_id)
            ->whereHas('enrollment', function ($query) use ($record) {
                $query->where('student_id', $record->student_id);
            })
            ->first();

        if (!$enrollmentCourse) {
            return;
        }

        $attendedSessions = AttendanceRecord::where('student_id', $record->student_id)
            ->whereIn('status', ['present', 'late'])
            ->whereHas('session', function ($query) use ($session) {
                $query->where('course_id', $session->course_id);
            })
            ->count();

        $totalSessions = (int) ($enrollmentCourse->total_sessions ?: ($session->group?->total_sessions ?: 12));
        $currentSession = min($attendedSessions, $totalSessions);

        $newStatus = $currentSession >= $totalSessions ? 'completed' : 'active';

        $enrollmentCourse->update([
            'current_session' => $currentSession,
            'status' => $newStatus,
            'total_sessions' => $totalSessions,
        ]);
    }
}

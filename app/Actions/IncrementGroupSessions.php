<?php

namespace App\Actions;

use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\EnrollmentCourse;
use App\Models\Group;

class IncrementGroupSessions
{
    /**
     * Execute the action to increment current session for all students in a group
     */
    public static function execute(Group $group, ?string $notes = null): void
    {
        // Increment group's session counter
        $newSession = ($group->completed_sessions ?? 0) + 1;
        
        // Don't exceed total sessions
        if ($newSession > ($group->total_sessions ?? 12)) {
            $newSession = $group->total_sessions ?? 12;
        }

        $group->update([
            'completed_sessions' => $newSession,
        ]);

        // Update all active students in this group for this course
        $enrollmentCourses = EnrollmentCourse::where('group_id', $group->id)
            ->where('course_id', $group->course_id)
            ->where('status', 'active')
            ->where('tenant_id', $group->tenant_id)
            ->get();

        foreach ($enrollmentCourses as $enrollmentCourse) {
            // Increment session
            $enrollmentCourse->current_session = $newSession;

            // Check if should be completed
            if ($newSession >= ($enrollmentCourse->total_sessions ?? 12)) {
                $enrollmentCourse->status = 'completed';
            }

            $enrollmentCourse->save();
        }

        // Create attendance session record
        AttendanceSession::create([
            'tenant_id' => $group->tenant_id,
            'course_id' => $group->course_id,
            'group_id' => $group->id,
            'attendance_date' => now()->toDateString(),
            'classroom' => $group->classroom?->name ?? $group->classroom?->code ?? 'Unknown',
            'session_notes' => $notes,
            'recorded_by' => auth()->id(),
        ]);
    }
}

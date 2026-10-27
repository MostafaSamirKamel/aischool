<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Notifications\AttendanceNotification;
use Illuminate\Support\Facades\Notification;

class AttendanceObserver
{
    /**
     * Handle the Attendance "created" event.
     */
    public function created(Attendance $attendance): void
    {
        // إشعار ولي الأمر بحضور/غياب الطالب
        if ($attendance->student->user->parent) {
            $attendance->student->user->parent->notify(
                new AttendanceNotification(
                    $attendance->attendanceSession,
                    $attendance->student,
                    $attendance->status
                )
            );
        }

        // إشعار الطالب نفسه
        $attendance->student->user->notify(
            new AttendanceNotification(
                $attendance->attendanceSession,
                $attendance->student,
                $attendance->status
            )
        );

        // إشعار المشرف إذا كان هناك غياب
        if (in_array($attendance->status, ['absent', 'late']) && $attendance->attendanceSession->group->supervisor) {
            $attendance->attendanceSession->group->supervisor->user->notify(
                new AttendanceNotification(
                    $attendance->attendanceSession,
                    $attendance->student,
                    $attendance->status
                )
            );
        }
    }

    /**
     * Handle the Attendance "updated" event.
     */
    public function updated(Attendance $attendance): void
    {
        // إشعار بتحديث الحضور إذا تغيرت الحالة
        if ($attendance->wasChanged('status')) {
            if ($attendance->student->user->parent) {
                $attendance->student->user->parent->notify(
                    new AttendanceNotification(
                        $attendance->attendanceSession,
                        $attendance->student,
                        $attendance->status
                    )
                );
            }
        }
    }
}

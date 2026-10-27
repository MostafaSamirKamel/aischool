<?php

namespace App\Observers;

use App\Models\Assignment;
use App\Notifications\NewAssignmentNotification;

class AssignmentObserver
{
    /**
     * Handle the Assignment "created" event.
     */
    public function created(Assignment $assignment): void
    {
        // إشعار جميع الطلاب في المجموعة بالواجب الجديد
        $students = $assignment->group->students;
        
        foreach ($students as $student) {
            $student->user->notify(
                new NewAssignmentNotification($assignment)
            );

            // إشعار ولي الأمر أيضاً
            if ($student->user->parent) {
                $student->user->parent->notify(
                    new NewAssignmentNotification($assignment)
                );
            }
        }
    }
}

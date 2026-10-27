<?php

namespace App\Observers;

use App\Models\Exam;
use App\Notifications\NewExamNotification;

class ExamObserver
{
    /**
     * Handle the Exam "created" event.
     */
    public function created(Exam $exam): void
    {
        // إشعار جميع الطلاب في المجموعة بالامتحان الجديد
        $students = $exam->group->students;
        
        foreach ($students as $student) {
            $student->user->notify(
                new NewExamNotification($exam)
            );

            // إشعار ولي الأمر أيضاً
            if ($student->user->parent) {
                $student->user->parent->notify(
                    new NewExamNotification($exam)
                );
            }
        }
    }
}

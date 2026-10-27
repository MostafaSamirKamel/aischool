<?php

namespace App\Observers;

use App\Models\ExamAttempt;
use App\Notifications\NewGradeNotification;

class ExamAttemptObserver
{
    /**
     * Handle the ExamAttempt "created" event.
     */
    public function created(ExamAttempt $examAttempt): void
    {
        // لا نرسل إشعار عند إنشاء المحاولة، فقط عند التصحيح
    }

    /**
     * Handle the ExamAttempt "updated" event.
     */
    public function updated(ExamAttempt $examAttempt): void
    {
        // إشعار الطالب وولي الأمر عند إضافة/تحديث الدرجة
        if ($examAttempt->wasChanged('score') && $examAttempt->score !== null) {
            // إشعار الطالب
            $examAttempt->student->user->notify(
                new NewGradeNotification($examAttempt)
            );

            // إشعار ولي الأمر
            if ($examAttempt->student->user->parent) {
                $examAttempt->student->user->parent->notify(
                    new NewGradeNotification($examAttempt)
                );
            }
        }
    }
}

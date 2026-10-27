<?php

namespace App\Console\Commands;

use App\Models\Exam;
use App\Models\Student;
use App\Notifications\UpcomingExamNotification;
use Illuminate\Console\Command;

class SendExamReminders extends Command
{
    protected $signature = 'exams:send-reminders';

    protected $description = 'Send reminders for upcoming exams';

    public function handle(): int
    {
        $this->info('Sending exam reminders...');

        // Get exams in the next 7 days
        $upcomingExams = Exam::where('status', 'scheduled')
            ->whereBetween('exam_date', [now(), now()->addDays(7)])
            ->get();

        if ($upcomingExams->isEmpty()) {
            $this->info('No upcoming exams found.');
            return self::SUCCESS;
        }

        $notificationsSent = 0;

        foreach ($upcomingExams as $exam) {
            $daysUntil = now()->diffInDays($exam->exam_date, false);
            
            // Send reminders at 7 days, 3 days, 1 day, and same day
            if (!in_array($daysUntil, [7, 3, 1, 0])) {
                continue;
            }

            // Get students enrolled in this exam's group
            $students = Student::whereHas('enrollments', function ($query) use ($exam) {
                $query->where('group_id', $exam->group_id);
            })->get();

            foreach ($students as $student) {
                $student->notify(new UpcomingExamNotification($exam, $daysUntil));
                $notificationsSent++;
            }

            $this->info("Sent {$students->count()} reminders for: {$exam->title} ({$daysUntil} days until)");
        }

        $this->info("Total notifications sent: {$notificationsSent}");
        return self::SUCCESS;
    }
}

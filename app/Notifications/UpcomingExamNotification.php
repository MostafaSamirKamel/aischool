<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpcomingExamNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Exam $exam;
    protected int $daysUntil;

    public function __construct(Exam $exam, int $daysUntil)
    {
        $this->exam = $exam;
        $this->daysUntil = $daysUntil;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $timeMessage = match ($this->daysUntil) {
            0 => 'اليوم',
            1 => 'غداً',
            2 => 'بعد غد',
            default => 'بعد ' . $this->daysUntil . ' أيام',
        };

        return (new MailMessage)
            ->subject('تذكير: امتحان قادم - ' . $this->exam->title)
            ->greeting('مرحباً ' . $notifiable->first_name . '!')
            ->line('لديك امتحان قادم ' . $timeMessage)
            ->line('**الامتحان:** ' . $this->exam->title)
            ->line('**الكورس:** ' . $this->exam->course->name)
            ->line('**التاريخ:** ' . $this->exam->exam_date->format('d/m/Y h:i A'))
            ->line('**المدة:** ' . $this->exam->duration . ' دقيقة')
            ->line('**الدرجة الكلية:** ' . $this->exam->total_marks)
            ->line('**درجة النجاح:** ' . $this->exam->passing_marks)
            ->when($this->exam->instructions, function ($message) {
                return $message->line('**التعليمات:** ' . $this->exam->instructions);
            })
            ->action('عرض تفاصيل الامتحان', url('/admin/exams/' . $this->exam->id))
            ->line('بالتوفيق! 🎓');
    }

    public function toArray($notifiable): array
    {
        return [
            'exam_id' => $this->exam->id,
            'exam_title' => $this->exam->title,
            'course_name' => $this->exam->course->name,
            'exam_date' => $this->exam->exam_date->toDateTimeString(),
            'duration' => $this->exam->duration,
            'total_marks' => $this->exam->total_marks,
            'days_until' => $this->daysUntil,
        ];
    }
}

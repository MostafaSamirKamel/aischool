<?php

namespace App\Notifications;

use App\Models\Grade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GradePublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Grade $grade;

    public function __construct(Grade $grade)
    {
        $this->grade = $grade;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $exam = $this->grade->exam;
        $isPassing = $this->grade->marks_obtained >= $exam->passing_marks;

        return (new MailMessage)
            ->subject('نتيجة امتحان: ' . $exam->title)
            ->greeting('مرحباً ' . $notifiable->first_name . '!')
            ->line('تم نشر نتيجة امتحانك في ' . $exam->title)
            ->line('**الكورس:** ' . $exam->course->name)
            ->line('**الدرجة المستحقة:** ' . $this->grade->marks_obtained . ' من ' . $exam->total_marks)
            ->line('**النسبة المئوية:** ' . $this->grade->percentage . '%')
            ->line('**التقدير:** ' . $this->grade->grade)
            ->line('**الحالة:** ' . ($isPassing ? '✅ ناجح' : '❌ راسب'))
            ->when($this->grade->remarks, function ($message) {
                return $message->line('**ملاحظات:** ' . $this->grade->remarks);
            })
            ->action('عرض كشف الدرجات', url('/admin/students/transcript?student_id=' . $notifiable->id))
            ->line('استمر في التفوق!');
    }

    public function toArray($notifiable): array
    {
        return [
            'grade_id' => $this->grade->id,
            'exam_id' => $this->grade->exam_id,
            'exam_title' => $this->grade->exam->title,
            'course_name' => $this->grade->exam->course->name,
            'marks_obtained' => $this->grade->marks_obtained,
            'total_marks' => $this->grade->exam->total_marks,
            'percentage' => $this->grade->percentage,
            'grade' => $this->grade->grade,
            'is_passing' => $this->grade->marks_obtained >= $this->grade->exam->passing_marks,
        ];
    }
}

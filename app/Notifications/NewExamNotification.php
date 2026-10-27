<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewExamNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $exam;

    public function __construct($exam)
    {
        $this->exam = $exam;
    }

    public function via($notifiable): array
    {
                return ['database', 'mail', \App\Channels\SmsChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'exam',
            'title' => 'امتحان جديد',
            'message' => "تم إضافة امتحان جديد: {$this->exam->title}",
            'icon' => 'heroicon-o-clipboard-document-check',
            'color' => 'warning',
            'data' => [
                'exam_id' => $this->exam->id,
                'exam_title' => $this->exam->title,
                'group_id' => $this->exam->group_id,
                'group_name' => $this->exam->group->name,
                'exam_date' => $this->exam->exam_date,
                'duration' => $this->exam->duration,
                'total_marks' => $this->exam->total_marks,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('امتحان جديد')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line("تم إضافة امتحان جديد: {$this->exam->title}")
            ->line("المجموعة: {$this->exam->group->name}")
            ->line("التاريخ: {$this->exam->exam_date->format('Y-m-d')}")
            ->line("المدة: {$this->exam->duration} دقيقة")
            ->line("الدرجة الكاملة: {$this->exam->total_marks}")
            ->action('عرض الامتحان', url('/'))
            ->line('استعد جيداً للامتحان!');
    }

    public function toSms($notifiable): string
    {
        $examDate = $this->exam->exam_date->format('Y-m-d H:i');
        return "امتحان جديد: {$this->exam->title} - المجموعة: {$this->exam->group->name} - الموعد: {$examDate} - المدة: {$this->exam->duration} دقيقة";
    }
}

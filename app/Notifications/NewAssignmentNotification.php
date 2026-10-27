<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $assignment;

    public function __construct($assignment)
    {
        $this->assignment = $assignment;
    }

    public function via($notifiable): array
    {
                return ['database', 'mail', \App\Channels\SmsChannel::class];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'assignment',
            'title' => 'واجب جديد',
            'message' => "تم إضافة واجب جديد: {$this->assignment->title}",
            'icon' => 'heroicon-o-document-text',
            'color' => 'info',
            'data' => [
                'assignment_id' => $this->assignment->id,
                'assignment_title' => $this->assignment->title,
                'group_id' => $this->assignment->group_id,
                'group_name' => $this->assignment->group->name,
                'due_date' => $this->assignment->due_date,
                'max_score' => $this->assignment->max_score,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('واجب جديد')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line("تم إضافة واجب جديد: {$this->assignment->title}")
            ->line("المجموعة: {$this->assignment->group->name}")
            ->line("تاريخ التسليم: {$this->assignment->due_date}")
            ->line("الدرجة الكاملة: {$this->assignment->max_score}")
            ->action('عرض الواجب', url('/'))
            ->line('لا تنسى تسليم الواجب في الموعد!');
    }

    public function toSms($notifiable): string
    {
        $dueDate = \Carbon\Carbon::parse($this->assignment->due_date)->format('Y-m-d');
        return "واجب جديد: {$this->assignment->title} - المجموعة: {$this->assignment->group->name} - موعد التسليم: {$dueDate}";
    }
}

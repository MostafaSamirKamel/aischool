<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewGradeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $examAttempt;

    public function __construct($examAttempt)
    {
        $this->examAttempt = $examAttempt;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail', \App\Channels\SmsChannel::class, \App\Channels\WhatsAppChannel::class];
    }

    public function toArray($notifiable): array
    {
        $percentage = $this->examAttempt->score ? 
            round(($this->examAttempt->score / $this->examAttempt->exam->total_marks) * 100, 1) : 0;

        $status = $percentage >= 50 ? 'ناجح' : 'راسب';
        $color = $percentage >= 85 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');

        return [
            'type' => 'grade',
            'title' => 'درجة جديدة',
            'message' => "تم إضافة درجة امتحان {$this->examAttempt->exam->title} - الدرجة: {$this->examAttempt->score}/{$this->examAttempt->exam->total_marks}",
            'icon' => 'heroicon-o-academic-cap',
            'color' => $color,
            'data' => [
                'exam_id' => $this->examAttempt->exam->id,
                'exam_title' => $this->examAttempt->exam->title,
                'score' => $this->examAttempt->score,
                'total_marks' => $this->examAttempt->exam->total_marks,
                'percentage' => $percentage,
                'status' => $status,
                'student_id' => $this->examAttempt->student_id,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $percentage = $this->examAttempt->score ? 
            round(($this->examAttempt->score / $this->examAttempt->exam->total_marks) * 100, 1) : 0;

        return (new MailMessage)
            ->subject('درجة امتحان جديدة')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line("تم إضافة درجة امتحان {$this->examAttempt->exam->title}")
            ->line("الدرجة: {$this->examAttempt->score} من {$this->examAttempt->exam->total_marks}")
            ->line("النسبة المئوية: {$percentage}%")
            ->action('عرض النتيجة', url('/'))
            ->line('استمر في التقدم!');
    }

    public function toWhatsApp($notifiable): string
    {
        $percentage = $this->examAttempt->score ?
            round(($this->examAttempt->score / $this->examAttempt->exam->total_marks) * 100, 1) : 0;

        $status = $percentage >= 50 ? 'ناجح ✅' : 'راسب ❌';
        $icon = $percentage >= 85 ? '🌟' : ($percentage >= 50 ? '📝' : '📉');

        return "{$icon} *درجة امتحان جديدة*\n\n" .
            "الامتحان: *{$this->examAttempt->exam->title}*\n" .
            "الدرجة: {$this->examAttempt->score}/{$this->examAttempt->exam->total_marks}\n" .
            "النسبة: {$percentage}%\n" .
            "النتيجة: {$status}\n\n" .
            "استمر في التقدم! 💪";
    }

    public function toSms($notifiable): string
    {
        $percentage = $this->examAttempt->score ?
            round(($this->examAttempt->score / $this->examAttempt->exam->total_marks) * 100, 1) : 0;

        return "درجة جديدة: {$this->examAttempt->exam->title} - الدرجة: {$this->examAttempt->score}/{$this->examAttempt->exam->total_marks} ({$percentage}%)";
    }
}

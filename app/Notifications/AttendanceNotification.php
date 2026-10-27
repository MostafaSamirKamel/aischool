<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AttendanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $attendanceSession;
    protected $student;
    protected $status;

    public function __construct($attendanceSession, $student, $status)
    {
        $this->attendanceSession = $attendanceSession;
        $this->student = $student;
        $this->status = $status;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail', \App\Channels\SmsChannel::class, \App\Channels\WhatsAppChannel::class];
    }

    public function toArray($notifiable): array
    {
        $statusArabic = match($this->status) {
            'present' => 'حاضر',
            'absent' => 'غائب',
            'late' => 'متأخر',
            'excused' => 'غياب بعذر',
            default => $this->status
        };

        return [
            'type' => 'attendance',
            'title' => 'تحديث الحضور',
            'message' => "تم تسجيل حضور الطالب {$this->student->user->name} - الحالة: {$statusArabic}",
            'icon' => $this->status === 'present' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle',
            'color' => $this->status === 'present' ? 'success' : ($this->status === 'absent' ? 'danger' : 'warning'),
            'data' => [
                'student_id' => $this->student->id,
                'student_name' => $this->student->user->name,
                'session_id' => $this->attendanceSession->id,
                'session_date' => $this->attendanceSession->attendance_date,
                'group_name' => $this->attendanceSession->group->name,
                'status' => $this->status,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $statusArabic = match($this->status) {
            'present' => 'حاضر',
            'absent' => 'غائب',
            'late' => 'متأخر',
            'excused' => 'غياب بعذر',
            default => $this->status
        };

        return (new MailMessage)
            ->subject('تحديث حضور الطالب')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line("تم تسجيل حضور الطالب {$this->student->user->name}")
            ->line("الحالة: {$statusArabic}")
            ->line("المجموعة: {$this->attendanceSession->group->name}")
            ->line("التاريخ: {$this->attendanceSession->attendance_date}")
            ->action('عرض التفاصيل', url('/'))
            ->line('شكراً لاستخدامك نظامنا!');
    }

    public function toWhatsApp($notifiable): string
    {
        $statusArabic = match ($this->status) {
            'present' => 'حاضر ✅',
            'absent' => 'غائب 🔴',
            'late' => 'متأخر ⚠️',
            'excused' => 'غياب بعذر 📝',
            default => $this->status
        };

        $statusIcon = match ($this->status) {
            'present' => '✅',
            'absent' => '🔴',
            'late' => '⚠️',
            'excused' => '📝',
            default => '📊'
        };

        return "{$statusIcon} *تحديث حضور*\n\n" .
            "الطالب/ة: *{$this->student->user->name}*\n" .
            "الحالة: {$statusArabic}\n" .
            "المجموعة: {$this->attendanceSession->group->name}\n" .
            "التاريخ: {$this->attendanceSession->attendance_date}\n\n" .
            "للاستفسار يرجى التواصل مع الإدارة.";
    }

    public function toSms($notifiable): string
    {
        $statusArabic = match ($this->status) {
            'present' => 'حاضر',
            'absent' => 'غائب',
            'late' => 'متأخر',
            'excused' => 'غياب بعذر',
            default => $this->status
        };

        return "تحديث حضور: {$this->student->user->name} - الحالة: {$statusArabic} - المجموعة: {$this->attendanceSession->group->name} - التاريخ: {$this->attendanceSession->attendance_date}";
    }
}

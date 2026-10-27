<?php

namespace App\Notifications;

use App\Models\EnrollmentCourse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class CourseCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public EnrollmentCourse $enrollmentCourse
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $courseName = $this->enrollmentCourse->course->name_ar ?? $this->enrollmentCourse->course->name;
        $studentName = $this->enrollmentCourse->enrollment->student->user->name;

        return (new MailMessage)
            ->subject('🎉 تهانينا! لقد أكملت الكورس')
            ->greeting('مبروك يا ' . $studentName . '!')
            ->line('تهانينا! لقد أكملت جميع جلسات كورس **' . $courseName . '** بنجاح.')
            ->line('عدد الجلسات المكتملة: ' . $this->enrollmentCourse->total_sessions)
            ->line('يمكنك الآن مراجعة إنجازاتك وطلب شهادة الإتمام إن وجدت.')
            ->action('عرض تفاصيل الكورس', url('/student'))
            ->line('نتمنى لك التوفيق في مسيرتك التعليمية!');
    }

    /**
     * Get the array representation of the notification (for database).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'course_completed',
            'title' => 'اكتمال الكورس',
            'message' => 'تهانينا! لقد أكملت كورس ' . ($this->enrollmentCourse->course->name_ar ?? $this->enrollmentCourse->course->name),
            'enrollment_course_id' => $this->enrollmentCourse->id,
            'course_id' => $this->enrollmentCourse->course_id,
            'course_name' => $this->enrollmentCourse->course->name_ar ?? $this->enrollmentCourse->course->name,
            'total_sessions' => $this->enrollmentCourse->total_sessions,
            'icon' => 'heroicon-o-academic-cap',
            'color' => 'success',
        ];
    }

    /**
     * Send Filament notification
     */
    public function toFilament(object $notifiable): FilamentNotification
    {
        $courseName = $this->enrollmentCourse->course->name_ar ?? $this->enrollmentCourse->course->name;

        return FilamentNotification::make()
            ->success()
            ->title('🎉 مبروك! اكتمل الكورس')
            ->body('لقد أكملت جميع جلسات كورس **' . $courseName . '** بنجاح!')
            ->icon('heroicon-o-academic-cap')
            ->iconColor('success');
    }
}

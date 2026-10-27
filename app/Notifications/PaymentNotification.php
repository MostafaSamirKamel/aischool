<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;
    protected $action;

    public function __construct($payment, $action = 'created')
    {
        $this->payment = $payment;
        $this->action = $action;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail', \App\Channels\SmsChannel::class, \App\Channels\WhatsAppChannel::class];
    }

    public function toArray($notifiable): array
    {
        $actionArabic = match($this->action) {
            'created' => 'تم إضافة',
            'confirmed' => 'تم تأكيد',
            'rejected' => 'تم رفض',
            default => $this->action
        };

        return [
            'type' => 'payment',
            'title' => 'تحديث الدفع',
            'message' => "{$actionArabic} دفع بمبلغ {$this->payment->amount} جنيه",
            'icon' => 'heroicon-o-currency-dollar',
            'color' => $this->payment->status === 'completed' ? 'success' : 'warning',
            'data' => [
                'payment_id' => $this->payment->id,
                'amount' => $this->payment->amount,
                'status' => $this->payment->status,
                'payment_method' => $this->payment->payment_method,
                'student_id' => $this->payment->student_id,
                'action' => $this->action,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $actionArabic = match($this->action) {
            'created' => 'تم إضافة دفعة جديدة',
            'confirmed' => 'تم تأكيد الدفع',
            'rejected' => 'تم رفض الدفع',
            default => 'تحديث الدفع'
        };

        return (new MailMessage)
            ->subject($actionArabic)
            ->greeting('مرحباً ' . $notifiable->name)
            ->line($actionArabic)
            ->line("المبلغ: {$this->payment->amount} جنيه")
            ->line("طريقة الدفع: {$this->payment->payment_method}")
            ->line("الحالة: {$this->payment->status}")
            ->action('عرض التفاصيل', url('/'))
            ->line('شكراً لك!');
    }

    public function toWhatsApp($notifiable): string
    {
        $actionArabic = match ($this->action) {
            'created' => 'تم إضافة دفعة',
            'confirmed' => 'تم تأكيد الدفع',
            'rejected' => 'تم رفض الدفع',
            default => 'تحديث الدفع'
        };

        $icon = $this->payment->status === 'completed' ? '💰' : '📝';

        return "{$icon} *{$actionArabic}*\n\n" .
            "المبلغ: *{$this->payment->amount} جنيه*\n" .
            "طريقة الدفع: {$this->payment->payment_method}\n" .
            "الحالة: {$this->payment->status}\n" .
            "التاريخ: " . now()->format('Y-m-d') . "\n\n" .
            "شكراً لثقتكم بنا! 🙏";
    }

    public function toSms($notifiable): string
    {
        $actionArabic = match ($this->action) {
            'created' => 'تم إضافة',
            'confirmed' => 'تم تأكيد',
            'rejected' => 'تم رفض',
            default => $this->action
        };

        return "{$actionArabic} دفع بمبلغ {$this->payment->amount} جنيه - الحالة: {$this->payment->status}";
    }
}

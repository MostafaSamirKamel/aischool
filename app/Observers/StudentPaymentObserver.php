<?php

namespace App\Observers;

use App\Models\StudentPayment;
use App\Notifications\PaymentNotification;

class StudentPaymentObserver
{
    /**
     * Handle the StudentPayment "created" event.
     */
    public function created(StudentPayment $payment): void
    {
        // إشعار الطالب وولي الأمر بالدفع الجديد
        $payment->student->user->notify(
            new PaymentNotification($payment, 'created')
        );

        if ($payment->student->user->parent) {
            $payment->student->user->parent->notify(
                new PaymentNotification($payment, 'created')
            );
        }
    }

    /**
     * Handle the StudentPayment "updated" event.
     */
    public function updated(StudentPayment $payment): void
    {
        // إشعار عند تغيير حالة الدفع
        if ($payment->wasChanged('status')) {
            $action = match($payment->status) {
                'completed' => 'confirmed',
                'failed' => 'rejected',
                default => 'updated'
            };

            $payment->student->user->notify(
                new PaymentNotification($payment, $action)
            );

            if ($payment->student->user->parent) {
                $payment->student->user->parent->notify(
                    new PaymentNotification($payment, $action)
                );
            }
        }
    }
}

<?php

namespace App\Channels;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        // Get phone number from notifiable entity
        $phone = $notifiable->routeNotificationFor('sms', $notification);

        if (!$phone) {
            return;
        }

        // Get message from notification
        $message = $notification->toSms($notifiable);

        // Send SMS
        if (config('sms.queue')) {
            // Queue the SMS for background processing
            dispatch(function () use ($phone, $message) {
                $this->smsService->send($phone, $message);
            })->onQueue('sms');
        } else {
            // Send immediately
            $this->smsService->send($phone, $message);
        }
    }
}

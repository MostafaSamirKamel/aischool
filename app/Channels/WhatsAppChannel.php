<?php

namespace App\Channels;

use App\Services\WhatsAppService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        // Get phone number from notifiable entity
        $phone = $notifiable->routeNotificationFor('whatsapp', $notification);

        if (!$phone) {
            Log::warning('No WhatsApp phone number found for notification', [
                'notifiable_id' => $notifiable->id,
                'notifiable_type' => get_class($notifiable),
            ]);
            return;
        }

        // Get message from notification
        if (!method_exists($notification, 'toWhatsApp')) {
            Log::warning('Notification does not implement toWhatsApp method', [
                'notification' => get_class($notification),
            ]);
            return;
        }

        $message = $notification->toWhatsApp($notifiable);

        // Send WhatsApp
        if (config('whatsapp.queue', true)) {
            // Queue the WhatsApp for background processing
            dispatch(function () use ($phone, $message) {
                $this->whatsappService->send($phone, $message);
            })->onQueue('whatsapp');
        } else {
            // Send immediately
            $this->whatsappService->send($phone, $message);
        }
    }
}

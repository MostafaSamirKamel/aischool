<?php

namespace App\Services\WhatsAppProviders;

use App\Contracts\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Log;

class LogProvider implements WhatsAppProviderInterface
{
    public function send(string $to, string $message): bool
    {
        Log::info("WhatsApp [Log Driver]", [
            'to' => $to,
            'message' => $message,
            'timestamp' => now()->toDateTimeString(),
        ]);

        return true;
    }

    public function sendImage(string $to, string $message, string $imageUrl): bool
    {
        Log::info("WhatsApp Image [Log Driver]", [
            'to' => $to,
            'message' => $message,
            'image_url' => $imageUrl,
            'timestamp' => now()->toDateTimeString(),
        ]);

        return true;
    }

    public function sendDocument(string $to, string $documentUrl, string $filename): bool
    {
        Log::info("WhatsApp Document [Log Driver]", [
            'to' => $to,
            'document_url' => $documentUrl,
            'filename' => $filename,
            'timestamp' => now()->toDateTimeString(),
        ]);

        return true;
    }

    public function getName(): string
    {
        return 'Log';
    }

    public function isConfigured(): bool
    {
        return true;
    }
}

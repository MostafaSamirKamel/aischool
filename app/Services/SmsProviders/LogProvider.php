<?php

namespace App\Services\SmsProviders;

use App\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Log;

class LogProvider implements SmsProviderInterface
{
    public function send(string $to, string $message): bool
    {
        Log::info("SMS [Log Driver]", [
            'to' => $to,
            'message' => $message,
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

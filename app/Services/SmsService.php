<?php

namespace App\Services;

use App\Constants\SystemConstants;
use App\Contracts\SmsProviderInterface;
use App\Models\SmsLog;
use App\Services\SmsProviders\LogProvider;
use App\Services\SmsProviders\TwilioProvider;
use App\Services\SmsProviders\VodafoneProvider;
use App\Services\SmsProviders\VonageProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class SmsService
{
    protected SmsProviderInterface $provider;
    protected string $driverName;

    public function __construct(?string $driver = null)
    {
        $this->driverName = $driver ?? config('sms.default', 'log');
        $this->provider = $this->createProvider($this->driverName);
    }

    /**
     * Create SMS provider instance using Strategy Pattern
     */
    protected function createProvider(string $driver): SmsProviderInterface
    {
        return match ($driver) {
            'twilio' => new TwilioProvider(),
            'nexmo', 'vonage' => new VonageProvider(),
            'vodafone' => new VodafoneProvider(),
            'log' => new LogProvider(),
            default => new LogProvider(),
        };
    }

    /**
     * Send SMS message
     */
    public function send(string $to, string $message, array $data = []): bool
    {
        // Check rate limit
        if (!$this->checkRateLimit($to)) {
            Log::warning("SMS rate limit exceeded for {$to}");
            return false;
        }

        // Normalize phone number
        $to = $this->normalizePhone($to);

        // Check if provider is configured
        if (!$this->provider->isConfigured()) {
            Log::error("SMS provider {$this->provider->getName()} is not properly configured");
            return false;
        }

        // Send via provider
        try {
            $result = $this->provider->send($to, $message);

            // Log to database
            if (config('sms.log_messages', true)) {
                $this->logMessage($to, $message, $result, $data);
            }

            // Update rate limit counter
            if ($result) {
                $this->incrementRateLimit($to);
            }

            return $result;
        } catch (Exception $e) {
            Log::error("SMS sending failed: " . $e->getMessage());

            if (config('sms.log_messages', true)) {
                $this->logMessage($to, $message, false, array_merge($data, ['error' => $e->getMessage()]));
            }
            
            return false;
        }
    }

    /**
     * Send SMS using template
     */
    public function sendTemplate(string $to, string $template, array $variables = []): bool
    {
        $message = config("sms.templates.{$template}");
        
        if (!$message) {
            Log::error("SMS template not found: {$template}");
            return false;
        }

        // Replace variables
        foreach ($variables as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }

        // Add school name
        $message = str_replace('{school_name}', config('sms.school_name'), $message);

        return $this->send($to, $message, ['template' => $template, 'variables' => $variables]);
    }

    /**
     * Normalize phone number to E.164 format
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add Egypt country code if not present
        if (!str_starts_with($phone, '20') && strlen($phone) === 11) {
            $phone = '20' . $phone;
        }

        // Add + prefix
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Check rate limit
     */
    protected function checkRateLimit(string $to): bool
    {
        if (!config('sms.rate_limit.enabled', true)) {
            return true;
        }

        $minuteKey = "sms_rate_limit_minute:{$to}";
        $hourKey = "sms_rate_limit_hour:{$to}";

        $minuteCount = Cache::get($minuteKey, 0);
        $hourCount = Cache::get($hourKey, 0);

        $maxPerMinute = config('sms.rate_limit.max_per_minute', SystemConstants::SMS_MAX_PER_MINUTE);
        $maxPerHour = config('sms.rate_limit.max_per_hour', 50);

        return $minuteCount < $maxPerMinute && $hourCount < $maxPerHour;
    }

    /**
     * Increment rate limit counter
     */
    protected function incrementRateLimit(string $to): void
    {
        if (!config('sms.rate_limit.enabled', true)) {
            return;
        }

        $minuteKey = "sms_rate_limit_minute:{$to}";
        $hourKey = "sms_rate_limit_hour:{$to}";

        Cache::put($minuteKey, Cache::get($minuteKey, 0) + 1, now()->addMinute());
        Cache::put($hourKey, Cache::get($hourKey, 0) + 1, now()->addHour());
    }

    /**
     * Log SMS message to database
     */
    protected function logMessage(string $to, string $message, bool $success, array $metadata = []): void
    {
        SmsLog::create([
            'phone' => $to,
            'message' => $message,
            'driver' => $this->driverName,
            'status' => $success ? 'sent' : 'failed',
            'metadata' => $metadata,
            'sent_at' => now(),
        ]);
    }

    /**
     * Get SMS statistics
     */
    public function getStats(array $filters = []): array
    {
        $query = SmsLog::query();

        if (isset($filters['from'])) {
            $query->where('sent_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->where('sent_at', '<=', $filters['to']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return [
            'total' => $query->count(),
            'sent' => (clone $query)->where('status', 'sent')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
            'cost_estimate' => $query->where('status', 'sent')->count() * 0.05, // Assuming 0.05 EGP per SMS
        ];
    }

    /**
     * Change SMS provider at runtime
     */
    public function setProvider(string $driver): self
    {
        $this->driverName = $driver;
        $this->provider = $this->createProvider($driver);
        return $this;
    }

    /**
     * Get current provider name
     */
    public function getProviderName(): string
    {
        return $this->provider->getName();
    }
}

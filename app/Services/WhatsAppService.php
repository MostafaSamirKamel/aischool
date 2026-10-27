<?php

namespace App\Services;

use App\Contracts\WhatsAppProviderInterface;
use App\Models\WhatsAppLog;
use App\Services\WhatsAppProviders\LogProvider;
use App\Services\WhatsAppProviders\UltramsgProvider;
use App\Services\WhatsAppProviders\SogProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppService
{
    protected WhatsAppProviderInterface $provider;
    protected string $driverName;

    public function __construct(?string $driver = null)
    {
        $this->driverName = $driver ?? config('whatsapp.default', 'log');
        $this->provider = $this->createProvider($this->driverName);
    }

    /**
     * Create WhatsApp provider instance using Strategy Pattern
     */
    protected function createProvider(string $driver): WhatsAppProviderInterface
    {
        return match ($driver) {
            'ultramsg' => new UltramsgProvider(),
            'sog' => new SogProvider(),
            'log' => new LogProvider(),
            default => new LogProvider(),
        };
    }

    /**
     * Send WhatsApp message
     */
    public function send(string $to, string $message, array $data = []): bool
    {
        // Check rate limit
        if (!$this->checkRateLimit($to)) {
            Log::warning("WhatsApp rate limit exceeded for {$to}");
            return false;
        }

        // Normalize phone number
        $to = $this->normalizePhone($to);

        // Check if provider is configured
        if (!$this->provider->isConfigured()) {
            Log::error("WhatsApp provider {$this->provider->getName()} is not properly configured");
            return false;
        }

        // Send via provider
        try {
            $result = $this->provider->send($to, $message);

            // Log to database
            if (config('whatsapp.log_messages', true)) {
                $this->logMessage($to, $message, 'text', $result, $data);
            }

            // Update rate limit counter
            if ($result) {
                $this->incrementRateLimit($to);
            }

            return $result;
        } catch (Exception $e) {
            Log::error("WhatsApp sending failed: " . $e->getMessage());

            if (config('whatsapp.log_messages', true)) {
                $this->logMessage($to, $message, 'text', false, array_merge($data, ['error' => $e->getMessage()]));
            }

            return false;
        }
    }

    /**
     * Send WhatsApp message with image
     */
    public function sendImage(string $to, string $message, string $imageUrl, array $data = []): bool
    {
        // Check rate limit
        if (!$this->checkRateLimit($to)) {
            Log::warning("WhatsApp rate limit exceeded for {$to}");
            return false;
        }

        // Normalize phone number
        $to = $this->normalizePhone($to);

        try {
            $result = $this->provider->sendImage($to, $message, $imageUrl);

            // Log to database
            if (config('whatsapp.log_messages', true)) {
                $this->logMessage($to, $message, 'image', $result, array_merge($data, ['image_url' => $imageUrl]));
            }

            if ($result) {
                $this->incrementRateLimit($to);
            }

            return $result;
        } catch (Exception $e) {
            Log::error("WhatsApp image sending failed: " . $e->getMessage());

            if (config('whatsapp.log_messages', true)) {
                $this->logMessage($to, $message, 'image', false, array_merge($data, ['error' => $e->getMessage()]));
            }

            return false;
        }
    }

    /**
     * Send WhatsApp message with document
     */
    public function sendDocument(string $to, string $documentUrl, string $filename, array $data = []): bool
    {
        // Check rate limit
        if (!$this->checkRateLimit($to)) {
            Log::warning("WhatsApp rate limit exceeded for {$to}");
            return false;
        }

        // Normalize phone number
        $to = $this->normalizePhone($to);

        try {
            $result = $this->provider->sendDocument($to, $documentUrl, $filename);

            // Log to database
            if (config('whatsapp.log_messages', true)) {
                $this->logMessage($to, '', 'document', $result, array_merge($data, [
                    'document_url' => $documentUrl,
                    'filename' => $filename,
                ]));
            }

            if ($result) {
                $this->incrementRateLimit($to);
            }

            return $result;
        } catch (Exception $e) {
            Log::error("WhatsApp document sending failed: " . $e->getMessage());

            if (config('whatsapp.log_messages', true)) {
                $this->logMessage($to, '', 'document', false, array_merge($data, ['error' => $e->getMessage()]));
            }

            return false;
        }
    }

    /**
     * Send WhatsApp message using template
     */
    public function sendTemplate(string $to, string $template, array $variables = []): bool
    {
        $message = config("whatsapp.templates.{$template}");

        if (!$message) {
            Log::error("WhatsApp template not found: {$template}");
            return false;
        }

        // Replace variables
        foreach ($variables as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $this->send($to, $message, ['template' => $template]);
    }

    /**
     * Normalize phone number to international format
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If phone starts with '00', replace with '+'
        if (str_starts_with($phone, '00')) {
            $phone = '+' . substr($phone, 2);
        }

        // If phone starts with '0' and is Egyptian (11 digits), add +20
        if (str_starts_with($phone, '0') && strlen($phone) === 11) {
            $phone = '+20' . substr($phone, 1);
        }

        // If phone doesn't start with '+', add '+20' (Egyptian default)
        if (!str_starts_with($phone, '+')) {
            $phone = '+20' . $phone;
        }

        return $phone;
    }

    /**
     * Check rate limit for phone number
     */
    protected function checkRateLimit(string $phone): bool
    {
        $minuteKey = "whatsapp:ratelimit:minute:{$phone}";
        $hourKey = "whatsapp:ratelimit:hour:{$phone}";

        $perMinute = config('whatsapp.rate_limit.per_minute', 5);
        $perHour = config('whatsapp.rate_limit.per_hour', 50);

        $minuteCount = Cache::get($minuteKey, 0);
        $hourCount = Cache::get($hourKey, 0);

        return $minuteCount < $perMinute && $hourCount < $perHour;
    }

    /**
     * Increment rate limit counter
     */
    protected function incrementRateLimit(string $phone): void
    {
        $minuteKey = "whatsapp:ratelimit:minute:{$phone}";
        $hourKey = "whatsapp:ratelimit:hour:{$phone}";

        // Increment or create counter
        Cache::increment($minuteKey, 1);
        Cache::increment($hourKey, 1);

        // Set expiry if key was just created
        if (Cache::get($minuteKey) === 1) {
            Cache::put($minuteKey, 1, now()->addMinutes(1));
        }
        if (Cache::get($hourKey) === 1) {
            Cache::put($hourKey, 1, now()->addHour());
        }
    }

    /**
     * Log message to database
     */
    protected function logMessage(string $to, string $message, string $type, bool $status, array $data = []): void
    {
        try {
            WhatsAppLog::create([
                'phone' => $to,
                'message' => $message,
                'type' => $type,
                'provider' => $this->provider->getName(),
                'status' => $status ? 'sent' : 'failed',
                'metadata' => $data,
                'tenant_id' => auth()->check() ? auth()->user()->tenant_id : null,
            ]);
        } catch (Exception $e) {
            Log::error("Failed to log WhatsApp message: " . $e->getMessage());
        }
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        return [
            'total' => WhatsAppLog::count(),
            'sent' => WhatsAppLog::where('status', 'sent')->count(),
            'failed' => WhatsAppLog::where('status', 'failed')->count(),
            'today' => WhatsAppLog::whereDate('created_at', today())->count(),
            'by_provider' => WhatsAppLog::selectRaw('provider, count(*) as count')
                ->groupBy('provider')
                ->pluck('count', 'provider')
                ->toArray(),
        ];
    }
}

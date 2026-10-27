<?php

namespace App\Services\SmsProviders;

use App\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VodafoneProvider implements SmsProviderInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config ?: config('sms.drivers.vodafone', []);
    }

    public function send(string $to, string $message): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config['token'],
            ])->post($this->config['endpoint'], [
                'from' => $this->config['from'],
                'to' => $to,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("Vodafone SMS sent successfully to {$to}");
                return true;
            }

            Log::error("Vodafone SMS failed: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("Vodafone SMS exception: " . $e->getMessage());
            return false;
        }
    }

    public function getName(): string
    {
        return 'Vodafone';
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['token']) && 
               !empty($this->config['endpoint']) && 
               !empty($this->config['from']);
    }
}

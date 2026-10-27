<?php

namespace App\Services\SmsProviders;

use App\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VonageProvider implements SmsProviderInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config ?: config('sms.drivers.vonage', []);
    }

    public function send(string $to, string $message): bool
    {
        try {
            $response = Http::post('https://rest.nexmo.com/sms/json', [
                'api_key' => $this->config['key'],
                'api_secret' => $this->config['secret'],
                'from' => $this->config['from'],
                'to' => $to,
                'text' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['messages'][0]['status']) && $data['messages'][0]['status'] === '0') {
                    Log::info("Vonage SMS sent successfully to {$to}");
                    return true;
                }
            }

            Log::error("Vonage SMS failed: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("Vonage SMS exception: " . $e->getMessage());
            return false;
        }
    }

    public function getName(): string
    {
        return 'Vonage';
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['key']) && 
               !empty($this->config['secret']) && 
               !empty($this->config['from']);
    }
}

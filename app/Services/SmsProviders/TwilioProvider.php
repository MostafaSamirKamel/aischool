<?php

namespace App\Services\SmsProviders;

use App\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioProvider implements SmsProviderInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config ?: config('sms.drivers.twilio', []);
    }

    public function send(string $to, string $message): bool
    {
        try {
            $response = Http::asForm()
                ->withBasicAuth($this->config['sid'], $this->config['token'])
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->config['sid']}/Messages.json", [
                    'From' => $this->config['from'],
                    'To' => $to,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::info("Twilio SMS sent successfully to {$to}");
                return true;
            }

            Log::error("Twilio SMS failed: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("Twilio SMS exception: " . $e->getMessage());
            return false;
        }
    }

    public function getName(): string
    {
        return 'Twilio';
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['sid']) && 
               !empty($this->config['token']) && 
               !empty($this->config['from']);
    }
}

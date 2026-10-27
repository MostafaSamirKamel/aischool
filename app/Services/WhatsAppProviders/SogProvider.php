<?php

namespace App\Services\WhatsAppProviders;

use App\Contracts\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SogProvider implements WhatsAppProviderInterface
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $sender;

    public function __construct()
    {
        $this->apiUrl = config('whatsapp.sog.api_url', 'https://sender.whatsapp.sog.eg/api');
        $this->apiKey = config('whatsapp.sog.api_key', '');
        $this->sender = config('whatsapp.sog.sender', '');
    }

    public function send(string $to, string $message): bool
    {
        if (!$this->isConfigured()) {
            Log::error('SOG WhatsApp provider is not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->apiUrl}/send", [
                'sender' => $this->sender,
                'recipient' => $to,
                'message' => $message,
                'type' => 'text',
            ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('WhatsApp message sent successfully via SOG', [
                    'to' => $to,
                    'message_id' => $result['messageId'] ?? $result['id'] ?? null,
                    'status' => $result['status'] ?? 'sent',
                ]);
                return true;
            }

            Log::error('Failed to send WhatsApp message via SOG', [
                'status' => $response->status(),
                'response' => $response->body(),
                'to' => $to,
            ]);
            return false;
        } catch (Exception $e) {
            Log::error('WhatsApp SOG exception: ' . $e->getMessage(), [
                'to' => $to,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    public function sendImage(string $to, string $message, string $imageUrl): bool
    {
        if (!$this->isConfigured()) {
            Log::error('SOG WhatsApp provider is not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->apiUrl}/send", [
                'sender' => $this->sender,
                'recipient' => $to,
                'message' => $message,
                'type' => 'image',
                'media' => [
                    'url' => $imageUrl,
                    'caption' => $message,
                ],
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp image sent successfully via SOG', ['to' => $to]);
                return true;
            }

            Log::error('Failed to send WhatsApp image via SOG', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return false;
        } catch (Exception $e) {
            Log::error('WhatsApp SOG image exception: ' . $e->getMessage());
            return false;
        }
    }

    public function sendDocument(string $to, string $documentUrl, string $filename): bool
    {
        if (!$this->isConfigured()) {
            Log::error('SOG WhatsApp provider is not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->apiUrl}/send", [
                'sender' => $this->sender,
                'recipient' => $to,
                'type' => 'document',
                'media' => [
                    'url' => $documentUrl,
                    'filename' => $filename,
                ],
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp document sent successfully via SOG', ['to' => $to]);
                return true;
            }

            Log::error('Failed to send WhatsApp document via SOG', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return false;
        } catch (Exception $e) {
            Log::error('WhatsApp SOG document exception: ' . $e->getMessage());
            return false;
        }
    }

    public function getName(): string
    {
        return 'SOG (Egyptian Government)';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->sender);
    }
}

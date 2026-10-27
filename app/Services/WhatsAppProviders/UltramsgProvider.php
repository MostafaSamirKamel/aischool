<?php

namespace App\Services\WhatsAppProviders;

use App\Contracts\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class UltramsgProvider implements WhatsAppProviderInterface
{
    protected string $instanceId;
    protected string $token;
    protected string $baseUrl = 'https://api.ultramsg.com';

    public function __construct()
    {
        $this->instanceId = config('whatsapp.ultramsg.instance_id', '');
        $this->token = config('whatsapp.ultramsg.token', '');
    }

    public function send(string $to, string $message): bool
    {
        if (!$this->isConfigured()) {
            Log::error('Ultramsg WhatsApp provider is not configured');
            return false;
        }

        try {
            $response = Http::post("{$this->baseUrl}/{$this->instanceId}/messages/chat", [
                'token' => $this->token,
                'to' => $to,
                'body' => $message,
                'priority' => 10,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('WhatsApp message sent successfully via Ultramsg', [
                    'to' => $to,
                    'message_id' => $result['id'] ?? null,
                ]);
                return true;
            }

            Log::error('Failed to send WhatsApp message via Ultramsg', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return false;
        } catch (Exception $e) {
            Log::error('WhatsApp Ultramsg exception: ' . $e->getMessage());
            return false;
        }
    }

    public function sendImage(string $to, string $message, string $imageUrl): bool
    {
        if (!$this->isConfigured()) {
            Log::error('Ultramsg WhatsApp provider is not configured');
            return false;
        }

        try {
            $response = Http::post("{$this->baseUrl}/{$this->instanceId}/messages/image", [
                'token' => $this->token,
                'to' => $to,
                'image' => $imageUrl,
                'caption' => $message,
                'priority' => 10,
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp image sent successfully via Ultramsg', ['to' => $to]);
                return true;
            }

            Log::error('Failed to send WhatsApp image via Ultramsg', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return false;
        } catch (Exception $e) {
            Log::error('WhatsApp Ultramsg image exception: ' . $e->getMessage());
            return false;
        }
    }

    public function sendDocument(string $to, string $documentUrl, string $filename): bool
    {
        if (!$this->isConfigured()) {
            Log::error('Ultramsg WhatsApp provider is not configured');
            return false;
        }

        try {
            $response = Http::post("{$this->baseUrl}/{$this->instanceId}/messages/document", [
                'token' => $this->token,
                'to' => $to,
                'document' => $documentUrl,
                'filename' => $filename,
                'priority' => 10,
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp document sent successfully via Ultramsg', ['to' => $to]);
                return true;
            }

            Log::error('Failed to send WhatsApp document via Ultramsg', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return false;
        } catch (Exception $e) {
            Log::error('WhatsApp Ultramsg document exception: ' . $e->getMessage());
            return false;
        }
    }

    public function getName(): string
    {
        return 'Ultramsg';
    }

    public function isConfigured(): bool
    {
        return !empty($this->instanceId) && !empty($this->token);
    }
}

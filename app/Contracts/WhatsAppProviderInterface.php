<?php

namespace App\Contracts;

interface WhatsAppProviderInterface
{
    /**
     * Send WhatsApp message
     *
     * @param string $to Phone number
     * @param string $message Message content
     * @return bool Success status
     */
    public function send(string $to, string $message): bool;

    /**
     * Send WhatsApp message with image
     *
     * @param string $to Phone number
     * @param string $message Message content
     * @param string $imageUrl Image URL
     * @return bool Success status
     */
    public function sendImage(string $to, string $message, string $imageUrl): bool;

    /**
     * Send WhatsApp message with document
     *
     * @param string $to Phone number
     * @param string $documentUrl Document URL
     * @param string $filename Filename
     * @return bool Success status
     */
    public function sendDocument(string $to, string $documentUrl, string $filename): bool;

    /**
     * Get provider name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if provider is configured
     *
     * @return bool
     */
    public function isConfigured(): bool;
}

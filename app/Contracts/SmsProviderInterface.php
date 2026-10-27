<?php

namespace App\Contracts;

interface SmsProviderInterface
{
    /**
     * Send SMS message
     *
     * @param string $to Phone number
     * @param string $message Message content
     * @return bool Success status
     */
    public function send(string $to, string $message): bool;

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

<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Exception;

class DataEncryptionService
{
    /**
     * Encrypt sensitive data
     */
    public function encrypt(mixed $data): string
    {
        try {
            return Crypt::encryptString(json_encode($data));
        } catch (Exception $e) {
            throw new Exception('فشل تشفير البيانات: ' . $e->getMessage());
        }
    }

    /**
     * Decrypt sensitive data
     */
    public function decrypt(string $encryptedData): mixed
    {
        try {
            return json_decode(Crypt::decryptString($encryptedData), true);
        } catch (Exception $e) {
            throw new Exception('فشل فك تشفير البيانات: ' . $e->getMessage());
        }
    }

    /**
     * Encrypt national ID
     */
    public function encryptNationalId(string $nationalId): string
    {
        return $this->encrypt($nationalId);
    }

    /**
     * Decrypt national ID
     */
    public function decryptNationalId(string $encryptedNationalId): string
    {
        return $this->decrypt($encryptedNationalId);
    }

    /**
     * Encrypt phone number
     */
    public function encryptPhone(string $phone): string
    {
        return $this->encrypt($phone);
    }

    /**
     * Decrypt phone number
     */
    public function decryptPhone(string $encryptedPhone): string
    {
        return $this->decrypt($encryptedPhone);
    }

    /**
     * Encrypt medical notes
     */
    public function encryptMedicalNotes(string $notes): string
    {
        return $this->encrypt($notes);
    }

    /**
     * Decrypt medical notes
     */
    public function decryptMedicalNotes(string $encryptedNotes): string
    {
        return $this->decrypt($encryptedNotes);
    }

    /**
     * Hash sensitive data (one-way)
     */
    public function hash(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * Verify hashed data
     */
    public function verifyHash(string $data, string $hash): bool
    {
        return hash_equals($this->hash($data), $hash);
    }

    /**
     * Mask sensitive data for display
     */
    public function mask(string $data, int $visibleChars = 4, string $maskChar = '*'): string
    {
        $length = strlen($data);
        
        if ($length <= $visibleChars) {
            return str_repeat($maskChar, $length);
        }

        $masked = str_repeat($maskChar, $length - $visibleChars);
        $visible = substr($data, -$visibleChars);

        return $masked . $visible;
    }

    /**
     * Mask phone number
     */
    public function maskPhone(string $phone): string
    {
        return $this->mask($phone, 4);
    }

    /**
     * Mask national ID
     */
    public function maskNationalId(string $nationalId): string
    {
        return $this->mask($nationalId, 3);
    }

    /**
     * Mask email
     */
    public function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        
        if (count($parts) !== 2) {
            return $email;
        }

        $username = $parts[0];
        $domain = $parts[1];

        $visibleChars = min(3, strlen($username));
        $maskedUsername = $this->mask($username, $visibleChars);

        return $maskedUsername . '@' . $domain;
    }
}

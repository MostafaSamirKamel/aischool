<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate secret key for user
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Get QR code URL
     */
    public function getQRCodeUrl(User $user, string $secret): string
    {
        $companyName = config('app.name', 'Laravel');
        
        return $this->google2fa->getQRCodeUrl(
            $companyName,
            $user->email,
            $secret
        );
    }

    /**
     * Verify OTP code
     */
    public function verifyCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Enable 2FA for user
     */
    public function enableFor(User $user, string $code): bool
    {
        if (!$user->two_factor_secret) {
            return false;
        }

        // Verify the code first
        if (!$this->verifyCode($user->two_factor_secret, $code)) {
            return false;
        }

        // Generate recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        return true;
    }

    /**
     * Disable 2FA for user
     */
    public function disableFor(User $user): void
    {
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    /**
     * Generate recovery codes
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = $this->generateRecoveryCode();
        }

        return $codes;
    }

    /**
     * Generate single recovery code
     */
    protected function generateRecoveryCode(): string
    {
        return strtoupper(bin2hex(random_bytes(5)));
    }

    /**
     * Get recovery codes for user
     */
    public function getRecoveryCodes(User $user): array
    {
        if (!$user->two_factor_recovery_codes) {
            return [];
        }

        return json_decode(decrypt($user->two_factor_recovery_codes), true);
    }

    /**
     * Use recovery code
     */
    public function useRecoveryCode(User $user, string $code): bool
    {
        $recoveryCodes = $this->getRecoveryCodes($user);

        if (!in_array($code, $recoveryCodes)) {
            return false;
        }

        // Remove used code
        $recoveryCodes = array_diff($recoveryCodes, [$code]);

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode(array_values($recoveryCodes))),
        ]);

        return true;
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        return $recoveryCodes;
    }

    /**
     * Check if user has 2FA enabled
     */
    public function isEnabled(User $user): bool
    {
        return $user->two_factor_enabled && $user->two_factor_secret;
    }

    /**
     * Verify 2FA code or recovery code
     */
    public function verify(User $user, string $code): bool
    {
        // Try OTP code first
        if ($this->verifyCode($user->two_factor_secret, $code)) {
            return true;
        }

        // Try recovery code
        return $this->useRecoveryCode($user, $code);
    }
}

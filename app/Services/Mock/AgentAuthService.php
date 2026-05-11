<?php

declare(strict_types=1);

namespace App\Services\Mock;

use Illuminate\Support\Str;

class AgentAuthService
{
    private const AGENT_PHONE_COUNTRY_CODE = '269';
    private const AGENT_PHONE_NUMBER       = '3201456';

    public function requestOtp(string $phoneCountryCode, string $phoneNumber): array|false
    {
        if ($phoneCountryCode !== self::AGENT_PHONE_COUNTRY_CODE || $phoneNumber !== self::AGENT_PHONE_NUMBER) {
            return false;
        }

        return [
            'challengeId' => (string) Str::uuid(),
            'expiresAt'   => now()->addMinutes(5)->toIso8601ZuluString(),
        ];
    }

    public function verifyOtp(string $challengeId, string $otpCode): bool
    {
        return (bool) preg_match('/^\d{6}$/', $otpCode);
    }
}

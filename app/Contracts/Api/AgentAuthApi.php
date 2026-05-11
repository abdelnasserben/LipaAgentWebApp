<?php

declare(strict_types=1);

namespace App\Contracts\Api;

interface AgentAuthApi
{
    /**
     * Request an OTP challenge for the given phone number.
     *
     * @return array{challengeId: string, expiresAt: string}|false
     */
    public function requestOtp(string $phoneCountryCode, string $phoneNumber): array|false;

    public function verifyOtp(string $challengeId, string $otpCode): bool;
}

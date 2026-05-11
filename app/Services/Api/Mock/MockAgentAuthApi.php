<?php

declare(strict_types=1);

namespace App\Services\Api\Mock;

use App\Contracts\Api\AgentAuthApi;
use App\Services\Api\Support\FixtureLoader;
use Illuminate\Support\Str;

final class MockAgentAuthApi implements AgentAuthApi
{
    public function requestOtp(string $phoneCountryCode, string $phoneNumber): array|false
    {
        $profile = FixtureLoader::load('agent/profile');

        if ($phoneCountryCode !== ($profile['phoneCountryCode'] ?? null)
            || $phoneNumber !== ($profile['phoneNumber'] ?? null)) {
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

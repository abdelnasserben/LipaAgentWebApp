<?php

declare(strict_types=1);

namespace App\Services\Api\Mock;

use App\Contracts\Api\AgentAuthApi;
use App\Exceptions\AgentAuthException;
use App\Services\Api\Support\FixtureLoader;
use Illuminate\Support\Str;

final class MockAgentAuthApi implements AgentAuthApi
{
    private const VALID_PIN = '1234';

    private const MFA_PIN = '4321';

    private const VALID_TOTP = '123456';

    public function login(string $phoneCountryCode, string $phoneNumber, string $pin): array
    {
        $profile = FixtureLoader::load('agent/profile');

        if ($phoneCountryCode !== ($profile['phoneCountryCode'] ?? null)
            || $phoneNumber !== ($profile['phoneNumber'] ?? null)) {
            throw new AgentAuthException('INVALID_CREDENTIALS', 401);
        }

        if ($pin === '9999') {
            throw new AgentAuthException('AUTH_PIN_NOT_SET', 422);
        }

        if ($pin === '0000') {
            throw new AgentAuthException('AUTH_PIN_LOCKED', 422);
        }

        if ($pin === self::MFA_PIN) {
            return [
                'mfaRequired' => true,
                'challengeId' => (string) Str::uuid(),
                'mfaFactor' => 'TOTP',
            ];
        }

        if ($pin !== self::VALID_PIN) {
            throw new AgentAuthException('INVALID_CREDENTIALS', 401);
        }

        return [
            'mfaRequired' => false,
            'tokens' => $this->tokens(),
        ];
    }

    public function verifyMfa(string $challengeId, string $code): array
    {
        if ($challengeId === '' || $code !== self::VALID_TOTP) {
            throw new AgentAuthException('MFA_INVALID', 401);
        }

        return [
            'mfaRequired' => false,
            'tokens' => $this->tokens(),
        ];
    }

    /**
     * @return array{accessToken: string, accessTokenExpiresAt: string, refreshToken: string, refreshTokenExpiresAt: string}
     */
    private function tokens(): array
    {
        return [
            'accessToken' => 'mock-agent-access-token',
            'accessTokenExpiresAt' => now()->addHours(8)->toIso8601ZuluString(),
            'refreshToken' => 'mock-agent-refresh-token',
            'refreshTokenExpiresAt' => now()->addDays(30)->toIso8601ZuluString(),
        ];
    }
}

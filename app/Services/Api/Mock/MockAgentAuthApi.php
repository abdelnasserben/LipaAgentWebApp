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

    private const PIN_SETUP_TOKEN = 'mock-agent-pin-setup-token';

    public function login(string $phoneCountryCode, string $phoneNumber, string $pin): array
    {
        $profile = FixtureLoader::load('agent/profile');

        if ($phoneCountryCode !== ($profile['phoneCountryCode'] ?? null)
            || $phoneNumber !== ($profile['phoneNumber'] ?? null)) {
            throw new AgentAuthException('INVALID_CREDENTIALS', 401);
        }

        if ($pin === '9999') {
            return [
                'mfaRequired' => false,
                'pinSetupRequired' => true,
                'pinSetupToken' => self::PIN_SETUP_TOKEN,
                'pinSetupTokenExpiresAt' => now()->addMinutes(10)->toIso8601ZuluString(),
            ];
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

    public function setupAuthPin(string $pinSetupToken, string $pin): void
    {
        if ($pinSetupToken !== self::PIN_SETUP_TOKEN) {
            throw new AgentAuthException('AUTH_INVALID_TOKEN', 401);
        }

        if (preg_match('/^\d{4,8}$/', $pin) !== 1) {
            throw new AgentAuthException('AUTH_PIN_FORMAT', 422);
        }
    }

    public function resetAuthPin(string $phoneCountryCode, string $phoneNumber, string $totpCode, string $newPin): void
    {
        $profile = FixtureLoader::load('agent/profile');

        $phoneMatches = $phoneCountryCode === ($profile['phoneCountryCode'] ?? null)
            && $phoneNumber === ($profile['phoneNumber'] ?? null);

        // Mock convention: TOTP enrollment is signalled by phone "0000000" — return TOTP_REQUIRED for it.
        if ($phoneMatches && $phoneNumber === '0000000') {
            throw new AgentAuthException('AUTH_PIN_RESET_TOTP_REQUIRED', 422);
        }

        if (preg_match('/^\d{4,8}$/', $newPin) !== 1) {
            throw new AgentAuthException('AUTH_PIN_FORMAT', 422);
        }

        // Anti-enumeration: unknown phone OR wrong TOTP both return AUTH_MFA_INVALID.
        if (! $phoneMatches || $totpCode !== self::VALID_TOTP) {
            throw new AgentAuthException('AUTH_MFA_INVALID', 401);
        }
    }

    public function logout(): void
    {
        // No-op in mock mode.
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

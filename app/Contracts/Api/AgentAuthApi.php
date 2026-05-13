<?php

declare(strict_types=1);

namespace App\Contracts\Api;

interface AgentAuthApi
{
    /**
     * Authenticate an Agent with the PIN-first flow.
     *
     * @return array{
     *     mfaRequired: bool,
     *     pinSetupRequired?: bool,
     *     pinSetupToken?: string,
     *     pinSetupTokenExpiresAt?: string,
     *     challengeId?: string,
     *     mfaFactor?: string,
     *     tokens?: array{
     *         accessToken: string,
     *         accessTokenExpiresAt: string,
     *         refreshToken: string,
     *         refreshTokenExpiresAt: string
     *     }
     * }
     */
    public function login(string $phoneCountryCode, string $phoneNumber, string $pin): array;

    /**
     * POST /api/v1/auth/agent/auth-pin/setup — consume the single-use `pinSetupToken`
     * returned by login when `pinSetupRequired=true` to define the initial Agent PIN.
     * Returns 204 No Content.
     */
    public function setupAuthPin(string $pinSetupToken, string $pin): void;

    /**
     * Verify the TOTP challenge returned by the PIN-first login flow.
     *
     * @return array{
     *     mfaRequired: bool,
     *     tokens?: array{
     *         accessToken: string,
     *         accessTokenExpiresAt: string,
     *         refreshToken: string,
     *         refreshTokenExpiresAt: string
     *     }
     * }
     */
    public function verifyMfa(string $challengeId, string $code): array;

    /**
     * POST /api/v1/auth/agent/logout — revoke the current access token and active refresh tokens.
     * Returns 204 No Content. Implementations should swallow auth failures so logout never blocks.
     */
    public function logout(): void;
}

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
}

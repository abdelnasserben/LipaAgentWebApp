<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\AgentAuthApi;

final class HttpAgentAuthApi implements AgentAuthApi
{
    public function __construct(private readonly KomopayClient $client)
    {
    }

    public function requestOtp(string $phoneCountryCode, string $phoneNumber): array|false
    {
        // TODO: POST /v1/agents/auth/otp/request {phoneCountryCode, phoneNumber}
        $response = $this->client->post('/v1/agents/auth/otp/request', [
            'phoneCountryCode' => $phoneCountryCode,
            'phoneNumber'      => $phoneNumber,
        ]);

        if ($response->status() === 404 || $response->status() === 403) {
            return false;
        }

        $response->throw();

        /** @var array{challengeId: string, expiresAt: string} */
        return $response->json();
    }

    public function verifyOtp(string $challengeId, string $otpCode): bool
    {
        // TODO: POST /v1/agents/auth/otp/verify {challengeId, otpCode}
        $response = $this->client->post('/v1/agents/auth/otp/verify', [
            'challengeId' => $challengeId,
            'otpCode'     => $otpCode,
        ]);

        return $response->successful();
    }
}

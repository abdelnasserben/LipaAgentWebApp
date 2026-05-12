<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\AgentAuthApi;
use App\Exceptions\ApiException;
use App\Exceptions\AgentAuthException;
use Illuminate\Http\Client\Response;

final class HttpAgentAuthApi implements AgentAuthApi
{
    public function __construct(private readonly KomopayClient $client) {}

    public function login(string $phoneCountryCode, string $phoneNumber, string $pin): array
    {
        $response = $this->client->post('/api/v1/auth/agent/login', [
            'phoneCountryCode' => $phoneCountryCode,
            'phoneNumber' => $phoneNumber,
            'pin' => $pin,
        ], [], false);

        if ($response->failed()) {
            throw $this->exceptionFromResponse($response, 'INVALID_CREDENTIALS');
        }

        return $this->loginData($response);
    }

    public function verifyMfa(string $challengeId, string $code): array
    {
        $response = $this->client->post('/api/v1/auth/agent/login/verify-mfa', [
            'challengeId' => $challengeId,
            'code' => $code,
        ], [], false);

        if ($response->failed()) {
            throw $this->exceptionFromResponse($response, 'MFA_INVALID');
        }

        return $this->loginData($response);
    }

    public function logout(): void
    {
        try {
            $this->client->post('/api/v1/auth/agent/logout', [], [], true);
        } catch (ApiException) {
            // Swallow — local session is cleared regardless of API outcome.
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function loginData(Response $response): array
    {
        try {
            return $this->client->data($response, 'INVALID_RESPONSE');
        } catch (ApiException $exception) {
            throw AgentAuthException::fromApiException($exception);
        }
    }

    private function exceptionFromResponse(Response $response, string $fallbackCode): AgentAuthException
    {
        return AgentAuthException::fromApiException(
            $this->client->exceptionFromResponse($response, $this->authFallbackErrorCode($response, $fallbackCode))
        );
    }

    private function authFallbackErrorCode(Response $response, string $fallbackCode): string
    {
        return match ($response->status()) {
            400 => 'VALIDATION_FIELD_REQUIRED',
            410 => 'LEGACY_OTP_LOGIN_REMOVED',
            404, 405 => 'AUTH_ENDPOINT_NOT_FOUND',
            429 => 'TERMINAL_RATE_LIMIT',
            default => $fallbackCode,
        };
    }
}

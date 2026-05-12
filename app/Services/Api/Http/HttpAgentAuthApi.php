<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\AgentAuthApi;
use App\Exceptions\AgentAuthException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

final class HttpAgentAuthApi implements AgentAuthApi
{
    public function __construct(private readonly KomopayClient $client) {}

    public function login(string $phoneCountryCode, string $phoneNumber, string $pin): array
    {
        $response = $this->client->post('/api/v1/auth/agent/login', [
            'phoneCountryCode' => $phoneCountryCode,
            'phoneNumber' => $phoneNumber,
            'pin' => $pin,
        ]);

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
        ]);

        if ($response->failed()) {
            throw $this->exceptionFromResponse($response, 'MFA_INVALID');
        }

        return $this->loginData($response);
    }

    /**
     * @return array<string, mixed>
     */
    private function loginData(Response $response): array
    {
        $json = $response->json();

        if (! is_array($json)) {
            throw new AgentAuthException('INVALID_RESPONSE', $response->status());
        }

        $data = $json['data'] ?? $json;

        if (! is_array($data)) {
            throw new AgentAuthException('INVALID_RESPONSE', $response->status());
        }

        return $data;
    }

    private function exceptionFromResponse(Response $response, string $fallbackCode): AgentAuthException
    {
        $json = $response->json();
        $error = is_array($json) ? ($json['error'] ?? $json) : [];

        $apiCode = is_array($error) && is_string($error['code'] ?? null)
            ? $error['code']
            : $this->fallbackErrorCode($response, $fallbackCode);

        $apiMessage = is_array($error) && is_string($error['message'] ?? null)
            ? $error['message']
            : '';

        Log::warning('Agent auth API request failed.', [
            'status' => $response->status(),
            'code' => $apiCode,
            'message' => $apiMessage,
            'base_url' => config('komopay.base_url'),
        ]);

        return new AgentAuthException($apiCode, $response->status(), $apiMessage);
    }

    private function fallbackErrorCode(Response $response, string $fallbackCode): string
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

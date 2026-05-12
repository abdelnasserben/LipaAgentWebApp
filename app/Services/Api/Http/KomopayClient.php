<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Exceptions\ApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around Laravel's HTTP client, configured for the Komopay API.
 *
 * HTTP implementations of `App\Contracts\Api\*` should depend on this class
 * and call its helpers rather than hitting `Http::*` directly — keeping base
 * URL, auth headers, and error handling in one place.
 */
final class KomopayClient
{
    public function request(bool $withAuth = true): PendingRequest
    {
        $client = Http::baseUrl((string) config('komopay.base_url'))
            ->timeout((int) config('komopay.timeout', 15))
            ->acceptJson()
            ->asJson();

        $token = $withAuth ? $this->bearerToken() : null;
        if ($token !== null) {
            $client = $client->withToken($token);
        }

        return $client;
    }

    public function get(string $path, array $query = [], array $headers = [], bool $withAuth = true): Response
    {
        return $this->send(
            fn (): Response => $this->request($withAuth)->withHeaders($headers)->get($path, $query),
            'GET',
            $path,
        );
    }

    public function post(string $path, array $body = [], array $headers = [], bool $withAuth = true): Response
    {
        return $this->send(
            fn (): Response => $this->request($withAuth)->withHeaders($headers)->post($path, $body),
            'POST',
            $path,
        );
    }

    public function put(string $path, array $body = [], array $headers = [], bool $withAuth = true): Response
    {
        return $this->send(
            fn (): Response => $this->request($withAuth)->withHeaders($headers)->put($path, $body),
            'PUT',
            $path,
        );
    }

    public function delete(string $path, array $body = [], array $headers = [], bool $withAuth = true): Response
    {
        return $this->send(
            fn (): Response => $this->request($withAuth)->withHeaders($headers)->delete($path, $body),
            'DELETE',
            $path,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function data(Response $response, string $fallbackCode = 'API_ERROR'): array
    {
        if ($response->failed()) {
            throw $this->exceptionFromResponse($response, $fallbackCode);
        }

        if ($response->status() === 204) {
            return [];
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new ApiException('INVALID_RESPONSE', $response->status());
        }

        $data = array_key_exists('data', $json) ? $json['data'] : $json;
        if (! is_array($data)) {
            throw new ApiException('INVALID_RESPONSE', $response->status());
        }

        return $data;
    }

    /**
     * @return array{data: array<int, array<string, mixed>>, pagination: array<string, mixed>}
     */
    public function paged(Response $response, string $fallbackCode = 'API_ERROR'): array
    {
        if ($response->failed()) {
            throw $this->exceptionFromResponse($response, $fallbackCode);
        }

        $json = $response->json();
        if (! is_array($json) || ! is_array($json['data'] ?? null)) {
            throw new ApiException('INVALID_RESPONSE', $response->status());
        }

        $pagination = is_array($json['pagination'] ?? null)
            ? $json['pagination']
            : ['nextCursor' => null, 'hasMore' => false, 'limit' => count($json['data'])];

        return [
            'data' => $json['data'],
            'pagination' => $pagination,
        ];
    }

    public function exceptionFromResponse(Response $response, string $fallbackCode = 'API_ERROR'): ApiException
    {
        $error = $this->errorPayload($response);

        $apiCode = is_string($error['code'] ?? null)
            ? $error['code']
            : $this->fallbackErrorCode($response, $fallbackCode);

        $apiMessage = is_string($error['message'] ?? null)
            ? $error['message']
            : '';

        $details = is_array($error['details'] ?? null)
            ? array_values($error['details'])
            : [];

        $correlationId = is_string($error['correlationId'] ?? null)
            ? $error['correlationId']
            : null;

        Log::warning('Komopay API request failed.', [
            'status' => $response->status(),
            'code' => $apiCode,
            'message' => $apiMessage,
            'correlation_id' => $correlationId,
            'base_url' => config('komopay.base_url'),
        ]);

        return new ApiException($apiCode, $response->status(), $apiMessage, $details, $correlationId);
    }

    private function bearerToken(): ?string
    {
        $sessionToken = session('agent_access_token');
        if (is_string($sessionToken) && $sessionToken !== '') {
            return $sessionToken;
        }

        $apiKey = config('komopay.api_key');

        return is_string($apiKey) && $apiKey !== '' ? $apiKey : null;
    }

    /**
     * @param  callable(): Response  $callback
     */
    private function send(callable $callback, string $method, string $path): Response
    {
        try {
            return $callback();
        } catch (ConnectionException $exception) {
            Log::warning('Komopay API connection failed.', [
                'method' => $method,
                'path' => $path,
                'base_url' => config('komopay.base_url'),
                'message' => $exception->getMessage(),
            ]);

            throw new ApiException('API_CONNECTION_FAILED', 0, '', [], null, $exception);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function errorPayload(Response $response): array
    {
        $json = $response->json();
        if (! is_array($json)) {
            return [];
        }

        $error = $json['error'] ?? $json;

        return is_array($error) ? $error : [];
    }

    private function fallbackErrorCode(Response $response, string $fallbackCode): string
    {
        return match ($response->status()) {
            400 => 'VALIDATION_ERROR',
            401 => in_array($fallbackCode, ['INVALID_CREDENTIALS', 'MFA_INVALID', 'AUTH_PIN_INVALID'], true)
                ? $fallbackCode
                : 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => $fallbackCode !== 'API_ERROR' ? $fallbackCode : 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            409 => 'DUPLICATE_IDEMPOTENCY_KEY',
            422 => $fallbackCode !== 'API_ERROR' ? $fallbackCode : 'BUSINESS_RULE_FAILED',
            429 => 'TERMINAL_RATE_LIMIT',
            500, 502, 503, 504 => 'API_SERVER_ERROR',
            default => $fallbackCode,
        };
    }
}

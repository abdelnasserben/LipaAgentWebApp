<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\OperationsApi;
use Illuminate\Support\Str;

final class HttpOperationsApi implements OperationsApi
{
    public function __construct(private readonly KomopayClient $client)
    {
    }

    public function lookupCustomer(string $phoneCountryCode, string $phoneNumber): ?array
    {
        $response = $this->client->get('/api/v1/agent/lookup', [
            'phoneCountryCode' => $phoneCountryCode,
            'phoneNumber'      => $phoneNumber,
        ]);

        if ($response->status() === 404) {
            $exception = $this->client->exceptionFromResponse($response, 'CUSTOMER_NOT_FOUND');

            if ($exception->apiCode() === 'CUSTOMER_NOT_FOUND') {
                return null;
            }

            throw $exception;
        }

        return $this->client->data($response, 'CUSTOMER_NOT_FOUND');
    }

    public function lookupMerchant(string $phoneCountryCode, string $phoneNumber): ?array
    {
        $response = $this->client->get('/api/v1/agent/merchants/lookup', [
            'phoneCountryCode' => $phoneCountryCode,
            'phoneNumber'      => $phoneNumber,
        ]);

        if ($response->status() === 404) {
            $exception = $this->client->exceptionFromResponse($response, 'MERCHANT_NOT_FOUND');

            if ($exception->apiCode() === 'MERCHANT_NOT_FOUND') {
                return null;
            }

            throw $exception;
        }

        return $this->client->data($response, 'MERCHANT_NOT_FOUND');
    }

    public function processCashIn(array $data): array
    {
        return $this->client->data(
            $this->client->post('/api/v1/agent/cash-in', $data, ['Idempotency-Key' => (string) Str::uuid()]),
            'VALIDATION_ERROR',
        );
    }

    public function processCashOut(array $data, string $idempotencyKey): array
    {
        $fallback = array_key_exists('merchantPin', $data) ? 'AUTH_PIN_INVALID' : 'VALIDATION_ERROR';

        $response = $this->client->post(
            '/api/v1/agent/cash-out',
            $data,
            ['Idempotency-Key' => $idempotencyKey],
        );
        $payload = $this->client->data($response, $fallback);

        if ($response->status() === 202) {
            return ['status' => 202, 'data' => $payload];
        }

        return $payload;
    }
}

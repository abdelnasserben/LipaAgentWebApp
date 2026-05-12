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

    public function processCashIn(array $data): array
    {
        return $this->client->data(
            $this->client->post('/api/v1/agent/cash-in', $data, $this->idempotencyHeaders()),
            'VALIDATION_ERROR',
        );
    }

    public function processCashOut(array $data): array
    {
        $response = $this->client->post('/api/v1/agent/cash-out', $data, $this->idempotencyHeaders());
        $data = $this->client->data($response, 'VALIDATION_ERROR');

        if ($response->status() === 202) {
            return ['status' => 202, 'data' => $data];
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function idempotencyHeaders(): array
    {
        return ['Idempotency-Key' => (string) Str::uuid()];
    }
}

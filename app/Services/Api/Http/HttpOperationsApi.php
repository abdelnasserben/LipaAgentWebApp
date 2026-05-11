<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\OperationsApi;

final class HttpOperationsApi implements OperationsApi
{
    public function __construct(private readonly KomopayClient $client)
    {
    }

    public function lookupCustomer(string $phoneCountryCode, string $phoneNumber): ?array
    {
        // TODO: GET /v1/customers/lookup?phoneCountryCode=&phoneNumber=
        $response = $this->client->get('/v1/customers/lookup', [
            'phoneCountryCode' => $phoneCountryCode,
            'phoneNumber'      => $phoneNumber,
        ]);

        if ($response->status() === 404) {
            return null;
        }

        return $response->throw()->json();
    }

    public function processCashIn(array $data): array
    {
        // TODO: POST /v1/operations/cash-in
        return $this->client->post('/v1/operations/cash-in', $data)->throw()->json();
    }

    public function processCashOut(array $data): array
    {
        // TODO: POST /v1/operations/cash-out (returns 202 when pending approval)
        $response = $this->client->post('/v1/operations/cash-out', $data)->throw();

        if ($response->status() === 202) {
            return ['status' => 202, 'data' => $response->json()];
        }

        return $response->json();
    }
}

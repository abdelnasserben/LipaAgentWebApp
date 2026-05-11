<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\EnrollApi;

final class HttpEnrollApi implements EnrollApi
{
    public function __construct(private readonly KomopayClient $client)
    {
    }

    public function enrollCustomer(array $data): array
    {
        // TODO: POST /v1/customers/enroll
        return $this->client->post('/v1/customers/enroll', $data)->throw()->json();
    }

    public function uploadKycDocument(string $customerId, array $data): array
    {
        // TODO: POST /v1/customers/{customerId}/kyc-documents
        return $this->client
            ->post('/v1/customers/' . urlencode($customerId) . '/kyc-documents', $data)
            ->throw()
            ->json();
    }
}

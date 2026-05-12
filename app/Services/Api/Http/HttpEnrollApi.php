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
        return $this->client->data(
            $this->client->post('/api/v1/agent/customers/enroll', $data),
            'VALIDATION_ERROR',
        );
    }

    public function uploadKycDocument(string $customerId, array $data): array
    {
        return $this->client->data(
            $this->client->post('/api/v1/agent/customers/'.urlencode($customerId).'/kyc-documents', $data),
            'VALIDATION_ERROR',
        );
    }
}

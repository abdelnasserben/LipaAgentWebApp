<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\EnrollApi;
use Illuminate\Http\UploadedFile;

final class HttpEnrollApi implements EnrollApi
{
    /** Fields accepted by EnrollCustomerRequest per the Agent spec. */
    private const ENROLL_FIELDS = [
        'fullName',
        'dateOfBirth',
        'phoneCountryCode',
        'phoneNumber',
        'nationalIdNumber',
        'nationalIdType',
        'addressIsland',
        'addressCity',
        'addressDistrict',
    ];

    public function __construct(private readonly KomopayClient $client)
    {
    }

    public function enrollCustomer(array $data): array
    {
        $payload = [];
        foreach (self::ENROLL_FIELDS as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }
            $value = $data[$field];
            if ($value === null || $value === '') {
                continue;
            }
            $payload[$field] = $value;
        }

        return $this->client->data(
            $this->client->post('/api/v1/agent/customers/enroll', $payload),
            'VALIDATION_ERROR',
        );
    }

    public function uploadKycDocument(string $customerId, string $documentType, UploadedFile $file): array
    {
        $response = $this->client->multipartRequest()
            ->attach('file', fopen($file->getRealPath(), 'r'), $file->getClientOriginalName())
            ->post(
                '/api/v1/agent/customers/'.urlencode($customerId).'/kyc-documents',
                ['documentType' => $documentType],
            );

        return $this->client->data($response, 'VALIDATION_ERROR');
    }

    public function listKycDocuments(string $customerId): array
    {
        $response = $this->client->get(
            '/api/v1/agent/customers/'.urlencode($customerId).'/kyc-documents',
        );
        $data = $this->client->data($response, 'CUSTOMER_NOT_FOUND');

        return array_is_list($data) ? $data : [];
    }
}

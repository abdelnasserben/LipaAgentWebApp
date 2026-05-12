<?php

declare(strict_types=1);

namespace App\Contracts\Api;

use Illuminate\Http\UploadedFile;

interface EnrollApi
{
    /**
     * POST /api/v1/agent/customers/enroll
     *
     * @param  array<string, mixed>  $data EnrollCustomerRequest fields only:
     *   fullName, dateOfBirth (YYYY-MM-DD), phoneCountryCode, phoneNumber,
     *   nationalIdNumber, nationalIdType,
     *   addressIsland?, addressCity?, addressDistrict?
     * @return array{customerId: string, externalRef: string, walletId: string}
     */
    public function enrollCustomer(array $data): array;

    /**
     * POST /api/v1/agent/customers/{customerId}/kyc-documents (multipart/form-data).
     *
     * @return array<string, mixed> KycDocumentResponse
     */
    public function uploadKycDocument(string $customerId, string $documentType, UploadedFile $file): array;

    /**
     * GET /api/v1/agent/customers/{customerId}/kyc-documents
     *
     * @return array<int, array<string, mixed>>
     */
    public function listKycDocuments(string $customerId): array;
}

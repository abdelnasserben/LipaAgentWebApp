<?php

declare(strict_types=1);

namespace App\Services\Api\Mock;

use App\Contracts\Api\EnrollApi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final class MockEnrollApi implements EnrollApi
{
    /** @var array<string, array<int, array<string, mixed>>> */
    private array $store = [];

    public function enrollCustomer(array $data): array
    {
        $seq = str_pad((string) random_int(100, 999), 5, '0', STR_PAD_LEFT);

        return [
            'customerId'  => (string) Str::uuid(),
            'externalRef' => 'CUST-' . $seq,
            'walletId'    => (string) Str::uuid(),
        ];
    }

    public function uploadKycDocument(string $customerId, string $documentType, UploadedFile $file): array
    {
        $doc = [
            'id'                  => (string) Str::uuid(),
            'ownerActorType'      => 'CUSTOMER',
            'ownerActorId'        => $customerId,
            'documentType'        => $documentType,
            'contentHash'         => hash('sha256', $file->getClientOriginalName() . microtime()),
            'uploadedByActorType' => 'AGENT',
            'uploadedByActorId'   => 'mock-agent',
            'uploadedAt'          => now()->toIso8601ZuluString(),
            'status'              => 'PENDING_REVIEW',
        ];

        $this->store[$customerId][] = $doc;

        return $doc;
    }

    public function listKycDocuments(string $customerId): array
    {
        return $this->store[$customerId] ?? [];
    }
}

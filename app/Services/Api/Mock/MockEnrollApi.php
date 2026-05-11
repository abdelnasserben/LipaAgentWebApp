<?php

declare(strict_types=1);

namespace App\Services\Api\Mock;

use App\Contracts\Api\EnrollApi;
use Illuminate\Support\Str;

final class MockEnrollApi implements EnrollApi
{
    public function enrollCustomer(array $data): array
    {
        $seq = str_pad((string) random_int(100, 999), 5, '0', STR_PAD_LEFT);

        return [
            'customerId'  => (string) Str::uuid(),
            'externalRef' => 'CUST-' . $seq,
            'walletId'    => (string) Str::uuid(),
        ];
    }

    public function uploadKycDocument(string $customerId, array $data): array
    {
        return [
            'id'            => (string) Str::uuid(),
            'customerId'    => $customerId,
            'documentType'  => $data['documentType'] ?? 'NATIONAL_ID',
            'status'        => 'PENDING_REVIEW',
            'uploadedAt'    => now()->toIso8601ZuluString(),
            'reviewedAt'    => null,
            'rejectionNote' => null,
        ];
    }
}

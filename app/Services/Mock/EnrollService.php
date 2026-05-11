<?php

declare(strict_types=1);

namespace App\Services\Mock;

use Illuminate\Support\Str;

class EnrollService
{
    public function enrollCustomer(array $data): array
    {
        $seq = str_pad((string) rand(100, 999), 5, '0', STR_PAD_LEFT);

        return [
            'customerId'  => (string) Str::uuid(),
            'externalRef' => 'CUST-' . $seq,
            'walletId'    => (string) Str::uuid(),
        ];
    }

    public function uploadKycDocument(string $customerId, array $data): array
    {
        return [
            'id'           => (string) Str::uuid(),
            'customerId'   => $customerId,
            'documentType' => $data['documentType'] ?? 'NATIONAL_ID',
            'status'       => 'PENDING_REVIEW',
            'uploadedAt'   => now()->toIso8601ZuluString(),
            'reviewedAt'   => null,
            'rejectionNote' => null,
        ];
    }
}

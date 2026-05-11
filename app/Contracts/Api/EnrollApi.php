<?php

declare(strict_types=1);

namespace App\Contracts\Api;

interface EnrollApi
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{customerId: string, externalRef: string, walletId: string}
     */
    public function enrollCustomer(array $data): array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function uploadKycDocument(string $customerId, array $data): array;
}

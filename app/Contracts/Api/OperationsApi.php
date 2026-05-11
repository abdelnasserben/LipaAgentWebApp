<?php

declare(strict_types=1);

namespace App\Contracts\Api;

interface OperationsApi
{
    /** @return array<string, mixed>|null */
    public function lookupCustomer(string $phoneCountryCode, string $phoneNumber): ?array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function processCashIn(array $data): array;

    /**
     * Process a cash-out request.
     *
     * Returns either the completed transaction payload directly, OR a wrapper
     * `['status' => 202, 'data' => array]` when the transaction is pending approval.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function processCashOut(array $data): array;
}

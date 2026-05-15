<?php

declare(strict_types=1);

namespace App\Contracts\Api;

interface OperationsApi
{
    /** @return array<string, mixed>|null */
    public function lookupCustomer(string $phoneCountryCode, string $phoneNumber): ?array;

    /**
     * GET /api/v1/agent/merchants/lookup — minimal merchant projection for pre-cash-out confirmation.
     *
     * @return array<string, mixed>|null MerchantLookupResponse, or null when MERCHANT_NOT_FOUND.
     */
    public function lookupMerchant(string $phoneCountryCode, string $phoneNumber): ?array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function processCashIn(array $data): array;

    /**
     * Process a cash-out request.
     *
     * Returns either the completed transaction payload directly, OR a wrapper
     * `['status' => 202, 'data' => array]` when the cash-out is paused at a
     * control tier (PENDING_PIN / PENDING_CONFIRMATION / PENDING_APPROVAL).
     *
     * The same `$idempotencyKey` must be reused for PENDING_PIN / PENDING_CONFIRMATION
     * resubmissions, carrying `merchantPin` or `confirmationAcknowledged=true`.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function processCashOut(array $data, string $idempotencyKey): array;
}

<?php

declare(strict_types=1);

namespace App\Contracts\Api;

interface CardApi
{
    /**
     * GET /api/v1/agent/card-stock — returns the Agent's assigned card stock.
     *
     * @return array<int, array<string, mixed>> AgentCardStockView entries.
     */
    public function getCardStock(): array;

    /**
     * POST /api/v1/agent/card-sell — sell an assigned NFC card to a customer.
     *
     * @param  array{customerId: string, nfcUid: string, cardPrice: int}  $data
     * @return array<string, mixed> AgentCardSaleResponse
     */
    public function sellCard(array $data): array;

    /**
     * POST /api/v1/agent/customers/{customerId}/cards/{cardId}/report-lost
     * Idempotent when the card is already LOST.
     *
     * @return array<string, mixed> CardResponse
     */
    public function reportLost(string $customerId, string $cardId): array;

    /**
     * POST /api/v1/agent/customers/{customerId}/cards/{cardId}/report-stolen
     * Idempotent when the card is already STOLEN.
     *
     * @return array<string, mixed> CardResponse
     */
    public function reportStolen(string $customerId, string $cardId): array;

    /**
     * POST /api/v1/agent/customers/{customerId}/cards/{cardId}/replace
     * Consumes an Agent-assigned stock and charges replacementFee. Requires Idempotency-Key.
     *
     * @param  array{stockId: string, replacementFee: int}  $data
     * @return array<string, mixed> AgentCardReplacementResponse
     */
    public function replaceCard(string $customerId, string $cardId, array $data): array;
}

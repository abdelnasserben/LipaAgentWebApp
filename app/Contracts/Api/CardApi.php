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
}

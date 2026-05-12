<?php

declare(strict_types=1);

namespace App\Contracts\Api;

interface CommissionApi
{
    /**
     * GET /api/v1/agent/commissions
     *
     * @param  array<string, mixed>  $filters Accepts cursor, limit, status (PayoutStatus)
     * @return array{data: array<int, array<string, mixed>>, pagination: array<string, mixed>}
     */
    public function getCommissions(array $filters = []): array;

    /**
     * Client-side summary derived from /api/v1/agent/commissions.
     * No dedicated summary endpoint exists in the spec.
     *
     * @return array{pendingTotal: int, todayEarned: int, weekEarned: int, monthEarned: int}
     */
    public function getSummary(): array;
}

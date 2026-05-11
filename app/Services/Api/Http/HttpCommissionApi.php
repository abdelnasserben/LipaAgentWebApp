<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\CommissionApi;

final class HttpCommissionApi implements CommissionApi
{
    public function __construct(private readonly KomopayClient $client)
    {
    }

    public function getCommissions(array $filters = []): array
    {
        // TODO: GET /v1/commissions?status=&type=&cursor=
        return $this->client->get('/v1/commissions', array_filter($filters))->throw()->json();
    }

    public function getSummary(): array
    {
        // TODO: GET /v1/commissions/summary
        return $this->client->get('/v1/commissions/summary')->throw()->json();
    }
}

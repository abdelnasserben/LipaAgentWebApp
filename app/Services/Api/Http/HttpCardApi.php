<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\CardApi;

final class HttpCardApi implements CardApi
{
    public function __construct(private readonly KomopayClient $client)
    {
    }

    public function getCardStock(): array
    {
        $result = $this->client->paged($this->client->get('/api/v1/agent/card-stock'), 'FORBIDDEN');

        return $result['data'];
    }
}

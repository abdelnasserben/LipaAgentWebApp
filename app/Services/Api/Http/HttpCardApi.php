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
        // TODO: GET /v1/agents/me/cards/stock
        return $this->client->get('/v1/agents/me/cards/stock')->throw()->json();
    }
}

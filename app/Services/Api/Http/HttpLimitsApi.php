<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\LimitsApi;

final class HttpLimitsApi implements LimitsApi
{
    public function __construct(private readonly KomopayClient $client)
    {
    }

    public function getLimits(): array
    {
        // TODO: GET /v1/agents/me/limits
        return $this->client->get('/v1/agents/me/limits')->throw()->json();
    }
}

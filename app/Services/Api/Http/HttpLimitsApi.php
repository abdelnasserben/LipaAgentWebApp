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
        return $this->client->data($this->client->get('/api/v1/agent/limits'), 'CONFIG_LIMIT_PROFILE_NOT_FOUND');
    }
}

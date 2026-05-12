<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\AgentApi;

final class HttpAgentApi implements AgentApi
{
    public function __construct(private readonly KomopayClient $client)
    {
    }

    public function getProfile(): array
    {
        return $this->client->data($this->client->get('/api/v1/agent/me'), 'ACTOR_NOT_FOUND');
    }

    public function getBalance(): array
    {
        return $this->client->data($this->client->get('/api/v1/agent/balance'), 'WALLET_NOT_FOUND');
    }

    public function getDailySummary(): array
    {
        return $this->client->data($this->client->get('/api/v1/agent/summary/daily'), 'ACTOR_NOT_FOUND');
    }
}

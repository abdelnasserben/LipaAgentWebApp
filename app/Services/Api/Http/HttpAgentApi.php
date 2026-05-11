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
        // TODO: GET /v1/agents/me
        return $this->client->get('/v1/agents/me')->throw()->json();
    }

    public function getBalance(): array
    {
        // TODO: GET /v1/agents/me/balance
        return $this->client->get('/v1/agents/me/balance')->throw()->json();
    }

    public function getDailySummary(): array
    {
        // TODO: GET /v1/agents/me/summary?period=today
        return $this->client->get('/v1/agents/me/summary', ['period' => 'today'])->throw()->json();
    }
}

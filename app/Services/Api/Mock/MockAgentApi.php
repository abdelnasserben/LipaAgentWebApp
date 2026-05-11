<?php

declare(strict_types=1);

namespace App\Services\Api\Mock;

use App\Contracts\Api\AgentApi;
use App\Services\Api\Support\FixtureLoader;

final class MockAgentApi implements AgentApi
{
    public function getProfile(): array
    {
        return FixtureLoader::load('agent/profile');
    }

    public function getBalance(): array
    {
        return FixtureLoader::load('agent/balance');
    }

    public function getDailySummary(): array
    {
        return FixtureLoader::load('agent/daily-summary');
    }
}

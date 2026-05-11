<?php

declare(strict_types=1);

namespace App\Contracts\Api;

interface AgentApi
{
    /** @return array<string, mixed> */
    public function getProfile(): array;

    /** @return array<string, mixed> */
    public function getBalance(): array;

    /** @return array<string, mixed> */
    public function getDailySummary(): array;
}

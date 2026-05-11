<?php

declare(strict_types=1);

namespace App\Services\Api\Mock;

use App\Contracts\Api\CommissionApi;
use App\Services\Api\Support\FixtureLoader;

final class MockCommissionApi implements CommissionApi
{
    public function getCommissions(array $filters = []): array
    {
        $fixture = FixtureLoader::load('commissions/list');
        $data    = $fixture['data'] ?? [];

        if (! empty($filters['status'])) {
            $data = array_values(array_filter(
                $data,
                static fn (array $e): bool => ($e['status'] ?? null) === $filters['status']
            ));
        }

        if (! empty($filters['type'])) {
            $data = array_values(array_filter(
                $data,
                static fn (array $e): bool => ($e['type'] ?? null) === $filters['type']
            ));
        }

        return [
            'data'       => $data,
            'pagination' => $fixture['pagination'] ?? ['nextCursor' => null, 'hasMore' => false, 'limit' => 20],
        ];
    }

    public function getSummary(): array
    {
        return FixtureLoader::load('commissions/summary');
    }
}

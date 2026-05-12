<?php

declare(strict_types=1);

namespace App\Services\Api\Mock;

use App\Contracts\Api\CommissionApi;
use App\Services\Api\Support\FixtureLoader;
use Carbon\CarbonImmutable;

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

        return [
            'data'       => $data,
            'pagination' => $fixture['pagination'] ?? ['nextCursor' => null, 'hasMore' => false, 'limit' => 20],
        ];
    }

    public function getSummary(): array
    {
        $now = CarbonImmutable::now();

        return array_reduce(
            $this->getCommissions()['data'],
            static function (array $summary, array $commission) use ($now): array {
                $amount = (int) ($commission['amount'] ?? 0);
                $status = (string) ($commission['status'] ?? '');
                $createdAt = CarbonImmutable::parse((string) ($commission['createdAt'] ?? 'now'));

                if ($status === 'PENDING') {
                    $summary['pendingTotal'] += $amount;
                }
                if ($createdAt->isSameDay($now)) {
                    $summary['todayEarned'] += $amount;
                }
                if ($createdAt->betweenIncluded($now->startOfWeek(), $now->endOfWeek())) {
                    $summary['weekEarned'] += $amount;
                }
                if ($createdAt->isSameMonth($now)) {
                    $summary['monthEarned'] += $amount;
                }

                return $summary;
            },
            ['pendingTotal' => 0, 'todayEarned' => 0, 'weekEarned' => 0, 'monthEarned' => 0],
        );
    }
}

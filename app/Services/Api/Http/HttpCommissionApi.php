<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\CommissionApi;
use Carbon\CarbonImmutable;

final class HttpCommissionApi implements CommissionApi
{
    public function __construct(private readonly KomopayClient $client)
    {
    }

    public function getCommissions(array $filters = []): array
    {
        return $this->client->paged(
            $this->client->get('/api/v1/agent/commissions', array_filter($filters)),
            'API_ERROR',
        );
    }

    public function getSummary(): array
    {
        return $this->summaryFrom($this->getCommissions()['data']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $commissions
     * @return array<string, int>
     */
    private function summaryFrom(array $commissions): array
    {
        $now = CarbonImmutable::now();

        return array_reduce($commissions, function (array $summary, array $commission) use ($now): array {
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
        }, [
            'pendingTotal' => 0,
            'todayEarned' => 0,
            'weekEarned' => 0,
            'monthEarned' => 0,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Api\Mock;

use App\Contracts\Api\TransactionApi;
use App\Services\Api\Support\FixtureLoader;

final class MockTransactionApi implements TransactionApi
{
    public function getTransactions(array $filters = []): array
    {
        $fixture = FixtureLoader::load('transactions/list');
        $data    = $fixture['data'] ?? [];

        if (! empty($filters['status'])) {
            $data = array_values(array_filter(
                $data,
                static fn (array $t): bool => ($t['status'] ?? null) === $filters['status']
            ));
        }

        if (! empty($filters['type'])) {
            $data = array_values(array_filter(
                $data,
                static fn (array $t): bool => ($t['type'] ?? null) === $filters['type']
            ));
        }

        return [
            'data'       => $data,
            'pagination' => $fixture['pagination'] ?? ['nextCursor' => null, 'hasMore' => false, 'limit' => 20],
        ];
    }

    public function getTransaction(string $id): ?array
    {
        $fixture = FixtureLoader::load('transactions/list');

        foreach ($fixture['data'] ?? [] as $transaction) {
            if (($transaction['id'] ?? null) === $id) {
                return $transaction;
            }
        }

        return null;
    }

    public function getStatements(array $filters = []): array
    {
        return FixtureLoader::load('transactions/statements');
    }
}

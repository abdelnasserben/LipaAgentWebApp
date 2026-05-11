<?php

declare(strict_types=1);

namespace App\Contracts\Api;

interface TransactionApi
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{data: array<int, array<string, mixed>>, pagination: array<string, mixed>}
     */
    public function getTransactions(array $filters = []): array;

    /** @return array<string, mixed>|null */
    public function getTransaction(string $id): ?array;

    /**
     * @param  array<string, mixed>  $filters
     * @return array{data: array<int, array<string, mixed>>, pagination: array<string, mixed>}
     */
    public function getStatements(array $filters = []): array;
}

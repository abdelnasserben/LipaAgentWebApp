<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\TransactionApi;

final class HttpTransactionApi implements TransactionApi
{
    public function __construct(private readonly KomopayClient $client)
    {
    }

    public function getTransactions(array $filters = []): array
    {
        // TODO: GET /v1/transactions?status=&type=&search=&cursor=
        return $this->client->get('/v1/transactions', array_filter($filters))->throw()->json();
    }

    public function getTransaction(string $id): ?array
    {
        // TODO: GET /v1/transactions/{id}
        $response = $this->client->get('/v1/transactions/' . urlencode($id));

        if ($response->status() === 404) {
            return null;
        }

        return $response->throw()->json();
    }

    public function getStatements(array $filters = []): array
    {
        // TODO: GET /v1/statements?from=&to=&cursor=
        return $this->client->get('/v1/statements', array_filter($filters))->throw()->json();
    }
}

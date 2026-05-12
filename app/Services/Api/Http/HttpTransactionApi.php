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
        return $this->client->paged(
            $this->client->get('/api/v1/agent/transactions', array_filter($filters)),
            'ACTOR_NOT_FOUND',
        );
    }

    public function getTransaction(string $id): ?array
    {
        $response = $this->client->get('/api/v1/agent/transactions/'.urlencode($id));

        if ($response->status() === 404) {
            $exception = $this->client->exceptionFromResponse($response, 'TRANSACTION_NOT_FOUND');

            if ($exception->apiCode() === 'TRANSACTION_NOT_FOUND') {
                return null;
            }

            throw $exception;
        }

        return $this->client->data($response, 'TRANSACTION_NOT_FOUND');
    }

    public function getStatements(array $filters = []): array
    {
        return $this->client->paged(
            $this->client->get('/api/v1/agent/statements', array_filter($filters)),
            'ACTOR_NOT_FOUND',
        );
    }
}

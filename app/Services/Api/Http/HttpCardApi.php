<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\CardApi;
use Illuminate\Support\Str;

final class HttpCardApi implements CardApi
{
    public function __construct(private readonly KomopayClient $client)
    {
    }

    public function getCardStock(): array
    {
        $result = $this->client->paged($this->client->get('/api/v1/agent/card-stock'), 'FORBIDDEN');

        return $result['data'];
    }

    public function lookupCard(string $nfcUid): ?array
    {
        $response = $this->client->get('/api/v1/agent/cards/lookup', [
            'nfcUid' => $nfcUid,
        ]);

        if ($response->status() === 404) {
            $exception = $this->client->exceptionFromResponse($response, 'CARD_NOT_FOUND');

            if ($exception->apiCode() === 'CARD_NOT_FOUND') {
                return null;
            }

            throw $exception;
        }

        return $this->client->data($response, 'CARD_NOT_FOUND');
    }

    public function sellCard(array $data): array
    {
        return $this->client->data(
            $this->client->post(
                '/api/v1/agent/card-sell',
                [
                    'customerId' => $data['customerId'],
                    'nfcUid'     => $data['nfcUid'],
                    'cardPrice'  => (int) $data['cardPrice'],
                ],
                ['Idempotency-Key' => (string) Str::uuid()],
            ),
            'VALIDATION_ERROR',
        );
    }

    public function reportLost(string $customerId, string $cardId): array
    {
        return $this->client->data(
            $this->client->post(
                "/api/v1/agent/customers/{$customerId}/cards/{$cardId}/report-lost",
            ),
            'CARD_NOT_FOUND',
        );
    }

    public function reportStolen(string $customerId, string $cardId): array
    {
        return $this->client->data(
            $this->client->post(
                "/api/v1/agent/customers/{$customerId}/cards/{$cardId}/report-stolen",
            ),
            'CARD_NOT_FOUND',
        );
    }

    public function replaceCard(string $customerId, string $cardId, array $data): array
    {
        return $this->client->data(
            $this->client->post(
                "/api/v1/agent/customers/{$customerId}/cards/{$cardId}/replace",
                [
                    'stockId'        => $data['stockId'],
                    'replacementFee' => (int) $data['replacementFee'],
                ],
                ['Idempotency-Key' => (string) Str::uuid()],
            ),
            'VALIDATION_ERROR',
        );
    }
}

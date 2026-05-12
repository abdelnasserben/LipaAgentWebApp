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
}

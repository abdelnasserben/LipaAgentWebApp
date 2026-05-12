<?php

declare(strict_types=1);

namespace App\Services\Api\Mock;

use App\Contracts\Api\CardApi;
use App\Services\Api\Support\FixtureLoader;
use Illuminate\Support\Str;

final class MockCardApi implements CardApi
{
    public function getCardStock(): array
    {
        return FixtureLoader::load('cards/stock');
    }

    public function sellCard(array $data): array
    {
        $price = (int) ($data['cardPrice'] ?? 0);

        return [
            'transactionId'    => (string) Str::uuid(),
            'status'           => 'COMPLETED',
            'cardId'           => (string) Str::uuid(),
            'customerId'       => (string) ($data['customerId'] ?? ''),
            'cardPrice'        => $price,
            'commissionAmount' => (int) floor($price * 0.05),
            'completedAt'      => now()->toIso8601ZuluString(),
            'replayed'         => false,
        ];
    }
}

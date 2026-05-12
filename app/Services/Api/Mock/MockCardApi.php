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

    public function reportLost(string $customerId, string $cardId): array
    {
        return $this->cardResponse($customerId, $cardId, 'LOST');
    }

    public function reportStolen(string $customerId, string $cardId): array
    {
        return $this->cardResponse($customerId, $cardId, 'STOLEN');
    }

    public function replaceCard(string $customerId, string $cardId, array $data): array
    {
        $fee = (int) ($data['replacementFee'] ?? 0);
        $newCardId = (string) Str::uuid();

        return [
            'transactionId'    => (string) Str::uuid(),
            'status'           => 'COMPLETED',
            'newCardId'        => $newCardId,
            'oldCardId'        => $cardId,
            'customerId'       => $customerId,
            'replacementFee'   => $fee,
            'commissionAmount' => (int) floor($fee * 0.05),
            'completedAt'      => now()->toIso8601ZuluString(),
            'replayed'         => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function cardResponse(string $customerId, string $cardId, string $status): array
    {
        return [
            'id'                  => $cardId,
            'nfcUid'              => null,
            'internalCardNumber'  => 'CARD-MOCK',
            'walletId'            => null,
            'customerId'          => $customerId,
            'cardType'            => 'STANDARD',
            'status'              => $status,
            'pinEnabled'          => false,
            'issuedByAgentId'     => null,
            'issuedAt'            => now()->subMonths(2)->toIso8601ZuluString(),
            'activatedAt'         => now()->subMonths(2)->toIso8601ZuluString(),
            'expiresAt'           => now()->addYears(2)->toDateString(),
            'lastUsedAt'          => null,
            'lastUsedTerminalId'  => null,
            'replacedByCardId'    => null,
            'replacementOfCardId' => null,
        ];
    }
}

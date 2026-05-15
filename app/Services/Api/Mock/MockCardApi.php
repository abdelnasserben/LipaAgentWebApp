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

    public function lookupCard(string $nfcUid): ?array
    {
        if (! preg_match('/^[0-9A-Fa-f]{14}$/', $nfcUid)) {
            return null;
        }

        $upper = strtoupper($nfcUid);

        // Match assigned stock first — those cards are not yet linked to a customer.
        foreach (FixtureLoader::load('cards/stock') as $stock) {
            if (strtoupper((string) ($stock['nfcUid'] ?? '')) === $upper) {
                $internalCardNumber = (string) ($stock['internalCardNumber'] ?? '');

                return [
                    'cardId'                   => (string) $stock['id'],
                    'nfcUid'                   => $upper,
                    'internalCardLast4'        => $this->internalCardLast4($internalCardNumber),
                    'maskedInternalCardNumber' => $this->maskedInternalCardNumber($internalCardNumber),
                    'cardType'                 => 'STANDARD',
                    'status'                   => 'ISSUED',
                    'customerId'               => null,
                    'customerFullName'         => null,
                    'customerPhoneMasked'      => null,
                    'expiresAt'                => null,
                ];
            }
        }

        // Deterministic mock: UIDs starting with "FF" are not found.
        if (str_starts_with($upper, 'FF')) {
            return null;
        }

        $known = FixtureLoader::load('customers/known');
        $customer = $known[abs(crc32($upper)) % max(1, count($known))] ?? null;
        $internalCardNumber = 'CARD-' . str_pad((string) (abs(crc32($upper)) % 100000), 5, '0', STR_PAD_LEFT);

        return [
            'cardId'                   => 'crd_' . substr(md5($upper), 0, 22),
            'nfcUid'                   => $upper,
            'internalCardLast4'        => $this->internalCardLast4($internalCardNumber),
            'maskedInternalCardNumber' => $this->maskedInternalCardNumber($internalCardNumber),
            'cardType'                 => 'STANDARD',
            'status'                   => 'ACTIVE',
            'customerId'               => is_array($customer) ? (string) ($customer['customerId'] ?? '') : null,
            'customerFullName'         => is_array($customer) ? (string) ($customer['fullName'] ?? '') : null,
            'customerPhoneMasked'      => $this->maskedCustomerPhone(is_array($customer) ? $customer : null),
            'expiresAt'                => now()->addYears(2)->toDateString(),
        ];
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
        $customer = $this->customerById($customerId);
        $internalCardNumber = 'CARD-' . str_pad((string) (abs(crc32($cardId)) % 100000), 5, '0', STR_PAD_LEFT);

        return [
            'cardId'                   => $cardId,
            'nfcUid'                   => strtoupper(substr(md5($cardId), 0, 14)),
            'internalCardLast4'        => $this->internalCardLast4($internalCardNumber),
            'maskedInternalCardNumber' => $this->maskedInternalCardNumber($internalCardNumber),
            'cardType'                 => 'STANDARD',
            'status'                   => $status,
            'customerId'               => $customerId,
            'customerFullName'         => is_array($customer) ? (string) ($customer['fullName'] ?? '') : null,
            'customerPhoneMasked'      => $this->maskedCustomerPhone($customer),
            'expiresAt'                => now()->addYears(2)->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function customerById(string $customerId): ?array
    {
        foreach (FixtureLoader::load('customers/known') as $customer) {
            if (($customer['customerId'] ?? null) === $customerId) {
                return $customer;
            }
        }

        return null;
    }

    private function internalCardLast4(?string $internalCardNumber): ?string
    {
        if ($internalCardNumber === null || $internalCardNumber === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $internalCardNumber) ?? '';

        if ($digits === '') {
            return substr($internalCardNumber, -4);
        }

        return substr(str_pad($digits, 4, '0', STR_PAD_LEFT), -4);
    }

    private function maskedInternalCardNumber(?string $internalCardNumber): ?string
    {
        $last4 = $this->internalCardLast4($internalCardNumber);

        return $last4 === null ? null : '**** ' . $last4;
    }

    /**
     * @param  array<string, mixed>|null  $customer
     */
    private function maskedCustomerPhone(?array $customer): ?string
    {
        if ($customer === null) {
            return null;
        }

        $countryCode = trim((string) ($customer['phoneCountryCode'] ?? ''));
        $phoneNumber = preg_replace('/\D+/', '', (string) ($customer['phoneNumber'] ?? '')) ?? '';

        if ($countryCode === '' || $phoneNumber === '') {
            return null;
        }

        return '+' . $countryCode . ' **** ' . substr($phoneNumber, -4);
    }
}

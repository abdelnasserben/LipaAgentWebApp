<?php

declare(strict_types=1);

namespace App\Services\Api\Mock;

use App\Contracts\Api\OperationsApi;
use App\Exceptions\ApiException;
use App\Services\Api\Support\FixtureLoader;
use Illuminate\Support\Str;

final class MockOperationsApi implements OperationsApi
{
    private const MOCK_MERCHANT_PIN = '1234';

    /** @var array<string, int> */
    private static array $mockPinAttempts = [];

    public function lookupCustomer(string $phoneCountryCode, string $phoneNumber): ?array
    {
        $known = FixtureLoader::load('customers/known');

        foreach ($known as $customer) {
            if (($customer['phoneNumber'] ?? null) === $phoneNumber) {
                if (($customer['phoneCountryCode'] ?? null) !== $phoneCountryCode) {
                    return null;
                }
                return $customer;
            }
        }

        if (! preg_match('/^\d{4,15}$/', $phoneNumber)) {
            return null;
        }

        $names     = FixtureLoader::load('customers/random-names');
        $nameIndex = (int) (hexdec(substr(md5($phoneNumber), 0, 4)) % count($names));
        $name      = $names[$nameIndex];

        return [
            'customerId'       => 'cust_' . strtoupper(substr(md5($phoneCountryCode . $phoneNumber), 0, 20)),
            'externalRef'      => 'CUST-' . str_pad((string) (crc32($phoneNumber) % 90000 + 10000), 5, '0', STR_PAD_LEFT),
            'fullName'         => $name,
            'phoneCountryCode' => $phoneCountryCode,
            'phoneNumber'      => $phoneNumber,
            'kycLevel'         => 'KYC_BASIC',
            'status'           => 'ACTIVE',
            'walletId'         => 'wlt_cust_' . strtoupper(substr(md5($phoneNumber . 'wallet'), 0, 18)),
        ];
    }

    public function lookupMerchant(string $phoneCountryCode, string $phoneNumber): ?array
    {
        if (! preg_match('/^\d{4,15}$/', $phoneNumber)) {
            return null;
        }

        // Deterministic mock: numbers ending in 0 are "not found"; numbers starting with 9 are SUSPENDED.
        if (str_ends_with($phoneNumber, '0')) {
            return null;
        }

        $names = FixtureLoader::load('customers/random-names');
        $nameIndex = (int) (hexdec(substr(md5($phoneNumber . 'merchant'), 0, 4)) % count($names));
        $businessName = 'Boutique ' . $names[$nameIndex];

        $status = str_starts_with($phoneNumber, '9') ? 'SUSPENDED' : 'ACTIVE';

        return [
            'merchantId'       => 'mch_' . strtoupper(substr(md5($phoneCountryCode . $phoneNumber . 'mch'), 0, 20)),
            'externalRef'      => 'MCH-' . str_pad((string) (crc32($phoneNumber) % 90000 + 10000), 5, '0', STR_PAD_LEFT),
            'businessName'     => $businessName,
            'phoneCountryCode' => $phoneCountryCode,
            'phoneNumber'      => $phoneNumber,
            'status'           => $status,
            'kycLevel'         => 'KYC_VERIFIED',
            'canCashOut'       => $status === 'ACTIVE',
        ];
    }

    public function processCashIn(array $data): array
    {
        $amount     = (int) ($data['amount'] ?? 0);
        $feeAmount  = (int) floor($amount * 0.01);
        $commission = (int) floor($amount * 0.01);
        $net        = $amount - $feeAmount;

        return [
            'transactionId'          => (string) Str::uuid(),
            'status'                 => 'COMPLETED',
            'requestedAmount'        => $amount,
            'feeAmount'              => $feeAmount,
            'commissionAmount'       => $commission,
            'netAmountToDestination' => $net,
            'currency'               => 'KMF',
            'completedAt'            => now()->toIso8601ZuluString(),
            'replayed'               => false,
        ];
    }

    public function processCashOut(array $data, string $idempotencyKey): array
    {
        $amount     = (int) ($data['amount'] ?? 0);
        $merchantId = (string) ($data['merchantId'] ?? '');

        // Mock control thresholds (priority: Approval > PIN > Confirmation).
        if ($amount > 200000) {
            return [
                'status' => 202,
                'data'   => [
                    'outcome'         => 'PENDING_APPROVAL',
                    'transactionId'   => null,
                    'status'          => 'PENDING_APPROVAL',
                    'approvalId'      => (string) Str::uuid(),
                    'requestedAmount' => $amount,
                    'currency'        => 'KMF',
                ],
            ];
        }

        if ($amount > 100000) {
            // PIN tier: require merchantPin.
            if (! array_key_exists('merchantPin', $data) || $data['merchantPin'] === null || $data['merchantPin'] === '') {
                return [
                    'status' => 202,
                    'data'   => [
                        'outcome'                => 'PENDING_PIN',
                        'requestedAmount'        => $amount,
                        'matchedThresholdAmount' => 100000,
                        'currency'               => 'KMF',
                    ],
                ];
            }

            $this->verifyMockMerchantPin($merchantId, (string) $data['merchantPin']);
        } elseif ($amount > 50000) {
            // Confirmation tier: require confirmationAcknowledged=true.
            if (($data['confirmationAcknowledged'] ?? false) !== true) {
                return [
                    'status' => 202,
                    'data'   => [
                        'outcome'                => 'PENDING_CONFIRMATION',
                        'requestedAmount'        => $amount,
                        'matchedThresholdAmount' => 50000,
                        'currency'               => 'KMF',
                    ],
                ];
            }
        }

        $feeAmount  = (int) floor($amount * 0.01);
        $commission = (int) floor($amount * 0.01);
        $net        = $amount - $feeAmount;

        return [
            'outcome'                => 'EXECUTED',
            'transactionId'          => (string) Str::uuid(),
            'status'                 => 'COMPLETED',
            'requestedAmount'        => $amount,
            'feeAmount'              => $feeAmount,
            'commissionAmount'       => $commission,
            'netAmountToDestination' => $net,
            'currency'               => 'KMF',
            'completedAt'            => now()->toIso8601ZuluString(),
            'replayed'               => false,
        ];
    }

    private function verifyMockMerchantPin(string $merchantId, string $merchantPin): void
    {
        $key = $merchantId !== '' ? $merchantId : 'unknown';
        $attempts = self::$mockPinAttempts[$key] ?? 0;

        if ($attempts >= 3) {
            throw new ApiException('AUTH_PIN_LOCKED', 422);
        }

        if ($merchantPin !== self::MOCK_MERCHANT_PIN) {
            self::$mockPinAttempts[$key] = $attempts + 1;
            throw new ApiException('AUTH_PIN_INVALID', 401);
        }

        unset(self::$mockPinAttempts[$key]);
    }
}

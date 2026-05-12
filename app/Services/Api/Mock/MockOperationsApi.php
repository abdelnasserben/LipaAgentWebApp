<?php

declare(strict_types=1);

namespace App\Services\Api\Mock;

use App\Contracts\Api\OperationsApi;
use App\Services\Api\Support\FixtureLoader;
use Illuminate\Support\Str;

final class MockOperationsApi implements OperationsApi
{
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

    public function processCashOut(array $data): array
    {
        $amount     = (int) ($data['amount'] ?? 0);
        $pending    = $amount > 100000;

        if ($pending) {
            // Spec §8.1: PENDING_APPROVAL response carries no transactionId/fees,
            // only approvalId, requestedAmount, currency and status.
            return [
                'status' => 202,
                'data'   => [
                    'transactionId'   => null,
                    'status'          => 'PENDING_APPROVAL',
                    'approvalId'      => (string) Str::uuid(),
                    'requestedAmount' => $amount,
                    'currency'        => 'KMF',
                ],
            ];
        }

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
}

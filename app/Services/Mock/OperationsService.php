<?php

declare(strict_types=1);

namespace App\Services\Mock;

use Illuminate\Support\Str;

class OperationsService
{
    private const KNOWN_CUSTOMERS = [
        '3201234' => [
            'customerId'       => 'cust_01HXKZ9P2Q3R4S5T6U7VALI1',
            'externalRef'      => 'CUST-00201',
            'fullName'         => 'Ali Hassan',
            'phoneCountryCode' => '269',
            'phoneNumber'      => '3201234',
            'kycLevel'         => 'KYC_BASIC',
            'status'           => 'ACTIVE',
            'walletId'         => 'wlt_cust_01A2B3C4D5E6F7G8H9',
        ],
        '3219876' => [
            'customerId'       => 'cust_02HXKZ9P2Q3R4S5T6U7VFAT2',
            'externalRef'      => 'CUST-00347',
            'fullName'         => 'Fatouma Youssouf',
            'phoneCountryCode' => '269',
            'phoneNumber'      => '3219876',
            'kycLevel'         => 'KYC_VERIFIED',
            'status'           => 'ACTIVE',
            'walletId'         => 'wlt_cust_02A2B3C4D5E6F7G8H9',
        ],
        '3445566' => [
            'customerId'       => 'cust_03HXKZ9P2Q3R4S5T6U7VOMA3',
            'externalRef'      => 'CUST-00519',
            'fullName'         => 'Omar Abdallah',
            'phoneCountryCode' => '269',
            'phoneNumber'      => '3445566',
            'kycLevel'         => 'KYC_BASIC',
            'status'           => 'SUSPENDED',
            'walletId'         => 'wlt_cust_03A2B3C4D5E6F7G8H9',
        ],
    ];

    private const RANDOM_NAMES = [
        'Nassuf Ibrahim',
        'Hadidja Saïd',
        'Raihana Combo',
        'Souffiane Athoumane',
        'Mbae Bacar',
        'Mariama Djae',
        'Toihira Ahamada',
        'Houmadi Mchangama',
        'Anziza Maoulida',
        'Yasmina Hamidou',
    ];

    public function lookupCustomer(string $phoneCountryCode, string $phoneNumber): ?array
    {
        if (isset(self::KNOWN_CUSTOMERS[$phoneNumber])) {
            $customer = self::KNOWN_CUSTOMERS[$phoneNumber];
            if ($customer['phoneCountryCode'] !== $phoneCountryCode) {
                return null;
            }
            return $customer;
        }

        if (!preg_match('/^\d{4,15}$/', $phoneNumber)) {
            return null;
        }

        $nameIndex = (int) (hexdec(substr(md5($phoneNumber), 0, 4)) % count(self::RANDOM_NAMES));
        $name      = self::RANDOM_NAMES[$nameIndex];

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

    public function processCashOut(array $data): array|string
    {
        $amount     = (int) ($data['amount'] ?? 0);
        $feeAmount  = (int) floor($amount * 0.01);
        $commission = (int) floor($amount * 0.01);
        $net        = $amount - $feeAmount;

        $response = [
            'transactionId'          => (string) Str::uuid(),
            'status'                 => $amount > 100000 ? 'PENDING_APPROVAL' : 'COMPLETED',
            'requestedAmount'        => $amount,
            'feeAmount'              => $feeAmount,
            'commissionAmount'       => $commission,
            'netAmountToDestination' => $net,
            'currency'               => 'KMF',
            'completedAt'            => $amount > 100000 ? null : now()->toIso8601ZuluString(),
            'replayed'               => false,
        ];

        if ($amount > 100000) {
            return ['status' => 202, 'data' => $response];
        }

        return $response;
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Mock;

class AgentService
{
    public function getProfile(): array
    {
        return [
            'id'                  => 'agt_01HXKZ9P2Q3R4S5T6U7V8W9X',
            'externalRef'         => 'AGENT-00142',
            'fullName'            => 'Moussa Bacar',
            'phoneCountryCode'    => '269',
            'phoneNumber'         => '3201456',
            'zone'                => 'Moroni Centre',
            'kycLevel'            => 'KYC_ENHANCED',
            'status'              => 'ACTIVE',
            'walletId'            => 'wlt_01HXKZ9P2Q3R4S5T6U7V8W9X',
            'limitProfileId'      => 'lp_01HXKZ9P',
            'canSellCards'        => true,
            'canDoCashIn'         => true,
            'canDoCashOut'        => true,
            'floatAlertThreshold' => 50000,
            'contractRef'         => 'CONT-2023-0088',
            'createdAt'           => '2023-06-15T08:00:00Z',
        ];
    }

    public function getBalance(): array
    {
        return [
            'walletId'         => 'wlt_01HXKZ9P2Q3R4S5T6U7V8W9X',
            'availableBalance' => 284750,
            'frozenBalance'    => 0,
            'currency'         => 'KMF',
            'walletStatus'     => 'ACTIVE',
        ];
    }

    public function getDailySummary(): array
    {
        return [
            'totalCompletedAmountToday' => 229500,
            'totalCompletedCountToday'  => 23,
            'commissionEarnedToday'     => 3840,
            'currentBalance'            => 284750,
            'floatAlertThreshold'       => 50000,
            'belowFloatAlert'           => false,
        ];
    }
}

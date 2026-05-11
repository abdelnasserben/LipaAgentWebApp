<?php

declare(strict_types=1);

namespace App\Services\Mock;

use App\Enums\PayoutStatus;
use App\Enums\TransactionType;

class CommissionService
{
    private const AGENT_ID = 'agt_01HXKZ9P2Q3R4S5T6U7V8W9X';

    public function getCommissions(array $filters = []): array
    {
        $entries = [
            [
                'id'            => 'com_01HW1A2B3C4D5E6F7G8H9I0J',
                'agentId'       => self::AGENT_ID,
                'transactionId' => 'txn_01HW1A2B3C4D5E6F7G8H9I0J',
                'amount'        => 18400,
                'status'        => PayoutStatus::PAID->value,
                'type'          => TransactionType::CASH_IN->value,
                'createdAt'     => '2025-12-31T23:59:00Z',
                'settledAt'     => '2026-01-02T09:00:00Z',
            ],
            [
                'id'            => 'com_02HW1A2B3C4D5E6F7G8H9I0K',
                'agentId'       => self::AGENT_ID,
                'transactionId' => 'txn_02HW1A2B3C4D5E6F7G8H9I0K',
                'amount'        => 15900,
                'status'        => PayoutStatus::PAID->value,
                'type'          => TransactionType::CASH_OUT->value,
                'createdAt'     => '2025-12-31T23:59:00Z',
                'settledAt'     => '2026-01-02T09:00:00Z',
            ],
            [
                'id'            => 'com_03HW1A2B3C4D5E6F7G8H9I0L',
                'agentId'       => self::AGENT_ID,
                'transactionId' => 'txn_03HW1A2B3C4D5E6F7G8H9I0L',
                'amount'        => 22750,
                'status'        => PayoutStatus::PAID->value,
                'type'          => TransactionType::CASH_IN->value,
                'createdAt'     => '2026-01-31T23:59:00Z',
                'settledAt'     => '2026-02-02T09:00:00Z',
            ],
            [
                'id'            => 'com_04HW1A2B3C4D5E6F7G8H9I0M',
                'agentId'       => self::AGENT_ID,
                'transactionId' => 'txn_04HW1A2B3C4D5E6F7G8H9I0M',
                'amount'        => 19600,
                'status'        => PayoutStatus::PAID->value,
                'type'          => TransactionType::CASH_OUT->value,
                'createdAt'     => '2026-01-31T23:59:00Z',
                'settledAt'     => '2026-02-02T09:00:00Z',
            ],
            [
                'id'            => 'com_05HW1A2B3C4D5E6F7G8H9I0N',
                'agentId'       => self::AGENT_ID,
                'transactionId' => 'txn_05HW1A2B3C4D5E6F7G8H9I0N',
                'amount'        => 24100,
                'status'        => PayoutStatus::PAID->value,
                'type'          => TransactionType::CASH_IN->value,
                'createdAt'     => '2026-02-28T23:59:00Z',
                'settledAt'     => '2026-03-02T09:00:00Z',
            ],
            [
                'id'            => 'com_06HW1A2B3C4D5E6F7G8H9I0O',
                'agentId'       => self::AGENT_ID,
                'transactionId' => 'txn_06HW1A2B3C4D5E6F7G8H9I0O',
                'amount'        => 21300,
                'status'        => PayoutStatus::PAID->value,
                'type'          => TransactionType::CASH_OUT->value,
                'createdAt'     => '2026-02-28T23:59:00Z',
                'settledAt'     => '2026-03-02T09:00:00Z',
            ],
            [
                'id'            => 'com_07HW1A2B3C4D5E6F7G8H9I0P',
                'agentId'       => self::AGENT_ID,
                'transactionId' => 'txn_07HW1A2B3C4D5E6F7G8H9I0P',
                'amount'        => 26800,
                'status'        => PayoutStatus::PAID->value,
                'type'          => TransactionType::CASH_IN->value,
                'createdAt'     => '2026-03-31T23:59:00Z',
                'settledAt'     => '2026-04-02T09:00:00Z',
            ],
            [
                'id'            => 'com_08HW1A2B3C4D5E6F7G8H9I0Q',
                'agentId'       => self::AGENT_ID,
                'transactionId' => 'txn_08HW1A2B3C4D5E6F7G8H9I0Q',
                'amount'        => 17950,
                'status'        => PayoutStatus::PAID->value,
                'type'          => TransactionType::CASH_OUT->value,
                'createdAt'     => '2026-03-31T23:59:00Z',
                'settledAt'     => '2026-04-02T09:00:00Z',
            ],
            [
                'id'            => 'com_09HW1A2B3C4D5E6F7G8H9I0R',
                'agentId'       => self::AGENT_ID,
                'transactionId' => 'txn_09HW1A2B3C4D5E6F7G8H9I0R',
                'amount'        => 23500,
                'status'        => PayoutStatus::PAID->value,
                'type'          => TransactionType::CASH_IN->value,
                'createdAt'     => '2026-04-30T23:59:00Z',
                'settledAt'     => '2026-05-02T09:00:00Z',
            ],
            [
                'id'            => 'com_10HW1A2B3C4D5E6F7G8H9I0S',
                'agentId'       => self::AGENT_ID,
                'transactionId' => 'txn_10HW1A2B3C4D5E6F7G8H9I0S',
                'amount'        => 20400,
                'status'        => PayoutStatus::PAID->value,
                'type'          => TransactionType::CASH_OUT->value,
                'createdAt'     => '2026-04-30T23:59:00Z',
                'settledAt'     => '2026-05-02T09:00:00Z',
            ],
            [
                'id'            => 'com_11HW1A2B3C4D5E6F7G8H9I0T',
                'agentId'       => self::AGENT_ID,
                'transactionId' => 'txn_11HW1A2B3C4D5E6F7G8H9I0T',
                'amount'        => 14800,
                'status'        => PayoutStatus::PENDING->value,
                'type'          => TransactionType::CASH_IN->value,
                'createdAt'     => '2026-05-11T08:12:05Z',
                'settledAt'     => null,
            ],
            [
                'id'            => 'com_12HW1A2B3C4D5E6F7G8H9I0U',
                'agentId'       => self::AGENT_ID,
                'transactionId' => 'txn_12HW1A2B3C4D5E6F7G8H9I0U',
                'amount'        => 13800,
                'status'        => PayoutStatus::PENDING->value,
                'type'          => TransactionType::CASH_OUT->value,
                'createdAt'     => '2026-05-11T10:45:06Z',
                'settledAt'     => null,
            ],
        ];

        if (!empty($filters['status'])) {
            $entries = array_values(array_filter(
                $entries,
                fn(array $e): bool => $e['status'] === $filters['status']
            ));
        }

        if (!empty($filters['type'])) {
            $entries = array_values(array_filter(
                $entries,
                fn(array $e): bool => $e['type'] === $filters['type']
            ));
        }

        return [
            'data'       => $entries,
            'pagination' => [
                'nextCursor' => null,
                'hasMore'    => false,
                'limit'      => 20,
            ],
        ];
    }

    public function getSummary(): array
    {
        return [
            'pendingTotal'  => 28600,
            'todayEarned'   => 3840,
            'weekEarned'    => 19200,
            'monthEarned'   => 28600,
        ];
    }
}

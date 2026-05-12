<?php

namespace App\Services\Api\Support;

final class StatementEntryPresenter
{
    public static function for(array $entry): array
    {
        $description = $entry['description'] ?? '';
        $entryType = $entry['entryType'] ?? null;

        $label = self::labelFromDescription($description);

        $isCredit = $entryType === 'CREDIT';

        return [
            'label' => $label,
            'sign' => $isCredit ? '+' : '−',
            'text_class' => $isCredit ? 'text-app-green' : 'text-app-red',
            'bg_class' => $isCredit ? 'bg-app-green-bg' : 'bg-app-red-bg',
        ];
    }

    private static function labelFromDescription(string $description): string
    {
        $upper = strtoupper($description);

        return match (true) {
            str_contains($upper, 'CARD_SALE') => 'Vente de carte',
            str_contains($upper, 'CASH_IN') => 'Cash In',
            str_contains($upper, 'CASH_OUT') => 'Cash Out',
            str_contains($upper, 'COMMISSION_PAYOUT') => 'Commission',
            str_contains($upper, 'AGENT_FUND_IN') => 'Approvisionnement agent',
            str_contains($upper, 'AGENT_FUND_OUT') => 'Retrait float agent',
            str_contains($upper, 'REVERSAL') => 'Annulation',
            str_contains($upper, 'FEE_COLLECTION') => 'Frais',
            str_contains($upper, 'PAYMENT') => 'Paiement',
            default => self::humanize($description),
        };
    }

    private static function humanize(string $description): string
    {
        $withoutParentheses = preg_replace('/\s*\(.*?\)\s*/', '', $description) ?? $description;

        return str($withoutParentheses)
            ->replace('_', ' ')
            ->lower()
            ->ucfirst()
            ->toString();
    }
}

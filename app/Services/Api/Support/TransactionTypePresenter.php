<?php

declare(strict_types=1);

namespace App\Services\Api\Support;

/**
 * Centralized presentation rules for transaction types in the Agent Web App.
 *
 * Every UI that renders a transaction (dashboard, listing, detail, recent
 * widget) must go through this class so that a new type never falls back to
 * "Cash Out" by accident.
 */
final class TransactionTypePresenter
{
    /**
     * Direction of money for the AGENT wallet:
     *  - 'in'      -> agent float credited (green, '+')
     *  - 'out'     -> agent float debited  (amber/red/indigo, '-')
     *  - 'neutral' -> no direct float impact / informational
     */
    private const MAP = [
        'CASH_IN' => [
            'label'      => 'Cash In',
            'direction'  => 'out', // agent float decreases (cash given to client wallet)
            'sign'       => '−',
            'text_class' => 'text-app-amber',
            'bg_class'   => 'bg-app-amber-bg',
            'icon'       => 'cash-in',
        ],
        'CASH_OUT' => [
            'label'      => 'Cash Out',
            'direction'  => 'in', // agent float increases (cash collected from client wallet)
            'sign'       => '+',
            'text_class' => 'text-app-green',
            'bg_class'   => 'bg-app-green-bg',
            'icon'       => 'cash-out',
        ],
        'CARD_SALE' => [
            'label'      => 'Vente de carte',
            'direction'  => 'in', // agent collects price, float credited
            'sign'       => '+',
            'text_class' => 'text-app-purple',
            'bg_class'   => 'bg-app-purple-bg',
            'icon'       => 'card',
        ],
        'CARD_REPLACEMENT' => [
            'label'      => 'Remplacement carte',
            'direction'  => 'in',
            'sign'       => '+',
            'text_class' => 'text-app-blue',
            'bg_class'   => 'bg-app-blue-bg',
            'icon'       => 'card',
        ],
        'COMMISSION_PAYOUT' => [
            'label'      => 'Commission',
            'direction'  => 'in',
            'sign'       => '+',
            'text_class' => 'text-app-teal',
            'bg_class'   => 'bg-app-teal-bg',
            'icon'       => 'commission',
        ],
        'FEE_COLLECTION' => [
            'label'      => 'Frais',
            'direction'  => 'out',
            'sign'       => '−',
            'text_class' => 'text-app-amber',
            'bg_class'   => 'bg-app-amber-bg',
            'icon'       => 'transactions',
        ],
        'PAYMENT' => [
            'label'      => 'Paiement',
            'direction'  => 'out',
            'sign'       => '−',
            'text_class' => 'text-app-amber',
            'bg_class'   => 'bg-app-amber-bg',
            'icon'       => 'transactions',
        ],
        'REVERSAL' => [
            'label'      => 'Annulation',
            'direction'  => 'neutral',
            'sign'       => '±',
            'text_class' => 'text-app-indigo',
            'bg_class'   => 'bg-app-indigo-bg',
            'icon'       => 'transactions',
        ],
        'AGENT_FUND_IN' => [
            'label'      => 'Approvisionnement agent',
            'direction'  => 'in',
            'sign'       => '+',
            'text_class' => 'text-app-blue',
            'bg_class'   => 'bg-app-blue-bg',
            'icon'       => 'cash-in',
        ],
        'AGENT_FUND_OUT' => [
            'label'      => 'Retrait float agent',
            'direction'  => 'out',
            'sign'       => '−',
            'text_class' => 'text-app-indigo',
            'bg_class'   => 'bg-app-indigo-bg',
            'icon'       => 'cash-out',
        ],
    ];

    /**
     * @return array{label:string,direction:string,sign:string,text_class:string,bg_class:string,icon:string,raw:string}
     */
    public static function for(?string $type): array
    {
        $key = $type ?? '';
        $cfg = self::MAP[$key] ?? [
            'label'      => self::humanizeFallback($key),
            'direction'  => 'neutral',
            'sign'       => '',
            'text_class' => 'text-app-muted',
            'bg_class'   => 'bg-app-border',
            'icon'       => 'transactions',
        ];

        $cfg['raw'] = $key;

        return $cfg;
    }

    public static function label(?string $type): string
    {
        return self::for($type)['label'];
    }

    private static function humanizeFallback(string $raw): string
    {
        if ($raw === '') {
            return '—';
        }

        return ucfirst(strtolower(str_replace('_', ' ', $raw)));
    }
}

<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionType: string
{
    case CASH_IN = 'CASH_IN';
    case CASH_OUT = 'CASH_OUT';
    case CARD_SALE = 'CARD_SALE';
    case CARD_REPLACEMENT = 'CARD_REPLACEMENT';
    case COMMISSION_PAYOUT = 'COMMISSION_PAYOUT';
    case FEE_COLLECTION = 'FEE_COLLECTION';
    case REVERSAL = 'REVERSAL';
    case AGENT_FUND_IN = 'AGENT_FUND_IN';
    case AGENT_FUND_OUT = 'AGENT_FUND_OUT';
}

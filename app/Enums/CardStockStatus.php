<?php

declare(strict_types=1);

namespace App\Enums;

enum CardStockStatus: string
{
    case IN_WAREHOUSE = 'IN_WAREHOUSE';
    case ASSIGNED_TO_AGENT = 'ASSIGNED_TO_AGENT';
    case SOLD = 'SOLD';
    case RETURNED = 'RETURNED';
    case SPOILED = 'SPOILED';
}

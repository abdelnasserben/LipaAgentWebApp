<?php

declare(strict_types=1);

namespace App\Enums;

enum CardStatus: string
{
    case ISSUED = 'ISSUED';
    case ACTIVE = 'ACTIVE';
    case BLOCKED = 'BLOCKED';
    case LOST = 'LOST';
    case STOLEN = 'STOLEN';
    case EXPIRED = 'EXPIRED';
    case CLOSED = 'CLOSED';
}

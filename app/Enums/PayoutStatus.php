<?php

declare(strict_types=1);

namespace App\Enums;

enum PayoutStatus: string
{
    case PENDING = 'PENDING';
    case PAID = 'PAID';
    case FAILED = 'FAILED';
    case CANCELLED = 'CANCELLED';
}

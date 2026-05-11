<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionStatus: string
{
    case PENDING = 'PENDING';
    case AUTHORIZED = 'AUTHORIZED';
    case COMPLETED = 'COMPLETED';
    case DECLINED = 'DECLINED';
    case EXPIRED = 'EXPIRED';
    case REVERSED = 'REVERSED';
}

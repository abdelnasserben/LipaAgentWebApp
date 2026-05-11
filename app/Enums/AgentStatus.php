<?php

declare(strict_types=1);

namespace App\Enums;

enum AgentStatus: string
{
    case PENDING_KYC = 'PENDING_KYC';
    case ACTIVE = 'ACTIVE';
    case SUSPENDED = 'SUSPENDED';
    case CLOSED = 'CLOSED';
}

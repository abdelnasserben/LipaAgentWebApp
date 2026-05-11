<?php

declare(strict_types=1);

namespace App\Enums;

enum KycDocumentStatus: string
{
    case PENDING_REVIEW = 'PENDING_REVIEW';
    case ACCEPTED = 'ACCEPTED';
    case REJECTED = 'REJECTED';
}

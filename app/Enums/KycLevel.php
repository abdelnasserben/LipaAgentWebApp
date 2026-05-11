<?php

declare(strict_types=1);

namespace App\Enums;

enum KycLevel: string
{
    case KYC_NONE = 'KYC_NONE';
    case KYC_BASIC = 'KYC_BASIC';
    case KYC_VERIFIED = 'KYC_VERIFIED';
    case KYC_ENHANCED = 'KYC_ENHANCED';
}

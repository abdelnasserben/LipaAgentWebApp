<?php

declare(strict_types=1);

namespace App\Enums;

enum KycDocumentType: string
{
    case NATIONAL_ID = 'NATIONAL_ID';
    case PASSPORT = 'PASSPORT';
    case PROOF_OF_ADDRESS = 'PROOF_OF_ADDRESS';
    case BUSINESS_LICENSE = 'BUSINESS_LICENSE';
    case OTHER = 'OTHER';
}

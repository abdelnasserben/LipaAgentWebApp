<?php

declare(strict_types=1);

namespace App\Contracts\Api;

interface LimitsApi
{
    /** @return array<string, mixed> */
    public function getLimits(): array;
}

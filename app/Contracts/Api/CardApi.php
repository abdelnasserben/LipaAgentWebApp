<?php

declare(strict_types=1);

namespace App\Contracts\Api;

interface CardApi
{
    /** @return array<int, array<string, mixed>> */
    public function getCardStock(): array;
}

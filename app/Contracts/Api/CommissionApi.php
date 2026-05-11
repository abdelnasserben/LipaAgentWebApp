<?php

declare(strict_types=1);

namespace App\Contracts\Api;

interface CommissionApi
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{data: array<int, array<string, mixed>>, pagination: array<string, mixed>}
     */
    public function getCommissions(array $filters = []): array;

    /** @return array<string, mixed> */
    public function getSummary(): array;
}

<?php

declare(strict_types=1);

namespace App\Services\Api\Mock;

use App\Contracts\Api\LimitsApi;
use App\Services\Api\Support\FixtureLoader;

final class MockLimitsApi implements LimitsApi
{
    public function getLimits(): array
    {
        return FixtureLoader::load('limits/limits');
    }
}

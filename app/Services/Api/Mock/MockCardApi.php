<?php

declare(strict_types=1);

namespace App\Services\Api\Mock;

use App\Contracts\Api\CardApi;
use App\Services\Api\Support\FixtureLoader;

final class MockCardApi implements CardApi
{
    public function getCardStock(): array
    {
        return FixtureLoader::load('cards/stock');
    }
}

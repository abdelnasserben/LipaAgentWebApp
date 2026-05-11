<?php

declare(strict_types=1);

namespace App\Services\Api\Support;

use RuntimeException;

/**
 * Loads JSON fixtures from the configured mocks directory.
 *
 * Fixtures live under `database/mocks/` and are referenced by relative path
 * (e.g. "agent/profile" → database/mocks/agent/profile.json). They are cached
 * per-request so a single mock service can read the same fixture multiple
 * times without re-hitting disk.
 */
final class FixtureLoader
{
    /** @var array<string, mixed> */
    private static array $cache = [];

    /**
     * @return array<mixed>
     */
    public static function load(string $relativePath): array
    {
        if (isset(self::$cache[$relativePath])) {
            return self::$cache[$relativePath];
        }

        $base = (string) config('komopay.mocks_path', database_path('mocks'));
        $full = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $relativePath) . '.json';

        if (! is_file($full)) {
            throw new RuntimeException("Mock fixture not found: {$full}");
        }

        $raw = file_get_contents($full);
        if ($raw === false) {
            throw new RuntimeException("Unable to read mock fixture: {$full}");
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            throw new RuntimeException("Invalid JSON in mock fixture: {$full}");
        }

        return self::$cache[$relativePath] = $decoded;
    }

    public static function flush(): void
    {
        self::$cache = [];
    }
}

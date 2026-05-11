<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around Laravel's HTTP client, configured for the Komopay API.
 *
 * HTTP implementations of `App\Contracts\Api\*` should depend on this class
 * and call its helpers rather than hitting `Http::*` directly — keeping base
 * URL, auth headers, and error handling in one place.
 */
final class KomopayClient
{
    public function request(): PendingRequest
    {
        $client = Http::baseUrl((string) config('komopay.base_url'))
            ->timeout((int) config('komopay.timeout', 15))
            ->acceptJson()
            ->asJson();

        $token = config('komopay.api_key');
        if (is_string($token) && $token !== '') {
            $client = $client->withToken($token);
        }

        return $client;
    }

    public function get(string $path, array $query = []): Response
    {
        return $this->request()->get($path, $query);
    }

    public function post(string $path, array $body = []): Response
    {
        return $this->request()->post($path, $body);
    }

    public function put(string $path, array $body = []): Response
    {
        return $this->request()->put($path, $body);
    }

    public function delete(string $path, array $body = []): Response
    {
        return $this->request()->delete($path, $body);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Api\Http;

use App\Contracts\Api\TotpApi;
use App\Exceptions\ApiException;

final class HttpTotpApi implements TotpApi
{
    public function __construct(private readonly KomopayClient $client)
    {
    }

    public function setup(): array
    {
        $data = $this->client->data(
            $this->client->post('/api/v1/auth/agent/totp-setup'),
            'TOTP_SETUP_FAILED',
        );

        if (! isset($data['secret'], $data['qrUri']) || ! is_string($data['secret']) || ! is_string($data['qrUri'])) {
            throw new ApiException('INVALID_RESPONSE', 200);
        }

        return ['secret' => $data['secret'], 'qrUri' => $data['qrUri']];
    }

    public function confirm(string $code): void
    {
        $this->client->data(
            $this->client->post('/api/v1/auth/agent/totp-confirm', ['code' => $code]),
            'MFA_INVALID',
        );
    }

    public function revoke(string $code): void
    {
        $this->client->data(
            $this->client->delete('/api/v1/auth/agent/totp-setup', ['code' => $code]),
            'MFA_INVALID',
        );
    }
}

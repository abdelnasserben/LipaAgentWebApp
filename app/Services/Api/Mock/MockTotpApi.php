<?php

declare(strict_types=1);

namespace App\Services\Api\Mock;

use App\Contracts\Api\TotpApi;
use App\Exceptions\ApiException;
use Illuminate\Support\Str;

final class MockTotpApi implements TotpApi
{
    private const VALID_CODE = '123456';

    public function setup(): array
    {
        $secret = strtoupper(substr(str_replace(['-', '_'], '', (string) Str::uuid()), 0, 32));

        return [
            'secret' => $secret,
            'qrUri'  => sprintf(
                'otpauth://totp/Lipa:agent@mock?secret=%s&issuer=Lipa&algorithm=SHA1&digits=6&period=30',
                $secret,
            ),
        ];
    }

    public function confirm(string $code): void
    {
        if ($code !== self::VALID_CODE) {
            throw new ApiException('MFA_INVALID', 401, 'Code TOTP invalide.');
        }
    }

    public function revoke(string $code): void
    {
        if ($code !== self::VALID_CODE) {
            throw new ApiException('MFA_INVALID', 401, 'Code TOTP invalide.');
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Contracts\Api;

interface TotpApi
{
    /**
     * POST /api/v1/auth/agent/totp-setup
     *
     * @return array{secret: string, qrUri: string}
     */
    public function setup(): array;

    /**
     * POST /api/v1/auth/agent/totp-confirm — activates the pending secret. 204 No Content.
     */
    public function confirm(string $code): void;

    /**
     * DELETE /api/v1/auth/agent/totp-setup — revokes active enrollment. 204 No Content.
     */
    public function revoke(string $code): void;
}

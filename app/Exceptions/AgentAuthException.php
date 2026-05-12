<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class AgentAuthException extends RuntimeException
{
    public function __construct(
        private readonly string $apiCode,
        private readonly int $statusCode = 0,
        string $apiMessage = '',
    ) {
        parent::__construct($apiMessage !== '' ? $apiMessage : $apiCode, $statusCode);
    }

    public function apiCode(): string
    {
        return $this->apiCode;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}

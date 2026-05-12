<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class ApiException extends RuntimeException
{
    /**
     * @param  array<int, mixed>  $details
     */
    public function __construct(
        private readonly string $apiCode,
        private readonly int $statusCode = 0,
        string $apiMessage = '',
        private readonly array $details = [],
        private readonly ?string $correlationId = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($apiMessage !== '' ? $apiMessage : $apiCode, $statusCode, $previous);
    }

    public function apiCode(): string
    {
        return $this->apiCode;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<int, mixed>
     */
    public function details(): array
    {
        return $this->details;
    }

    public function correlationId(): ?string
    {
        return $this->correlationId;
    }

    public function isAuthenticationFailure(): bool
    {
        return in_array($this->apiCode, ['UNAUTHORIZED', 'TOKEN_EXPIRED', 'TOKEN_REVOKED', 'REFRESH_TOKEN_INVALID'], true);
    }
}

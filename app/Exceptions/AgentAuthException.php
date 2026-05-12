<?php

declare(strict_types=1);

namespace App\Exceptions;

final class AgentAuthException extends ApiException
{
    /**
     * @param  array<int, mixed>  $details
     */
    public function __construct(
        string $apiCode,
        int $statusCode = 0,
        string $apiMessage = '',
        array $details = [],
        ?string $correlationId = null,
    ) {
        parent::__construct($apiCode, $statusCode, $apiMessage, $details, $correlationId);
    }

    public static function fromApiException(ApiException $exception): self
    {
        return new self(
            $exception->apiCode(),
            $exception->statusCode(),
            $exception->getMessage(),
            $exception->details(),
            $exception->correlationId(),
        );
    }
}

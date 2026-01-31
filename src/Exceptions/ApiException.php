<?php

declare(strict_types=1);

namespace Dropshipping\Exceptions;

class ApiException extends DropshippingException
{
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly ?string $traceId = null,
    ) {
        parent::__construct($message, $statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getTraceId(): ?string
    {
        return $this->traceId;
    }
}

<?php

declare(strict_types=1);

namespace Dropshipping\Exceptions;

/**
 * Exception thrown when the API returns an error response.
 */
class ApiException extends DropshippingException
{
    /**
     * @param string      $message    The error message returned by the API.
     * @param int         $statusCode The HTTP status code of the error response.
     * @param string|null $traceId    The trace ID for request correlation, if available.
     */
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly ?string $traceId = null,
    ) {
        parent::__construct($message, $statusCode);
    }

    /**
     * Get the HTTP status code of the error response.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the trace ID for request correlation.
     */
    public function getTraceId(): ?string
    {
        return $this->traceId;
    }
}

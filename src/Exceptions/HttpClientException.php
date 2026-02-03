<?php

declare(strict_types=1);

namespace Dropshipping\Exceptions;

use Psr\Http\Client\ClientExceptionInterface;

/**
 * Exception thrown when the HTTP client fails (e.g. network errors, timeouts).
 *
 * Wraps a PSR-18 ClientExceptionInterface as the underlying cause.
 */
class HttpClientException extends DropshippingException
{
    /**
     * @param string                   $message         The descriptive error message.
     * @param ClientExceptionInterface $clientException The underlying PSR-18 client exception.
     */
    public function __construct(
        string $message,
        private readonly ClientExceptionInterface $clientException,
    ) {
        parent::__construct($message, 0, $clientException);
    }

    /**
     * Get the underlying PSR-18 client exception.
     */
    public function getClientException(): ClientExceptionInterface
    {
        return $this->clientException;
    }
}

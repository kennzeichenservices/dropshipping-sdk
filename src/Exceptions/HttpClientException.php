<?php

declare(strict_types=1);

namespace Dropshipping\Exceptions;

use Psr\Http\Client\ClientExceptionInterface;

class HttpClientException extends DropshippingException
{
    public function __construct(
        string $message,
        private readonly ClientExceptionInterface $clientException,
    ) {
        parent::__construct($message, 0, $clientException);
    }

    public function getClientException(): ClientExceptionInterface
    {
        return $this->clientException;
    }
}

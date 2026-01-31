<?php

declare(strict_types=1);

namespace Dropshipping\Client;

use Dropshipping\Exceptions\HttpClientException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Psr18HttpClient
{
    public function __construct(
        private readonly ClientInterface $httpClient,
    ) {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new HttpClientException(
                sprintf('HTTP request failed: %s', $exception->getMessage()),
                $exception,
            );
        }
    }
}

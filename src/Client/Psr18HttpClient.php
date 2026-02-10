<?php

declare(strict_types=1);

namespace Dropshipping\Client;

use Dropshipping\Exceptions\HttpClientException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Thin wrapper around a PSR-18 HTTP client.
 *
 * Delegates request sending to the underlying PSR-18 client and converts
 * any PSR client exceptions into SDK-specific HttpClientException instances.
 */
final class Psr18HttpClient
{
    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly ClientInterface $httpClient,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Send the HTTP request and return the response.
     *
     * @throws HttpClientException When the underlying client throws a PSR-18 exception.
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            $this->logger->error('HTTP request failed', [
                'url' => (string) $request->getUri(),
                'method' => $request->getMethod(),
                'exception' => $exception->getMessage(),
            ]);

            throw new HttpClientException(
                sprintf('HTTP request failed: %s', $exception->getMessage()),
                $exception,
            );
        }

        $this->logger->info('HTTP request successful', [
            'url' => (string) $request->getUri(),
            'method' => $request->getMethod(),
            'status' => $response->getStatusCode(),
        ]);

        return $response;
    }

}

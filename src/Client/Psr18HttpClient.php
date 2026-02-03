<?php

declare(strict_types=1);

namespace Dropshipping\Client;

use Dropshipping\Exceptions\HttpClientException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Thin wrapper around a PSR-18 HTTP client.
 *
 * Delegates request sending to the underlying PSR-18 client and converts
 * any PSR client exceptions into SDK-specific HttpClientException instances.
 */
final class Psr18HttpClient
{
    public function __construct(
        private readonly ClientInterface $httpClient,
    ) {
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
            $this->debugLog($request, null, $exception);

            throw new HttpClientException(
                sprintf('HTTP request failed: %s', $exception->getMessage()),
                $exception,
            );
        }

        $this->debugLog($request, $response);

        return $response;
    }

    private function debugLog(
        RequestInterface $request,
        ?ResponseInterface $response,
        ?ClientExceptionInterface $exception = null,
    ): void {
        if (!defined('KS_DROPSHIPPING_DEBUG') || !KS_DROPSHIPPING_DEBUG) {
            return;
        }

        $logFile = defined('KS_DROPSHIPPING_DEBUG_FILE')
            ? KS_DROPSHIPPING_DEBUG_FILE
            : 'dropshipping-debug.log';

        $timestamp = date('Y-m-d H:i:s');
        $separator = str_repeat('-', 80);

        $requestBody = (string) $request->getBody();
        $request->getBody()->rewind();

        $lines = [];
        $lines[] = $separator;
        $lines[] = "[{$timestamp}] {$request->getMethod()} {$request->getUri()}";
        $lines[] = '';
        $lines[] = '>>> REQUEST HEADERS';

        foreach ($request->getHeaders() as $name => $values) {
            $value = $name === 'Authorization' ? '***' : implode(', ', $values);
            $lines[] = "  {$name}: {$value}";
        }

        if ($requestBody !== '') {
            $lines[] = '';
            $lines[] = '>>> REQUEST BODY';
            $lines[] = $requestBody;
        }

        if ($response !== null) {
            $responseBody = (string) $response->getBody();
            $response->getBody()->rewind();

            $lines[] = '';
            $lines[] = "<<< RESPONSE {$response->getStatusCode()} {$response->getReasonPhrase()}";

            foreach ($response->getHeaders() as $name => $values) {
                $lines[] = "  {$name}: " . implode(', ', $values);
            }

            if ($responseBody !== '') {
                $lines[] = '';
                $lines[] = '<<< RESPONSE BODY';
                $lines[] = $responseBody;
            }
        }

        if ($exception !== null) {
            $lines[] = '';
            $lines[] = '!!! EXCEPTION';
            $lines[] = "  {$exception->getMessage()}";
        }

        $lines[] = $separator;
        $lines[] = '';

        file_put_contents($logFile, implode("\n", $lines), FILE_APPEND | LOCK_EX);
    }
}

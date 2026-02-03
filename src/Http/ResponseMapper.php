<?php

declare(strict_types=1);

namespace Dropshipping\Http;

use Dropshipping\Contracts\SerializerInterface;
use Dropshipping\Exceptions\ApiException;
use Psr\Http\Message\ResponseInterface;

/**
 * Maps PSR-7 HTTP responses to decoded associative arrays.
 *
 * Validates the response status code against a list of expected codes and
 * throws an ApiException when the status is unexpected.
 */
final class ResponseMapper
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * Validate the response status code, extract the trace ID and decode the body.
     *
     * Throws an ApiException when the status code does not match any of the
     * expected codes. Returns an empty array for empty response bodies.
     *
     * @param list<int> $expectedStatusCodes
     * @return array<string, mixed>
     *
     * @throws ApiException When the response status code is unexpected.
     */
    public function mapResponse(ResponseInterface $response, array $expectedStatusCodes = [200]): array
    {
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $traceId = $response->getHeaderLine('X-Trace-Id') ?: null;

        if (!in_array($statusCode, $expectedStatusCodes, true)) {
            $errorMessage = 'API request failed';

            if ($body !== '') {
                $data = $this->serializer->decode($body);
                $errorMessage = $data['error'] ?? $errorMessage;
            }

            throw new ApiException($errorMessage, $statusCode, $traceId);
        }

        if ($body === '') {
            return [];
        }

        return $this->serializer->decode($body);
    }
}

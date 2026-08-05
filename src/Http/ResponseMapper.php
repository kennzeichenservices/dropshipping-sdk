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
     * @throws ApiException When the response status code is unexpected, or when a
     *                      successful response carries a body that is not valid JSON.
     */
    public function mapResponse(ResponseInterface $response, array $expectedStatusCodes = [200]): array
    {
        $body = $this->readBody($response, $expectedStatusCodes);

        if ($body === '') {
            return [];
        }

        try {
            return $this->serializer->decode($body);
        } catch (\JsonException $e) {
            throw new ApiException(
                'API returned a malformed JSON response',
                $response->getStatusCode(),
                $this->extractTraceId($response),
                $e,
            );
        }
    }

    /**
     * Return the raw response body, throwing an ApiException on an unexpected status code.
     *
     * Use this for responses that are not JSON — binary downloads, for instance — so that
     * every endpoint reports API errors the same way instead of hand-rolling the check.
     *
     * The body is read exactly once here: PSR-7 streams are not required to be seekable,
     * so a caller that reads it again may get an empty string back.
     *
     * @param list<int> $expectedStatusCodes
     *
     * @throws ApiException When the response status code is unexpected.
     */
    public function readBody(ResponseInterface $response, array $expectedStatusCodes = [200]): string
    {
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        if (!in_array($statusCode, $expectedStatusCodes, true)) {
            throw new ApiException(
                $this->extractErrorMessage($body),
                $statusCode,
                $this->extractTraceId($response),
            );
        }

        return $body;
    }

    /**
     * Pull the API's error message out of a failed response body, falling back to a default.
     *
     * Error responses are not reliably JSON: gateways, CDNs and WAFs answer with HTML or
     * plain text, and the API's own `error` field is only documented as a string. Nothing
     * that goes wrong while inspecting the body may mask the status code, which is the
     * part the caller actually needs — so every failure here degrades to the default.
     */
    private function extractErrorMessage(string $body): string
    {
        $fallback = 'API request failed';

        if ($body === '') {
            return $fallback;
        }

        try {
            $data = $this->serializer->decode($body);
        } catch (\Throwable) {
            return $fallback;
        }

        $error = $data['error'] ?? null;

        return is_string($error) && $error !== '' ? $error : $fallback;
    }

    /**
     * Extract the trace ID used for request correlation, if the API supplied one.
     */
    private function extractTraceId(ResponseInterface $response): ?string
    {
        return $response->getHeaderLine('X-Trace-Id') ?: null;
    }
}

<?php

declare(strict_types=1);

namespace Dropshipping\Http;

use Dropshipping\Contracts\SerializerInterface;
use Dropshipping\Exceptions\ApiException;
use Psr\Http\Message\ResponseInterface;

final class ResponseMapper
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * @param list<int> $expectedStatusCodes
     * @return array<string, mixed>
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

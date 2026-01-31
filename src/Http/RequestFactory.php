<?php

declare(strict_types=1);

namespace Dropshipping\Http;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Contracts\SerializerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class RequestFactory
{
    public function __construct(
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly ApiKeyAuthenticator $authenticator,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /** @param array<string, mixed> $body */
    public function createJsonRequest(string $method, string $url, array $body = []): RequestInterface
    {
        $request = $this->requestFactory->createRequest($method, $url);
        $request = $this->authenticator->authenticate($request);
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withHeader('Accept', 'application/json');

        if ($body !== []) {
            $stream = $this->streamFactory->createStream($this->serializer->encode($body));
            $request = $request->withBody($stream);
        }

        return $request;
    }

    public function createMultipartRequest(
        string $method,
        string $url,
        string $orderJson,
        string $boundary,
    ): RequestInterface {
        $request = $this->requestFactory->createRequest($method, $url);
        $request = $this->authenticator->authenticate($request);
        $request = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . $boundary);
        $request = $request->withHeader('Accept', 'application/json');

        return $request;
    }

    /**
     * @param list<string> $filePaths
     */
    public function buildMultipartBody(string $orderJson, array $filePaths, string $boundary): string
    {
        $body = '';

        $body .= '--' . $boundary . "\r\n";
        $body .= "Content-Disposition: form-data; name=\"order\"\r\n";
        $body .= "Content-Type: application/json\r\n\r\n";
        $body .= $orderJson . "\r\n";

        foreach ($filePaths as $filePath) {
            $filename = basename($filePath);
            $mimeType = $this->detectMimeType($filePath);
            $content = file_get_contents($filePath);

            if ($content === false) {
                throw new \Dropshipping\Exceptions\DropshippingException(
                    sprintf('Unable to read file: %s', $filePath),
                );
            }

            $body .= '--' . $boundary . "\r\n";
            $body .= sprintf("Content-Disposition: form-data; name=\"files\"; filename=\"%s\"\r\n", $filename);
            $body .= sprintf("Content-Type: %s\r\n\r\n", $mimeType);
            $body .= $content . "\r\n";
        }

        $body .= '--' . $boundary . "--\r\n";

        return $body;
    }

    private function detectMimeType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
    }
}

<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Client;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\Exceptions\HttpClientException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

final class Psr18HttpClientTest extends TestCase
{
    public function test_sendRequest_returns_response(): void
    {
        $expected = new Response(200, [], 'OK');
        $mock = $this->createMock(ClientInterface::class);
        $mock->method('sendRequest')->willReturn($expected);

        $client = new Psr18HttpClient($mock);
        $factory = new Psr17Factory();
        $request = $factory->createRequest('GET', 'https://example.com');

        $result = $client->sendRequest($request);

        self::assertSame(200, $result->getStatusCode());
    }

    public function test_sendRequest_wraps_client_exception(): void
    {
        $clientException = new class ('Connection failed') extends \RuntimeException implements ClientExceptionInterface {};

        $mock = $this->createMock(ClientInterface::class);
        $mock->method('sendRequest')->willThrowException($clientException);

        $client = new Psr18HttpClient($mock);
        $factory = new Psr17Factory();
        $request = $factory->createRequest('GET', 'https://example.com');

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('HTTP request failed: Connection failed');

        try {
            $client->sendRequest($request);
        } catch (HttpClientException $e) {
            self::assertSame($clientException, $e->getClientException());
            throw $e;
        }
    }
}

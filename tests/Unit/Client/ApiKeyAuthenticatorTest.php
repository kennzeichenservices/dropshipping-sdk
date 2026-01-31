<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Client;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class ApiKeyAuthenticatorTest extends TestCase
{
    public function test_authenticate_adds_basic_auth_header(): void
    {
        $authenticator = new ApiKeyAuthenticator('user@example.com', 'secret');
        $factory = new Psr17Factory();
        $request = $factory->createRequest('GET', 'https://example.com');

        $result = $authenticator->authenticate($request);

        $expected = 'Basic ' . base64_encode('user@example.com:secret');
        self::assertSame($expected, $result->getHeaderLine('Authorization'));
    }

    public function test_authenticate_preserves_existing_headers(): void
    {
        $authenticator = new ApiKeyAuthenticator('user', 'pass');
        $factory = new Psr17Factory();
        $request = $factory->createRequest('GET', 'https://example.com')
            ->withHeader('X-Custom', 'value');

        $result = $authenticator->authenticate($request);

        self::assertSame('value', $result->getHeaderLine('X-Custom'));
        self::assertNotEmpty($result->getHeaderLine('Authorization'));
    }
}

<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Configuration;

use Dropshipping\Configuration\DropshippingConfig;
use PHPUnit\Framework\TestCase;

final class DropshippingConfigTest extends TestCase
{
    public function test_getUsername_returns_value(): void
    {
        $config = new DropshippingConfig('host', 1, 'myuser', 'mypass');

        self::assertSame('myuser', $config->getUsername());
    }

    public function test_getPassword_returns_value(): void
    {
        $config = new DropshippingConfig('host', 1, 'myuser', 'mypass');

        self::assertSame('mypass', $config->getPassword());
    }

    public function test_getWebhookSignatureSecret_returns_null_when_not_set(): void
    {
        $config = new DropshippingConfig('host', 1, 'user', 'pass');

        self::assertNull($config->getWebhookSignatureSecret());
    }

    public function test_getWebhookSignatureSecret_returns_value_when_set(): void
    {
        $config = new DropshippingConfig('host', 1, 'user', 'pass', 'secret123');

        self::assertSame('secret123', $config->getWebhookSignatureSecret());
    }

    public function test_getTimeout_returns_default_55(): void
    {
        $config = new DropshippingConfig('host', 1, 'user', 'pass');

        self::assertSame(55, $config->getTimeout());
    }

    public function test_getTimeout_returns_custom_value(): void
    {
        $config = new DropshippingConfig('host', 1, 'user', 'pass', null, null, 30);

        self::assertSame(30, $config->getTimeout());
    }
}

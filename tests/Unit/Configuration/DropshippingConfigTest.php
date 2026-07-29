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

    public function test_getBaseUrl_uses_explicit_api_version(): void
    {
        $config = new DropshippingConfig('api.example.com', 42, 'user', 'pass', null, '2.3.2');

        self::assertSame('https://api.example.com/dropshipping-api/42/2.3.2', $config->getBaseUrl());
    }

    /**
     * The API version is part of the request URL, and an unreleased version makes the API answer
     * 403 on every endpoint. composer.json must therefore declare a version that is live for all
     * clients — a beta is opted into per integration, never shipped as the default.
     */
    public function test_default_api_version_comes_from_composer_json(): void
    {
        self::withoutApiVersionEnv(function (): void {
            $config = new DropshippingConfig('api.example.com', 42, 'user', 'pass');

            self::assertSame(
                'https://api.example.com/dropshipping-api/42/' . self::composerApiVersion(),
                $config->getBaseUrl(),
            );
        });
    }

    public function test_env_var_overrides_the_default_api_version(): void
    {
        $previous = getenv('DROPSHIPPING_API_VERSION');
        putenv('DROPSHIPPING_API_VERSION=9.9.9');

        try {
            $config = new DropshippingConfig('api.example.com', 42, 'user', 'pass');

            self::assertStringEndsWith('/9.9.9', $config->getBaseUrl());
        } finally {
            self::restoreApiVersionEnv($previous);
        }
    }

    public function test_explicit_api_version_wins_over_the_env_var(): void
    {
        $previous = getenv('DROPSHIPPING_API_VERSION');
        putenv('DROPSHIPPING_API_VERSION=9.9.9');

        try {
            $config = new DropshippingConfig('api.example.com', 42, 'user', 'pass', null, '2.3.2');

            self::assertStringEndsWith('/2.3.2', $config->getBaseUrl());
        } finally {
            self::restoreApiVersionEnv($previous);
        }
    }

    private static function withoutApiVersionEnv(callable $test): void
    {
        $previous = getenv('DROPSHIPPING_API_VERSION');
        putenv('DROPSHIPPING_API_VERSION');

        try {
            $test();
        } finally {
            self::restoreApiVersionEnv($previous);
        }
    }

    private static function restoreApiVersionEnv(string|false $previous): void
    {
        if (is_string($previous)) {
            putenv('DROPSHIPPING_API_VERSION=' . $previous);

            return;
        }

        putenv('DROPSHIPPING_API_VERSION');
    }

    private static function composerApiVersion(): string
    {
        $data = json_decode((string) file_get_contents(dirname(__DIR__, 3) . '/composer.json'), true);

        self::assertIsString($data['api-version'] ?? null, 'composer.json must declare a string "api-version"');

        return $data['api-version'];
    }
}

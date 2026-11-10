<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Integration;

use Dropshipping\Client\ApiClient;
use Dropshipping\Configuration\DropshippingConfig;
use Dropshipping\DTO\Responses\VehicleHolderIndustry;
use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the read-only industry listing published by API 2.7.0.
 *
 * It answers 403 on an older API version, so the test opts in explicitly through
 * DROPSHIPPING_API_VERSION rather than running on the default. Nothing here
 * writes anything.
 */
final class VehicleHolderIndustriesIntegrationTest extends TestCase
{
    private static ?ApiClient $client = null;

    public static function setUpBeforeClass(): void
    {
        $host = getenv('DROPSHIPPING_API_HOST');
        $clientId = getenv('DROPSHIPPING_CLIENT_ID');
        $username = getenv('DROPSHIPPING_USERNAME');
        $password = getenv('DROPSHIPPING_PASSWORD');

        if (!$host || !$clientId || !$username || !$password) {
            static::markTestSkipped('Integration test env vars not set');
        }

        $apiVersion = getenv('DROPSHIPPING_API_VERSION');

        if ($apiVersion === false || version_compare($apiVersion, '2.7.0', '<')) {
            static::markTestSkipped(
                'The vehicle holder industry listing needs API 2.7.0, set DROPSHIPPING_API_VERSION=2.7.0 to run this',
            );
        }

        $config = new DropshippingConfig($host, (int) $clientId, $username, $password);
        $psr17 = new Psr17Factory();

        self::$client = new ApiClient($config, new Client(), $psr17, $psr17);
    }

    public function test_get_industries_returns_codes_of_the_documented_shape(): void
    {
        $industries = self::$client->vehicleHolderIndustries->getAll();

        self::assertNotSame([], $industries->vehicleHolderIndustries, 'The industry listing should not be empty');

        foreach ($industries->vehicleHolderIndustries as $industry) {
            self::assertInstanceOf(VehicleHolderIndustry::class, $industry);
            self::assertMatchesRegularExpression(
                '/^[A-Z0-9]{3}$/',
                $industry->code,
                sprintf('The code "%s" does not have the documented shape', $industry->code),
            );
            self::assertNotSame('', $industry->sectionName);
            self::assertNotSame('', $industry->name);
        }
    }

    /**
     * The spec says a code may belong to several industry names, so the lookup
     * has to keep working either way.
     */
    public function test_every_code_is_findable(): void
    {
        $industries = self::$client->vehicleHolderIndustries->getAll();

        foreach ($industries->codes() as $code) {
            self::assertNotSame([], $industries->findByCode($code));
            self::assertTrue($industries->hasCode($code));
        }

        self::assertFalse($industries->hasCode('ZZZ'), 'A code outside the listing must not be reported as known');
    }

    public function test_the_sections_cover_every_industry(): void
    {
        $industries = self::$client->vehicleHolderIndustries->getAll();
        $grouped = $industries->groupedBySection();

        self::assertSame(
            count($industries->vehicleHolderIndustries),
            array_sum(array_map('count', $grouped)),
            'Grouping by section must not drop an industry',
        );
    }
}

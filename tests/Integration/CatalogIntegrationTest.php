<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Integration;

use Dropshipping\Client\ApiClient;
use Dropshipping\Configuration\DropshippingConfig;
use Dropshipping\DTO\Responses\Product;
use Dropshipping\DTO\Responses\RegistrationOffice;
use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the two read-only listings published by API 2.6.0.
 *
 * Both answer 403 on an older API version, so the test opts in explicitly
 * through DROPSHIPPING_API_VERSION rather than running on the default. Nothing
 * here writes anything.
 */
final class CatalogIntegrationTest extends TestCase
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

        if ($apiVersion === false || version_compare($apiVersion, '2.6.0', '<')) {
            static::markTestSkipped(
                'Catalog and registration office listings need API 2.6.0, set DROPSHIPPING_API_VERSION=2.6.0 to run this',
            );
        }

        $config = new DropshippingConfig($host, (int) $clientId, $username, $password);
        $psr17 = new Psr17Factory();

        self::$client = new ApiClient($config, new Client(), $psr17, $psr17);
    }

    public function test_get_catalog_returns_products_with_costs(): void
    {
        $catalog = self::$client->products->getCatalog();

        self::assertNotSame([], $catalog->products, 'The catalog should not be empty');

        foreach ($catalog->products as $product) {
            self::assertInstanceOf(Product::class, $product);
            self::assertNotSame('', $product->productType);
            self::assertGreaterThan(0, $product->productVariantId);
            self::assertTrue(
                is_numeric($product->costItem1),
                sprintf('costItem1 of variant %d is not numeric: %s', $product->productVariantId, $product->costItem1),
            );
            self::assertTrue(
                is_numeric($product->shippingCost),
                sprintf('shippingCost of variant %d is not numeric: %s', $product->productVariantId, $product->shippingCost),
            );
        }
    }

    public function test_get_catalog_entries_are_findable_by_variant_id(): void
    {
        $catalog = self::$client->products->getCatalog();
        $first = $catalog->products[0] ?? null;

        self::assertNotNull($first, 'The catalog should not be empty');
        self::assertSame($first, $catalog->findByVariantId($first->productVariantId));
    }

    public function test_get_registration_offices_returns_offices_with_fees(): void
    {
        $offices = self::$client->registrationOffices->getAll();

        self::assertNotSame([], $offices->registrationOffices, 'The office listing should not be empty');

        foreach ($offices->registrationOffices as $office) {
            self::assertInstanceOf(RegistrationOffice::class, $office);
            self::assertGreaterThan(0, $office->id);
            self::assertNotSame('', $office->regionCode);
            self::assertNotSame('', $office->name);
            self::assertTrue(
                is_numeric($office->reservationPrice),
                sprintf('reservationPrice of office %d is not numeric: %s', $office->id, $office->reservationPrice),
            );
        }
    }

    public function test_registration_offices_are_findable_by_id(): void
    {
        $offices = self::$client->registrationOffices->getAll();
        $first = $offices->registrationOffices[0] ?? null;

        self::assertNotNull($first, 'The office listing should not be empty');
        self::assertSame($first, $offices->findById($first->id));
        self::assertContains($first, $offices->findByRegionCode($first->regionCode));
    }
}

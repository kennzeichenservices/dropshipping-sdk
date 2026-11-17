<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Integration;

use Dropshipping\Client\ApiClient;
use Dropshipping\Configuration\DropshippingConfig;
use Dropshipping\Enums\VehicleRegistrationDurablePowerOfAttorneyStatus;
use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

/**
 * Reads the durable powers of attorney published by API 2.7.0.
 *
 * Read-only on purpose: creating one starts a real signing process and mails
 * the signer, so that is left to the unit tests.
 */
final class VehicleRegistrationDurablePowersOfAttorneyIntegrationTest extends TestCase
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
                'Durable powers of attorney need API 2.7.0, set DROPSHIPPING_API_VERSION=2.7.0 to run this',
            );
        }

        $config = new DropshippingConfig($host, (int) $clientId, $username, $password, apiVersion: $apiVersion);
        $psr17 = new Psr17Factory();

        self::$client = new ApiClient($config, new Client(), $psr17, $psr17);
    }

    public function test_listing_hydrates_and_every_entry_can_be_fetched_by_id(): void
    {
        $listing = self::$client->vehicleRegistrations->getDurablePowersOfAttorney();

        foreach ($listing->durablePowersOfAttorney as $powerOfAttorney) {
            self::assertNotSame(
                VehicleRegistrationDurablePowerOfAttorneyStatus::Unknown,
                $powerOfAttorney->status,
                'The API sent a status this SDK does not model',
            );

            $single = self::$client->vehicleRegistrations->getDurablePowerOfAttorney($powerOfAttorney->id);

            self::assertSame($powerOfAttorney->id, $single->id);
            self::assertSame($powerOfAttorney->status, $single->status);
        }

        $this->addToAssertionCount(1);
    }
}

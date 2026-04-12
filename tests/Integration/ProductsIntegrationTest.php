<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Integration;

use Dropshipping\Client\ApiClient;
use Dropshipping\Configuration\DropshippingConfig;
use Dropshipping\DTO\Requests\AvailabilityCheckRequest;
use Dropshipping\DTO\Responses\AvailabilityCheckResponse;
use Dropshipping\Enums\LicensePlateType;
use Dropshipping\Enums\VehicleType;
use Dropshipping\Exceptions\ApiException;
use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class ProductsIntegrationTest extends TestCase
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

        $config = new DropshippingConfig($host, (int) $clientId, $username, $password);
        $psr17 = new Psr17Factory();

        self::$client = new ApiClient($config, new Client(), $psr17, $psr17);
    }

    public function test_check_license_plate_availability(): void
    {
        $request = new AvailabilityCheckRequest(
            registrationOfficeServiceId: 602,
            city: 'B',
            middle: 'AB',
            end: '123',
            licensePlateType: LicensePlateType::Regular,
            vehicleType: VehicleType::Car,
        );

        try {
            $response = self::$client->products->checkLicensePlateAvailability($request);
        } catch (ApiException $e) {
            if (str_contains($e->getMessage(), 'unavailable at the moment')) {
                self::markTestSkipped('Registration office service temporarily unavailable: ' . $e->getMessage());
            }

            throw $e;
        }

        self::assertInstanceOf(AvailabilityCheckResponse::class, $response);
    }
}

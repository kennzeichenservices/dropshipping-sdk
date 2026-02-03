<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Integration;

use Dropshipping\Client\ApiClient;
use Dropshipping\Configuration\DropshippingConfig;
use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\LicensePlateReservationCustomization;
use Dropshipping\DTO\Requests\LicensePlateReservationRequest;
use Dropshipping\DTO\Requests\LicensePlateReservationVehicleHolder;
use Dropshipping\DTO\Responses\LicensePlateReservationResponse;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\LicensePlateType;
use Dropshipping\Enums\VehicleType;
use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class ShipmentsIntegrationTest extends TestCase
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

    public function test_create_license_plate_reservation(): void
    {
        $address = new Address(
            firstName: 'Test',
            lastName: 'User',
            gender: Gender::Unspecified,
            streetName: 'Teststraße',
            houseNumber: '1',
            zipCode: '12345',
            cityName: 'Berlin',
            countryCode: 'DE',
        );

        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');
        $customization = new LicensePlateReservationCustomization(
            registrationOfficeServiceId: 602,
            licensePlateType: LicensePlateType::Regular,
            vehicleType: VehicleType::Car,
            licensePlateNumberComponents: $components,
        );
        $vehicleHolder = new LicensePlateReservationVehicleHolder(
            address: $address,
            birthDate: '1990-01-01',
            placeOfBirth: 'Berlin',
        );

        $request = new LicensePlateReservationRequest(
            email: 'test@example.com',
            customization: $customization,
            vehicleHolder: $vehicleHolder,
            externalOrderId: 'integration-test-' . uniqid(),
        );

        $response = self::$client->shipments->createLicensePlateReservation($request);

        self::assertInstanceOf(LicensePlateReservationResponse::class, $response);
        self::assertGreaterThan(0, $response->orderId);
    }
}

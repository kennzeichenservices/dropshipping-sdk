<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Integration;

use Dropshipping\Client\ApiClient;
use Dropshipping\Configuration\DropshippingConfig;
use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\VehicleDeregistrationRequest;
use Dropshipping\DTO\Requests\VehicleDeregistrationVehicleHolder;
use Dropshipping\DTO\Responses\VehicleDeregistrationResponse;
use Dropshipping\DTO\VehicleDeregistrationCustomization;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\VehicleDeregistrationVehicleType;
use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class VehicleDeregistrationsIntegrationTest extends TestCase
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

    public function test_create_deregistration(): void
    {
        $address = new Address(
            firstName: 'Ifirst',
            lastName: 'Ilast',
            gender: Gender::Female,
            streetName: 'Istreet',
            houseNumber: '1I',
            zipCode: '22222',
            cityName: 'Icity',
            countryCode: 'DE',
        );

        $components = new EuroLicensePlateNumberComponents('BO', 'CD', '123');
        $customization = new VehicleDeregistrationCustomization(
            vehicleType: VehicleDeregistrationVehicleType::Car,
            // licensePlateType, vehicleRegistrationDate and the season months are
            // deliberately omitted: the API team confirmed they play no part in the
            // deregistration. This test is the standing proof that the live API
            // accepts the request without them.
            licensePlateType: null,
            licensePlateNumberComponents: $components,
            licensePlateReservationIncluded: false,
            vehicleIdentificationNumber: 'W0L000051T2123456',
            vehicleRegistrationCertificateSecurityCode: 'VCSC123',
            vehicleRegistrationDate: null,
            rearLicensePlateSecurityCode: 'RC2',
            frontLicensePlateSecurityCode: 'FC1',
        );

        $vehicleHolder = new VehicleDeregistrationVehicleHolder(address: $address);

        $request = new VehicleDeregistrationRequest(
            email: 'dropshipping-api-end-customer@localhost.test',
            customization: $customization,
            vehicleHolder: $vehicleHolder,
            externalOrderId: 'dropshipping-api-reservation-' . uniqid(),
        );

        $response = self::$client->vehicleDeregistrations->createDeregistration($request);

        self::assertInstanceOf(VehicleDeregistrationResponse::class, $response);
        self::assertGreaterThan(0, $response->orderId);
    }
}

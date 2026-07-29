<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Integration;

use Dropshipping\Client\ApiClient;
use Dropshipping\Configuration\DropshippingConfig;
use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\VehicleRegistrationRequest;
use Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolder;
use Dropshipping\DTO\Responses\VehicleRegistrationResponse;
use Dropshipping\DTO\VehicleRegistrationCustomization;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategy;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Enums\VehicleRegistrationServiceTypeCode;
use Dropshipping\Enums\VehicleRegistrationVehicleType;
use Dropshipping\Exceptions\ApiException;
use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class VehicleRegistrationsIntegrationTest extends TestCase
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

        // Vehicle registration only exists on a beta API version, which is never the SDK
        // default. Run this test by opting in explicitly: DROPSHIPPING_API_VERSION=2.3.2
        if (!getenv('DROPSHIPPING_API_VERSION')) {
            static::markTestSkipped(
                'Vehicle registration needs a beta API version — set DROPSHIPPING_API_VERSION=2.3.2 to run this test',
            );
        }

        $config = new DropshippingConfig($host, (int) $clientId, $username, $password);
        $psr17 = new Psr17Factory();

        self::$client = new ApiClient($config, new Client(), $psr17, $psr17);
    }

    public function test_create_registration(): void
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

        $customization = new VehicleRegistrationCustomization(
            licensePlateNumberAssignmentStrategy: VehicleRegistrationLicensePlateNumberAssignmentStrategy::Random,
            vehicleRegistrationServiceTypeCode: VehicleRegistrationServiceTypeCode::NZ,
            licensePlateNumberComponents: new EuroLicensePlateNumberComponents('BO', 'CD', '123'),
            deregistered: false,
            vehicleType: VehicleRegistrationVehicleType::Car,
            licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            electronicInsuranceConfirmationNumber: 'EVB1234',
            vehicleIdentificationNumber: 'W0L000051T2123456',
            vehicleTitleSecurityCode: 'VTSC12345678',
            iban: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
        );

        $request = new VehicleRegistrationRequest(
            email: 'dropshipping-api-end-customer@localhost.test',
            customization: $customization,
            vehicleHolder: new VehicleRegistrationVehicleHolder(address: $address),
            externalOrderId: 'dropshipping-api-registration-' . uniqid(),
        );

        try {
            $response = self::$client->vehicleRegistrations->createRegistration($request);
        } catch (ApiException $e) {
            if (in_array($e->getStatusCode(), [403, 404], true)) {
                self::markTestSkipped(sprintf(
                    'API version %s is not enabled for this client yet (HTTP %d)',
                    (string) getenv('DROPSHIPPING_API_VERSION'),
                    $e->getStatusCode(),
                ));
            }

            throw $e;
        }

        self::assertInstanceOf(VehicleRegistrationResponse::class, $response);
        self::assertGreaterThan(0, $response->orderId);
        self::assertNotSame('', $response->customerInputFormUrl);
    }
}

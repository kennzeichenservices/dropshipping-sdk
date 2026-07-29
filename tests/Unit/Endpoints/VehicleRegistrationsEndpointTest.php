<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Endpoints;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\VehicleRegistrationRequest;
use Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolder;
use Dropshipping\DTO\VehicleRegistrationCustomization;
use Dropshipping\Endpoints\VehicleRegistrations\VehicleRegistrationsEndpoint;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategy;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Enums\VehicleRegistrationServiceTypeCode;
use Dropshipping\Enums\VehicleRegistrationVehicleType;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;
use Dropshipping\Serialization\ArrayMapper;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

final class VehicleRegistrationsEndpointTest extends TestCase
{
    public function test_createRegistration_sends_post_and_returns_response(): void
    {
        $responseBody = json_encode([
            'order' => ['id' => 88],
            'identityVerificationVendorId' => 3,
            'customerInputFormUrl' => 'https://example.com/forms/xyz',
        ]);

        $sentBody = null;

        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($responseBody, &$sentBody) {
                self::assertSame('POST', $request->getMethod());
                self::assertStringEndsWith('/vehicleRegistrations/registrations', (string) $request->getUri());
                $sentBody = (string) $request->getBody();

                return new Response(200, [], $responseBody);
            });

        $psr17 = new Psr17Factory();
        $serializer = new ArrayMapper();
        $endpoint = new VehicleRegistrationsEndpoint(
            new Psr18HttpClient($mockClient),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            'https://api.example.com/dropshippingClients/1',
        );

        $customization = new VehicleRegistrationCustomization(
            licensePlateNumberAssignmentStrategy: VehicleRegistrationLicensePlateNumberAssignmentStrategy::Random,
            vehicleRegistrationServiceTypeCode: VehicleRegistrationServiceTypeCode::NZ,
            licensePlateNumberComponents: new EuroLicensePlateNumberComponents('B', 'AB', '123'),
            deregistered: false,
            vehicleType: VehicleRegistrationVehicleType::Car,
            licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            electronicInsuranceConfirmationNumber: 'ABC1234',
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleTitleSecurityCode: 'ABCDEF123456',
            iban: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
        );
        $address = new Address('Max', 'Mustermann', Gender::Male, 'Str', '1', '12345', 'Berlin', 'DE');
        $vehicleHolder = new VehicleRegistrationVehicleHolder($address);
        $request = new VehicleRegistrationRequest('test@example.com', $customization, $vehicleHolder);

        $response = $endpoint->createRegistration($request);

        self::assertSame(88, $response->orderId);
        self::assertSame(3, $response->identityVerificationVendorId);
        self::assertSame('https://example.com/forms/xyz', $response->customerInputFormUrl);

        $decoded = json_decode((string) $sentBody, true);
        self::assertSame('VEHICLE_REGISTRATION', $decoded['customization']['productType']);
    }
}

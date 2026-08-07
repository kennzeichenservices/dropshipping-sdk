<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Endpoints;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Address;
use Dropshipping\DTO\Requests\VehicleRegistrationRequest;
use Dropshipping\DTO\Requests\VehicleRegistrationVehicleHolder;
use Dropshipping\DTO\VehicleRegistrationCustomization;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom;
use Dropshipping\Endpoints\VehicleRegistrations\VehicleRegistrationsEndpoint;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Enums\VehicleRegistrationServiceTypeCode;
use Dropshipping\Enums\VehicleRegistrationVehicleType;
use Dropshipping\Exceptions\ApiException;
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
        $responseBody = json_encode(['order' => ['id' => 88]]);

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
            licensePlateNumberAssignmentStrategy: new VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom(
                licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            ),
            vehicleRegistrationServiceTypeCode: VehicleRegistrationServiceTypeCode::NZ,
            deregistered: false,
            vehicleType: VehicleRegistrationVehicleType::Car,
            electronicInsuranceConfirmationNumber: 'ABC1234',
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleTitleSecurityCode: 'ABCDEF123456',
            iban: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
        );
        $address = new Address('Max', 'Mustermann', Gender::Male, 'Str', '1', '12345', 'Berlin', 'DE');
        $vehicleHolder = new VehicleRegistrationVehicleHolder(
            address: $address,
            placeOfBirth: 'Berlin',
            birthDate: '1990-01-31',
        );
        $request = new VehicleRegistrationRequest('test@example.com', $customization, $vehicleHolder);

        $response = $endpoint->createRegistration($request);

        self::assertSame(88, $response->orderId);

        $decoded = json_decode((string) $sentBody, true);
        self::assertSame('VEHICLE_REGISTRATION', $decoded['customization']['productType']);
    }

    public function test_downloadFileContent_returns_raw_binary_and_escapes_the_key(): void
    {
        $binary = "%PDF-1.4\n\x00\x01binary\xff";

        $endpoint = $this->endpointReturning(
            new Response(200, [], $binary),
            function (RequestInterface $request): void {
                self::assertSame('GET', $request->getMethod());
                self::assertStringEndsWith(
                    '/vehicleRegistrations/files/content/key%2Fwith%20chars',
                    (string) $request->getUri(),
                );
            },
        );

        self::assertSame($binary, $endpoint->downloadFileContent('key/with chars'));
    }

    public function test_downloadFileContent_throws_api_exception_with_the_error_message(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(404, ['X-Trace-Id' => 'trace-9'], '{"error":"File access key expired"}'),
        );

        try {
            $endpoint->downloadFileContent('expired-key');
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('File access key expired', $e->getMessage());
            self::assertSame(404, $e->getStatusCode());
            self::assertSame('trace-9', $e->getTraceId());
        }
    }

    /**
     * A binary download is the most likely endpoint to be fronted by a proxy that answers
     * with an HTML error page, so it must not leak a raw JsonException to the caller.
     */
    public function test_downloadFileContent_reports_a_non_json_error_body_as_api_exception(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(502, [], '<html><title>502 Bad Gateway</title></html>'),
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('API request failed');

        $endpoint->downloadFileContent('some-key');
    }

    /**
     * @param (callable(RequestInterface): void)|null $assertRequest
     */
    private function endpointReturning(
        Response $response,
        ?callable $assertRequest = null,
    ): VehicleRegistrationsEndpoint {
        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($response, $assertRequest) {
                if ($assertRequest !== null) {
                    $assertRequest($request);
                }

                return $response;
            });

        $psr17 = new Psr17Factory();
        $serializer = new ArrayMapper();

        return new VehicleRegistrationsEndpoint(
            new Psr18HttpClient($mockClient),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            'https://api.example.com/dropshippingClients/1',
        );
    }
}

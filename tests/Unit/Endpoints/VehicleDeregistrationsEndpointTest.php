<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Endpoints;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\VehicleDeregistrationRequest;
use Dropshipping\DTO\Requests\VehicleDeregistrationVehicleHolder;
use Dropshipping\DTO\VehicleDeregistrationCustomization;
use Dropshipping\Endpoints\VehicleDeregistrations\VehicleDeregistrationsEndpoint;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\VehicleDeregistrationLicensePlateType;
use Dropshipping\Enums\VehicleDeregistrationVehicleType;
use Dropshipping\Exceptions\ApiException;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;
use Dropshipping\Serialization\ArrayMapper;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

final class VehicleDeregistrationsEndpointTest extends TestCase
{
    public function test_createDeregistration_sends_post_and_returns_response(): void
    {
        $responseBody = json_encode(['order' => ['id' => 77]]);

        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($responseBody) {
                self::assertSame('POST', $request->getMethod());
                self::assertStringEndsWith('/vehicleDeregistrations/deregistrations', (string) $request->getUri());
                return new Response(200, [], $responseBody);
            });

        $psr17 = new Psr17Factory();
        $serializer = new ArrayMapper();
        $endpoint = new VehicleDeregistrationsEndpoint(
            new Psr18HttpClient($mockClient),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            'https://api.example.com/dropshippingClients/1',
        );

        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');
        $customization = new VehicleDeregistrationCustomization(
            vehicleType: VehicleDeregistrationVehicleType::Car,
            licensePlateType: VehicleDeregistrationLicensePlateType::Regular,
            licensePlateNumberComponents: $components,
            licensePlateReservationIncluded: false,
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleRegistrationCertificateSecurityCode: 'SEC123',
            vehicleRegistrationDate: '2020-01-15',
            rearLicensePlateSecurityCode: 'REAR1',
        );
        $address = new Address('Max', 'Mustermann', Gender::Male, 'Str', '1', '12345', 'Berlin', 'DE');
        $vehicleHolder = new VehicleDeregistrationVehicleHolder($address);
        $request = new VehicleDeregistrationRequest('test@example.com', $customization, $vehicleHolder);

        $response = $endpoint->createDeregistration($request);

        self::assertSame(77, $response->orderId);
    }

    public function test_downloadFileContent_returns_raw_binary_and_escapes_the_key(): void
    {
        $binary = "%PDF-1.4\n\x00\x01binary\xff";

        $endpoint = $this->endpointReturning(
            new Response(200, [], $binary),
            function (RequestInterface $request): void {
                self::assertSame('GET', $request->getMethod());
                self::assertStringEndsWith(
                    '/vehicleDeregistrations/files/content/key%2Fwith%20chars',
                    (string) $request->getUri(),
                );
            },
        );

        self::assertSame($binary, $endpoint->downloadFileContent('key/with chars'));
    }

    public function test_downloadFileContent_throws_api_exception_with_the_error_message(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(404, ['X-Trace-Id' => 'trace-5'], '{"error":"File access key expired"}'),
        );

        try {
            $endpoint->downloadFileContent('expired-key');
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('File access key expired', $e->getMessage());
            self::assertSame(404, $e->getStatusCode());
            self::assertSame('trace-5', $e->getTraceId());
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
    ): VehicleDeregistrationsEndpoint {
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

        return new VehicleDeregistrationsEndpoint(
            new Psr18HttpClient($mockClient),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            'https://api.example.com/dropshippingClients/1',
        );
    }
}

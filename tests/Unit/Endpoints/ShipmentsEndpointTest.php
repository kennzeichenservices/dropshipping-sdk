<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Endpoints;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\LicensePlateReservationCustomization;
use Dropshipping\DTO\Requests\LicensePlateReservationRequest;
use Dropshipping\DTO\Requests\LicensePlateReservationVehicleHolder;
use Dropshipping\Endpoints\Shipments\ShipmentsEndpoint;
use Dropshipping\Enums\Gender;
use Dropshipping\Enums\LicensePlateType;
use Dropshipping\Enums\VehicleType;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;
use Dropshipping\Serialization\ArrayMapper;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

final class ShipmentsEndpointTest extends TestCase
{
    public function test_createLicensePlateReservation_sends_post_and_returns_response(): void
    {
        $responseBody = json_encode(['order' => ['id' => 99]]);

        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($responseBody) {
                self::assertSame('POST', $request->getMethod());
                self::assertStringEndsWith('/licensePlateReservations/reservations', (string) $request->getUri());
                return new Response(200, [], $responseBody);
            });

        $psr17 = new Psr17Factory();
        $serializer = new ArrayMapper();
        $endpoint = new ShipmentsEndpoint(
            new Psr18HttpClient($mockClient),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            'https://api.example.com/dropshippingClients/1',
        );

        $address = new Address('Max', 'Mustermann', Gender::Male, 'Str', '1', '12345', 'Berlin', 'DE');
        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');
        $customization = new LicensePlateReservationCustomization(1, LicensePlateType::Regular, VehicleType::Car, $components);
        $vehicleHolder = new LicensePlateReservationVehicleHolder($address);

        $request = new LicensePlateReservationRequest('test@example.com', $customization, $vehicleHolder);
        $response = $endpoint->createLicensePlateReservation($request);

        self::assertSame(99, $response->orderId);
    }
}

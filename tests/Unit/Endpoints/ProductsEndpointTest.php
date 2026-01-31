<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Endpoints;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Requests\AvailabilityCheckRequest;
use Dropshipping\Endpoints\Products\ProductsEndpoint;
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

final class ProductsEndpointTest extends TestCase
{
    public function test_checkLicensePlateAvailability_sends_post_and_returns_response(): void
    {
        $responseBody = json_encode([
            'availableLicensePlateNumbers' => [
                ['city' => 'B', 'middle' => 'AB', 'end' => '123'],
            ],
        ]);

        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($responseBody) {
                self::assertSame('POST', $request->getMethod());
                self::assertStringEndsWith('/licensePlateReservations/availabilityChecks', (string) $request->getUri());
                return new Response(200, [], $responseBody);
            });

        $psr17 = new Psr17Factory();
        $serializer = new ArrayMapper();
        $endpoint = new ProductsEndpoint(
            new Psr18HttpClient($mockClient),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            'https://api.example.com/dropshippingClients/1',
        );

        $request = new AvailabilityCheckRequest(100, 'B', 'AB', '123', LicensePlateType::Regular, VehicleType::Car);
        $response = $endpoint->checkLicensePlateAvailability($request);

        self::assertCount(1, $response->availableLicensePlateNumbers);
        self::assertSame('B', $response->availableLicensePlateNumbers[0]->city);
    }
}

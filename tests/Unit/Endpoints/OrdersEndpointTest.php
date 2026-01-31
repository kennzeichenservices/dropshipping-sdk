<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Endpoints;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Address;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\LicensePlateItemCustomization;
use Dropshipping\DTO\OrderItem;
use Dropshipping\DTO\Requests\EmissionStickerOrderRequest;
use Dropshipping\DTO\Requests\OrderCreationRequest;
use Dropshipping\DTO\Requests\ReshippedOrderRequest;
use Dropshipping\Endpoints\Orders\OrdersEndpoint;
use Dropshipping\Enums\Gender;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;
use Dropshipping\Serialization\ArrayMapper;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

final class OrdersEndpointTest extends TestCase
{
    private ClientInterface $mockClient;
    private OrdersEndpoint $endpoint;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(ClientInterface::class);
        $psr17 = new Psr17Factory();
        $serializer = new ArrayMapper();

        $this->endpoint = new OrdersEndpoint(
            new Psr18HttpClient($this->mockClient),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            $serializer,
            'https://api.example.com/dropshippingClients/1',
            $psr17,
        );
    }

    private function createAddress(): Address
    {
        return new Address('Max', 'Mustermann', Gender::Male, 'Str', '1', '12345', 'Berlin', 'DE');
    }

    public function test_create_sends_post_and_returns_response(): void
    {
        $responseBody = json_encode([
            'id' => 42,
            'deliveries' => [['id' => 1, 'items' => [['orderItemIndex' => 0]]]],
        ]);

        $this->mockClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($responseBody) {
                self::assertSame('POST', $request->getMethod());
                self::assertStringEndsWith('/orders', (string) $request->getUri());
                self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
                return new Response(201, [], $responseBody);
            });

        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');
        $item = new OrderItem(1, 'Kennzeichen', 'LP-001', 2, new LicensePlateItemCustomization($components));
        $request = new OrderCreationRequest('ext-1', 'test@example.com', $this->createAddress(), $this->createAddress(), [$item]);

        $response = $this->endpoint->create($request);

        self::assertSame(42, $response->id);
        self::assertCount(1, $response->deliveries);
    }

    public function test_createReshippedOrder_sends_post_to_correct_url(): void
    {
        $responseBody = json_encode(['id' => 10, 'deliveries' => []]);

        $this->mockClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($responseBody) {
                self::assertStringEndsWith('/orders/reshippedOrders', (string) $request->getUri());
                return new Response(201, [], $responseBody);
            });

        $request = new ReshippedOrderRequest('ext-2', 5, $this->createAddress(), $this->createAddress());
        $response = $this->endpoint->createReshippedOrder($request);

        self::assertSame(10, $response->id);
    }

    public function test_createEmissionStickerOrder_sends_multipart_post(): void
    {
        $responseBody = json_encode(['id' => 20, 'delivery' => ['id' => 30]]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'test') . '.pdf';
        file_put_contents($tmpFile, 'fake-pdf');

        try {
            $this->mockClient
                ->expects($this->once())
                ->method('sendRequest')
                ->willReturnCallback(function (RequestInterface $request) use ($responseBody) {
                    self::assertSame('POST', $request->getMethod());
                    self::assertStringEndsWith('/orders/emissionStickerOrders', (string) $request->getUri());
                    self::assertStringContainsString('multipart/form-data', $request->getHeaderLine('Content-Type'));

                    $body = (string) $request->getBody();
                    self::assertStringContainsString('name="order"', $body);
                    self::assertStringContainsString('fake-pdf', $body);

                    return new Response(201, [], $responseBody);
                });

            $components = new EuroLicensePlateNumberComponents('BO', 'CD', '123');
            $request = new EmissionStickerOrderRequest(
                externalId: 'ext-3',
                email: 'test@example.com',
                deliveryAddress: $this->createAddress(),
                invoiceAddress: $this->createAddress(),
                licensePlateNumberComponents: $components,
                electric: true,
                emissionKeyNumber: '35A0',
                filePaths: [$tmpFile],
            );

            $response = $this->endpoint->createEmissionStickerOrder($request);

            self::assertSame(20, $response->id);
            self::assertSame(30, $response->deliveryId);
        } finally {
            unlink($tmpFile);
        }
    }
}

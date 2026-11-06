<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Endpoints;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Requests\AvailabilityCheckRequest;
use Dropshipping\Endpoints\Products\ProductsEndpoint;
use Dropshipping\Enums\LicensePlateType;
use Dropshipping\Enums\VehicleType;
use Dropshipping\Exceptions\ApiException;
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

    public function test_getCatalog_sends_an_authenticated_get_and_returns_the_catalog(): void
    {
        $body = json_encode([
            'products' => [
                [
                    'productType' => 'LICENSE_PLATE',
                    'productVariantName' => 'Euro-Kennzeichen 520 x 110 mm',
                    'productVariantId' => 2,
                    'utschSku' => 'SKU-123',
                    'supplierId' => 1,
                    'costItem1' => '9.90',
                    'costItem2' => '7.90',
                    'costItem3' => '7.90',
                    'costItem4' => '7.90',
                    'licensePlateNumberLength' => 9,
                    'shippingCost' => '3.45',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $endpoint = $this->endpointReturning(
            new Response(200, [], $body),
            function (RequestInterface $request): void {
                self::assertSame('GET', $request->getMethod());
                self::assertStringEndsWith('/products', (string) $request->getUri());
                self::assertNotSame('', $request->getHeaderLine('Authorization'));
                self::assertSame('', (string) $request->getBody());
            },
        );

        $response = $endpoint->getCatalog();

        self::assertCount(1, $response->products);
        self::assertSame(2, $response->findByVariantId(2)?->productVariantId);
        self::assertSame('9.90', $response->products[0]->costItem1);
    }

    public function test_getCatalog_returns_an_empty_catalog_without_failing(): void
    {
        $endpoint = $this->endpointReturning(new Response(200, [], '{"products":[]}'));

        self::assertSame([], $endpoint->getCatalog()->products);
    }

    /**
     * A client not entitled to API 2.6.0 is answered 403 on every endpoint, so the
     * catalog has to report that as an ApiException rather than an empty result.
     */
    public function test_getCatalog_reports_a_403_as_api_exception(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(403, ['X-Trace-Id' => 'trace-7'], '{"error":"Version not enabled"}'),
        );

        try {
            $endpoint->getCatalog();
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('Version not enabled', $e->getMessage());
            self::assertSame(403, $e->getStatusCode());
            self::assertSame('trace-7', $e->getTraceId());
        }
    }

    public function test_getCatalog_reports_a_non_json_gateway_error_as_api_exception(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(502, [], '<html><title>502 Bad Gateway</title></html>'),
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('API request failed');

        $endpoint->getCatalog();
    }

    public function test_getCatalog_reports_a_malformed_success_body_as_api_exception(): void
    {
        $endpoint = $this->endpointReturning(new Response(200, [], '{"products":'));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('malformed JSON');

        $endpoint->getCatalog();
    }

    /**
     * @param (callable(RequestInterface): void)|null $assertRequest
     */
    private function endpointReturning(
        Response $response,
        ?callable $assertRequest = null,
    ): ProductsEndpoint {
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

        return new ProductsEndpoint(
            new Psr18HttpClient($mockClient),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            'https://api.example.com/dropshippingClients/1',
        );
    }
}

<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Endpoints;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\Endpoints\VehicleHolderIndustries\VehicleHolderIndustriesEndpoint;
use Dropshipping\Exceptions\ApiException;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;
use Dropshipping\Serialization\ArrayMapper;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

final class VehicleHolderIndustriesEndpointTest extends TestCase
{
    public function test_getAll_sends_an_authenticated_get_and_returns_the_listing(): void
    {
        $body = json_encode([
            'vehicleHolderIndustries' => [
                [
                    'code' => 'G01',
                    'sectionName' => 'G Handel',
                    'name' => 'Handel mit Kraftfahrzeugen, Kraftwagenteilen und -zubehör',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $endpoint = $this->endpointReturning(
            new Response(200, [], $body),
            function (RequestInterface $request): void {
                self::assertSame('GET', $request->getMethod());
                self::assertStringEndsWith('/vehicleHolderIndustries', (string) $request->getUri());
                self::assertNotSame('', $request->getHeaderLine('Authorization'));
                self::assertSame('', (string) $request->getBody());
            },
        );

        $response = $endpoint->getAll();

        self::assertCount(1, $response->vehicleHolderIndustries);
        self::assertSame('G01', $response->vehicleHolderIndustries[0]->code);
        self::assertSame('G Handel', $response->vehicleHolderIndustries[0]->sectionName);
    }

    public function test_getAll_returns_an_empty_listing_without_failing(): void
    {
        $endpoint = $this->endpointReturning(new Response(200, [], '{"vehicleHolderIndustries":[]}'));

        self::assertSame([], $endpoint->getAll()->vehicleHolderIndustries);
    }

    /**
     * A client not entitled to API 2.7.0 is answered 403 on every endpoint, so the
     * listing has to report that as an ApiException rather than an empty result.
     */
    public function test_getAll_reports_a_403_as_api_exception(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(403, ['X-Trace-Id' => 'trace-7'], '{"error":"Version not enabled"}'),
        );

        try {
            $endpoint->getAll();
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('Version not enabled', $e->getMessage());
            self::assertSame(403, $e->getStatusCode());
            self::assertSame('trace-7', $e->getTraceId());
        }
    }

    public function test_getAll_reports_a_non_json_gateway_error_as_api_exception(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(502, [], '<html><title>502 Bad Gateway</title></html>'),
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('API request failed');

        $endpoint->getAll();
    }

    public function test_getAll_reports_a_malformed_success_body_as_api_exception(): void
    {
        $endpoint = $this->endpointReturning(new Response(200, [], '{"vehicleHolderIndustries":'));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('malformed JSON');

        $endpoint->getAll();
    }

    /**
     * @param (callable(RequestInterface): void)|null $assertRequest
     */
    private function endpointReturning(
        Response $response,
        ?callable $assertRequest = null,
    ): VehicleHolderIndustriesEndpoint {
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

        return new VehicleHolderIndustriesEndpoint(
            new Psr18HttpClient($mockClient),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            'https://api.example.com/dropshippingClients/1',
        );
    }
}

<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Endpoints;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\Endpoints\RegistrationOffices\RegistrationOfficesEndpoint;
use Dropshipping\Exceptions\ApiException;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;
use Dropshipping\Serialization\ArrayMapper;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

final class RegistrationOfficesEndpointTest extends TestCase
{
    public function test_getAll_sends_an_authenticated_get_and_returns_the_listing(): void
    {
        $body = json_encode([
            'registrationOffices' => [
                [
                    'id' => 1,
                    'regionCode' => 'BO',
                    'name' => 'Bochum (Stadt)',
                    'district' => 'Stadt Bochum',
                    'stateName' => 'Nordrhein-Westfalen',
                    'reservationPrice' => '0.00',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $endpoint = $this->endpointReturning(
            new Response(200, [], $body),
            function (RequestInterface $request): void {
                self::assertSame('GET', $request->getMethod());
                self::assertStringEndsWith('/registrationOffices', (string) $request->getUri());
                self::assertNotSame('', $request->getHeaderLine('Authorization'));
                self::assertSame('', (string) $request->getBody());
            },
        );

        $response = $endpoint->getAll();

        self::assertCount(1, $response->registrationOffices);
        self::assertSame('Bochum (Stadt)', $response->registrationOffices[0]->name);
    }

    public function test_getAll_returns_an_empty_listing_without_failing(): void
    {
        $endpoint = $this->endpointReturning(new Response(200, [], '{"registrationOffices":[]}'));

        self::assertSame([], $endpoint->getAll()->registrationOffices);
    }

    /**
     * A client not entitled to API 2.6.0 is answered 403 on every endpoint, so the
     * listing has to report that as an ApiException rather than an empty result.
     */
    public function test_getAll_reports_a_403_as_api_exception(): void
    {
        $endpoint = $this->endpointReturning(
            new Response(403, ['X-Trace-Id' => 'trace-6'], '{"error":"Version not enabled"}'),
        );

        try {
            $endpoint->getAll();
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('Version not enabled', $e->getMessage());
            self::assertSame(403, $e->getStatusCode());
            self::assertSame('trace-6', $e->getTraceId());
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
        $endpoint = $this->endpointReturning(new Response(200, [], '{"registrationOffices":'));

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
    ): RegistrationOfficesEndpoint {
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

        return new RegistrationOfficesEndpoint(
            new Psr18HttpClient($mockClient),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            'https://api.example.com/dropshippingClients/1',
        );
    }
}

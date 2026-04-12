<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Endpoints;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Requests\GksConfigurationCompany;
use Dropshipping\DTO\Requests\GksConfigurationWriteRequest;
use Dropshipping\DTO\Responses\GksConfigurationOverviewsResponse;
use Dropshipping\DTO\Responses\OverviewGksConfiguration;
use Dropshipping\Endpoints\GksConfigurations\GksConfigurationsEndpoint;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;
use Dropshipping\Serialization\ArrayMapper;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

final class GksConfigurationsEndpointTest extends TestCase
{
    public function test_create_sends_post_and_returns_overview(): void
    {
        $responseBody = json_encode(['id' => 'uuid-1', 'name' => 'New Config']);

        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($responseBody) {
                self::assertSame('POST', $request->getMethod());
                self::assertStringEndsWith('/gksConfigurations', (string) $request->getUri());
                return new Response(201, [], $responseBody);
            });

        $endpoint = $this->createEndpoint($mockClient);
        $request = $this->createWriteRequest();

        $result = $endpoint->create($request);

        self::assertInstanceOf(OverviewGksConfiguration::class, $result);
        self::assertSame('uuid-1', $result->id);
        self::assertSame('New Config', $result->name);
    }

    public function test_update_sends_put(): void
    {
        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) {
                self::assertSame('PUT', $request->getMethod());
                self::assertStringEndsWith('/gksConfigurations/uuid-1', (string) $request->getUri());
                return new Response(204, [], '');
            });

        $endpoint = $this->createEndpoint($mockClient);
        $request = $this->createWriteRequest();

        $endpoint->update('uuid-1', $request);
    }

    public function test_getOverviews_sends_get_and_returns_list(): void
    {
        $responseBody = json_encode([
            'overviewGksConfigurations' => [
                ['id' => 'uuid-1', 'name' => 'Config A'],
                ['id' => 'uuid-2', 'name' => 'Config B'],
            ],
        ]);

        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($responseBody) {
                self::assertSame('GET', $request->getMethod());
                self::assertStringEndsWith('/gksConfigurations/overviews', (string) $request->getUri());
                return new Response(200, [], $responseBody);
            });

        $endpoint = $this->createEndpoint($mockClient);

        $result = $endpoint->getOverviews();

        self::assertInstanceOf(GksConfigurationOverviewsResponse::class, $result);
        self::assertCount(2, $result->overviewGksConfigurations);
    }

    public function test_getOverview_sends_get_and_returns_single(): void
    {
        $responseBody = json_encode(['id' => 'uuid-1', 'name' => 'Config A']);

        $mockClient = $this->createMock(ClientInterface::class);
        $mockClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($responseBody) {
                self::assertSame('GET', $request->getMethod());
                self::assertStringEndsWith('/gksConfigurations/overviews/uuid-1', (string) $request->getUri());
                return new Response(200, [], $responseBody);
            });

        $endpoint = $this->createEndpoint($mockClient);

        $result = $endpoint->getOverview('uuid-1');

        self::assertInstanceOf(OverviewGksConfiguration::class, $result);
        self::assertSame('uuid-1', $result->id);
    }

    private function createEndpoint(ClientInterface $mockClient): GksConfigurationsEndpoint
    {
        $psr17 = new Psr17Factory();
        $serializer = new ArrayMapper();

        return new GksConfigurationsEndpoint(
            new Psr18HttpClient($mockClient),
            new RequestFactory($psr17, $psr17, new ApiKeyAuthenticator('user', 'pass'), $serializer),
            new ResponseMapper($serializer),
            'https://api.example.com/dropshippingClients/1',
        );
    }

    private function createWriteRequest(): GksConfigurationWriteRequest
    {
        $company = new GksConfigurationCompany('Acme', 'Str', '1', '12345', 'Berlin', 'DE');

        return new GksConfigurationWriteRequest(
            name: 'Test Config',
            kopaKey: 'kopa-123',
            username: 'gks-user',
            password: 'gks-pass',
            publicKeyCertificate: 'cert',
            privateKey: 'key',
            company: $company,
        );
    }
}

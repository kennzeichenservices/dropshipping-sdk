<?php

declare(strict_types=1);

namespace Dropshipping\Client;

use Dropshipping\Client\Authentication\ApiKeyAuthenticator;
use Dropshipping\Configuration\DropshippingConfig;
use Dropshipping\Contracts\SerializerInterface;
use Dropshipping\Endpoints\GksConfigurations\GksConfigurationsEndpoint;
use Dropshipping\Endpoints\Orders\OrdersEndpoint;
use Dropshipping\Endpoints\Products\ProductsEndpoint;
use Dropshipping\Endpoints\Shipments\ShipmentsEndpoint;
use Dropshipping\Endpoints\VehicleDeregistrations\VehicleDeregistrationsEndpoint;
use Dropshipping\Endpoints\VehicleRegistrations\VehicleRegistrationsEndpoint;
use Dropshipping\Endpoints\Webhooks\WebhooksEndpoint;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;
use Dropshipping\Serialization\ArrayMapper;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Main entry point for the dropshipping SDK.
 *
 * Orchestrates the HTTP client, authentication, request building and response
 * mapping. Exposes endpoint objects for orders, shipments, products, webhooks,
 * GKS configurations, vehicle deregistrations and vehicle registrations as
 * public readonly properties.
 */
final class ApiClient
{
    private readonly Psr18HttpClient $httpClient;
    private readonly RequestFactory $requestFactory;
    private readonly ResponseMapper $responseMapper;
    private readonly string $baseUrl;

    public readonly OrdersEndpoint $orders;
    public readonly ShipmentsEndpoint $shipments;
    public readonly ProductsEndpoint $products;
    public readonly WebhooksEndpoint $webhooks;
    public readonly GksConfigurationsEndpoint $gksConfigurations;
    public readonly VehicleDeregistrationsEndpoint $vehicleDeregistrations;

    /** @experimental Vehicle registration is a beta feature of the dropshipping API (2.4.0). */
    public readonly VehicleRegistrationsEndpoint $vehicleRegistrations;

    public function __construct(
        DropshippingConfig $config,
        ?ClientInterface $httpClient,
        RequestFactoryInterface $psrRequestFactory,
        StreamFactoryInterface $streamFactory,
        ?SerializerInterface $serializer = null,
    ) {
        $serializer ??= new ArrayMapper();
        $this->baseUrl = $config->getBaseUrl();
        $this->httpClient = new Psr18HttpClient(
            $httpClient ?? new GuzzleClient(['timeout' => $config->getTimeout()]),
        );

        $authenticator = new ApiKeyAuthenticator(
            $config->getUsername(),
            $config->getPassword(),
        );

        $this->requestFactory = new RequestFactory(
            $psrRequestFactory,
            $streamFactory,
            $authenticator,
            $serializer,
        );

        $this->responseMapper = new ResponseMapper($serializer);

        $this->orders = new OrdersEndpoint(
            $this->httpClient,
            $this->requestFactory,
            $this->responseMapper,
            $serializer,
            $this->baseUrl,
            $streamFactory,
        );

        $this->shipments = new ShipmentsEndpoint(
            $this->httpClient,
            $this->requestFactory,
            $this->responseMapper,
            $this->baseUrl,
        );

        $this->products = new ProductsEndpoint(
            $this->httpClient,
            $this->requestFactory,
            $this->responseMapper,
            $this->baseUrl,
        );

        $this->webhooks = new WebhooksEndpoint();

        $this->gksConfigurations = new GksConfigurationsEndpoint(
            $this->httpClient,
            $this->requestFactory,
            $this->responseMapper,
            $this->baseUrl,
        );

        $this->vehicleDeregistrations = new VehicleDeregistrationsEndpoint(
            $this->httpClient,
            $this->requestFactory,
            $this->responseMapper,
            $this->baseUrl,
        );

        $this->vehicleRegistrations = new VehicleRegistrationsEndpoint(
            $this->httpClient,
            $this->requestFactory,
            $this->responseMapper,
            $this->baseUrl,
        );
    }
}

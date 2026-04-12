<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\GksConfigurations;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Requests\GksConfigurationWriteRequest;
use Dropshipping\DTO\Responses\GksConfigurationOverviewsResponse;
use Dropshipping\DTO\Responses\OverviewGksConfiguration;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;

/**
 * API endpoint for GKS (Großkundenschnittstelle) configuration operations.
 *
 * Provides CRUD methods for managing GKS configurations used
 * to connect to the KBA (Kraftfahrt-Bundesamt) interface.
 */
final class GksConfigurationsEndpoint
{
    /**
     * @param Psr18HttpClient $httpClient      HTTP client for sending requests.
     * @param RequestFactory  $requestFactory  Factory for creating HTTP request objects.
     * @param ResponseMapper  $responseMapper  Mapper for processing HTTP responses.
     * @param string          $baseUrl         Base URL of the dropshipping API.
     */
    public function __construct(
        private readonly Psr18HttpClient $httpClient,
        private readonly RequestFactory $requestFactory,
        private readonly ResponseMapper $responseMapper,
        private readonly string $baseUrl,
    ) {
    }

    /**
     * Create a new GKS configuration.
     *
     * @param GksConfigurationWriteRequest $request The configuration data to create.
     *
     * @return OverviewGksConfiguration The created configuration overview.
     */
    public function create(GksConfigurationWriteRequest $request): OverviewGksConfiguration
    {
        $httpRequest = $this->requestFactory->createJsonRequest(
            'POST',
            $this->baseUrl . '/gksConfigurations',
            $request->toArray(),
        );

        $response = $this->httpClient->sendRequest($httpRequest);
        $data = $this->responseMapper->mapResponse($response, [201]);

        return OverviewGksConfiguration::fromArray($data);
    }

    /**
     * Update an existing GKS configuration.
     *
     * @param string                       $id      UUID of the configuration to update.
     * @param GksConfigurationWriteRequest $request The updated configuration data.
     *
     * @return void
     */
    public function update(string $id, GksConfigurationWriteRequest $request): void
    {
        $httpRequest = $this->requestFactory->createJsonRequest(
            'PUT',
            $this->baseUrl . '/gksConfigurations/' . $id,
            $request->toArray(),
        );

        $response = $this->httpClient->sendRequest($httpRequest);
        $this->responseMapper->mapResponse($response, [204]);
    }

    /**
     * Get a list of all GKS configuration overviews.
     *
     * @return GksConfigurationOverviewsResponse The list of configuration overviews.
     */
    public function getOverviews(): GksConfigurationOverviewsResponse
    {
        $httpRequest = $this->requestFactory->createJsonRequest(
            'GET',
            $this->baseUrl . '/gksConfigurations/overviews',
            [],
        );

        $response = $this->httpClient->sendRequest($httpRequest);
        $data = $this->responseMapper->mapResponse($response);

        return GksConfigurationOverviewsResponse::fromArray($data);
    }

    /**
     * Get a single GKS configuration overview by ID.
     *
     * @param string $id UUID of the configuration to retrieve.
     *
     * @return OverviewGksConfiguration The configuration overview.
     */
    public function getOverview(string $id): OverviewGksConfiguration
    {
        $httpRequest = $this->requestFactory->createJsonRequest(
            'GET',
            $this->baseUrl . '/gksConfigurations/overviews/' . $id,
            [],
        );

        $response = $this->httpClient->sendRequest($httpRequest);
        $data = $this->responseMapper->mapResponse($response);

        return OverviewGksConfiguration::fromArray($data);
    }
}

<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\VehicleHolderIndustries;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Responses\VehicleHolderIndustriesResponse;
use Dropshipping\Exceptions\ApiException;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;

/**
 * API endpoint for the industry (Wirtschaftszweig) listing of legal person
 * vehicle holders.
 *
 * Requires API 2.7.0, which publishes the listing together with the legal person
 * vehicle holder.
 */
final class VehicleHolderIndustriesEndpoint
{
    /**
     * @param Psr18HttpClient $httpClient     HTTP client for sending requests.
     * @param RequestFactory  $requestFactory Factory for creating HTTP request objects.
     * @param ResponseMapper  $responseMapper Mapper for processing HTTP responses.
     * @param string          $baseUrl        Base URL of the dropshipping API.
     */
    public function __construct(
        private readonly Psr18HttpClient $httpClient,
        private readonly RequestFactory $requestFactory,
        private readonly ResponseMapper $responseMapper,
        private readonly string $baseUrl,
    ) {
    }

    /**
     * Get every industry a legal person can be registered under.
     *
     * The codes are the accepted values for `vehicleHolder.industryCode` of a
     * vehicle registration ordered for a legal person. The listing is not
     * paginated and mirrors a KBA reference table, so it is worth caching on the
     * caller's side rather than fetching it per order.
     *
     * @return VehicleHolderIndustriesResponse The industries.
     *
     * @throws ApiException When the API responds with an unexpected status code, which
     *                      includes 403 on an API version below 2.7.0.
     */
    public function getAll(): VehicleHolderIndustriesResponse
    {
        $httpRequest = $this->requestFactory->createJsonRequest(
            'GET',
            $this->baseUrl . '/vehicleHolderIndustries',
            [],
        );

        $response = $this->httpClient->sendRequest($httpRequest);
        $data = $this->responseMapper->mapResponse($response);

        return VehicleHolderIndustriesResponse::fromArray($data);
    }
}

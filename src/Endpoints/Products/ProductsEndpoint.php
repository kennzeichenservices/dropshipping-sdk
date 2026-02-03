<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\Products;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Requests\AvailabilityCheckRequest;
use Dropshipping\DTO\Responses\AvailabilityCheckResponse;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;

/**
 * API endpoint for product-related operations.
 */
final class ProductsEndpoint
{
    /**
     * @param Psr18HttpClient $httpClient   HTTP client for sending requests
     * @param RequestFactory  $requestFactory Factory for creating HTTP request objects
     * @param ResponseMapper  $responseMapper Mapper for processing HTTP responses
     * @param string          $baseUrl        Base URL of the dropshipping API
     */
    public function __construct(
        private readonly Psr18HttpClient $httpClient,
        private readonly RequestFactory $requestFactory,
        private readonly ResponseMapper $responseMapper,
        private readonly string $baseUrl,
    ) {
    }

    /**
     * Check whether specific license plate number combinations are available at a registration office.
     *
     * @param AvailabilityCheckRequest $request The availability check request containing the license plate details
     *
     * @return AvailabilityCheckResponse The availability check result
     */
    public function checkLicensePlateAvailability(AvailabilityCheckRequest $request): AvailabilityCheckResponse
    {
        $httpRequest = $this->requestFactory->createJsonRequest(
            'POST',
            $this->baseUrl . '/licensePlateReservations/availabilityChecks',
            $request->toArray(),
        );

        $response = $this->httpClient->sendRequest($httpRequest);
        $data = $this->responseMapper->mapResponse($response);

        return AvailabilityCheckResponse::fromArray($data);
    }
}

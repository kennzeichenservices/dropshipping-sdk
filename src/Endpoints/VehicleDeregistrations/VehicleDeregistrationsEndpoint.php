<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\VehicleDeregistrations;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Requests\VehicleDeregistrationRequest;
use Dropshipping\DTO\Responses\VehicleDeregistrationResponse;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;

/**
 * API endpoint for vehicle deregistration operations.
 *
 * Provides the method to submit a vehicle deregistration request
 * through the dropshipping API.
 */
final class VehicleDeregistrationsEndpoint
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
     * Submit a vehicle deregistration request.
     *
     * @param VehicleDeregistrationRequest $request The deregistration request data.
     *
     * @return VehicleDeregistrationResponse The deregistration result containing the order ID.
     */
    public function createDeregistration(
        VehicleDeregistrationRequest $request,
    ): VehicleDeregistrationResponse {
        $httpRequest = $this->requestFactory->createJsonRequest(
            'POST',
            $this->baseUrl . '/vehicleDeregistrations/deregistrations',
            $request->toArray(),
        );

        $response = $this->httpClient->sendRequest($httpRequest);
        $data = $this->responseMapper->mapResponse($response);

        return VehicleDeregistrationResponse::fromArray($data);
    }
}

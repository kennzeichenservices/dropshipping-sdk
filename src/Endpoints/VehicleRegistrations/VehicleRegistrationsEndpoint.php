<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\VehicleRegistrations;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Requests\VehicleRegistrationRequest;
use Dropshipping\DTO\Responses\VehicleRegistrationResponse;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;

/**
 * API endpoint for vehicle registration operations.
 *
 * Provides the method to submit a vehicle registration request
 * through the dropshipping API.
 *
 * @experimental Vehicle registration is a beta feature of the dropshipping API
 *               (2.3.2) and may change without a major version bump.
 */
final class VehicleRegistrationsEndpoint
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
     * Submit a vehicle registration request.
     *
     * The returned {@see VehicleRegistrationResponse::$customerInputFormUrl} must be
     * handed to the customer — the registration is only processed once the customer
     * has completed that form. Results are delivered via webhook events.
     *
     * @param VehicleRegistrationRequest $request The registration request data.
     *
     * @return VehicleRegistrationResponse The registration result containing the order ID,
     *                                     identity verification vendor ID and customer input form URL.
     */
    public function createRegistration(
        VehicleRegistrationRequest $request,
    ): VehicleRegistrationResponse {
        $httpRequest = $this->requestFactory->createJsonRequest(
            'POST',
            $this->baseUrl . '/vehicleRegistrations/registrations',
            $request->toArray(),
        );

        $response = $this->httpClient->sendRequest($httpRequest);
        $data = $this->responseMapper->mapResponse($response);

        return VehicleRegistrationResponse::fromArray($data);
    }
}

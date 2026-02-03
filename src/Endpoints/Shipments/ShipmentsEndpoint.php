<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\Shipments;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Requests\LicensePlateReservationRequest;
use Dropshipping\DTO\Responses\LicensePlateReservationResponse;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;

/**
 * API endpoint for shipment-related operations.
 */
final class ShipmentsEndpoint
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
     * Submit a license plate reservation request to the registration office.
     *
     * @param LicensePlateReservationRequest $request The reservation request containing the license plate details
     *
     * @return LicensePlateReservationResponse The reservation result
     */
    public function createLicensePlateReservation(
        LicensePlateReservationRequest $request,
    ): LicensePlateReservationResponse {
        $httpRequest = $this->requestFactory->createJsonRequest(
            'POST',
            $this->baseUrl . '/licensePlateReservations/reservations',
            $request->toArray(),
        );

        $response = $this->httpClient->sendRequest($httpRequest);
        $data = $this->responseMapper->mapResponse($response);

        return LicensePlateReservationResponse::fromArray($data);
    }
}

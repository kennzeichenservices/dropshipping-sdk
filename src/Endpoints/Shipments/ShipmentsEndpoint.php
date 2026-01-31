<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\Shipments;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Requests\LicensePlateReservationRequest;
use Dropshipping\DTO\Responses\LicensePlateReservationResponse;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;

final class ShipmentsEndpoint
{
    public function __construct(
        private readonly Psr18HttpClient $httpClient,
        private readonly RequestFactory $requestFactory,
        private readonly ResponseMapper $responseMapper,
        private readonly string $baseUrl,
    ) {
    }

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

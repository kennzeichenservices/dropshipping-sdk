<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\Products;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Requests\AvailabilityCheckRequest;
use Dropshipping\DTO\Responses\AvailabilityCheckResponse;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;

final class ProductsEndpoint
{
    public function __construct(
        private readonly Psr18HttpClient $httpClient,
        private readonly RequestFactory $requestFactory,
        private readonly ResponseMapper $responseMapper,
        private readonly string $baseUrl,
    ) {
    }

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

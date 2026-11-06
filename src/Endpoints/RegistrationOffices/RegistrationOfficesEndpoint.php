<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\RegistrationOffices;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Responses\RegistrationOfficesResponse;
use Dropshipping\Exceptions\ApiException;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;

/**
 * API endpoint for registration office (Zulassungsstelle) lookups.
 *
 * Requires API 2.6.0, which publishes the listing.
 */
final class RegistrationOfficesEndpoint
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
     * Get every registration office, with the reservation fee each one charges.
     *
     * The same content as the registration office CSV export of the dropshipping
     * self service. The listing is not paginated and changes rarely, so it is
     * worth caching on the caller's side rather than fetching it per order.
     *
     * @return RegistrationOfficesResponse The offices.
     *
     * @throws ApiException When the API responds with an unexpected status code, which
     *                      includes 403 on an API version below 2.6.0.
     */
    public function getAll(): RegistrationOfficesResponse
    {
        $httpRequest = $this->requestFactory->createJsonRequest(
            'GET',
            $this->baseUrl . '/registrationOffices',
            [],
        );

        $response = $this->httpClient->sendRequest($httpRequest);
        $data = $this->responseMapper->mapResponse($response);

        return RegistrationOfficesResponse::fromArray($data);
    }
}

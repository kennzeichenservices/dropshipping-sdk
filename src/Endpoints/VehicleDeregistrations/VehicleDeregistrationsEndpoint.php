<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\VehicleDeregistrations;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Requests\VehicleDeregistrationRequest;
use Dropshipping\DTO\Responses\VehicleDeregistrationResponse;
use Dropshipping\Exceptions\ApiException;
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

    /**
     * Download the binary content of a vehicle deregistration file.
     *
     * The {@see $fileAccessKey} is obtained from a {@see \Dropshipping\DTO\Webhooks\VehicleDeregistrationXkfzEventFile}
     * attached to a VEHICLE_DEREGISTRATION_XKFZ_EVENT webhook event.
     *
     * @param string $fileAccessKey The file access key from the webhook event.
     *
     * @return string Raw binary file content.
     *
     * @throws ApiException When the API responds with an unexpected status code.
     */
    public function downloadFileContent(string $fileAccessKey): string
    {
        $httpRequest = $this->requestFactory->createBinaryGetRequest(
            $this->baseUrl . '/vehicleDeregistrations/files/content/' . rawurlencode($fileAccessKey),
        );

        $response = $this->httpClient->sendRequest($httpRequest);
        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            $body = (string) $response->getBody();
            $traceId = $response->getHeaderLine('X-Trace-Id') ?: null;
            $errorMessage = 'API request failed';

            if ($body !== '') {
                $data = json_decode($body, true);
                $errorMessage = $data['error'] ?? $errorMessage;
            }

            throw new ApiException($errorMessage, $statusCode, $traceId);
        }

        return (string) $response->getBody();
    }
}

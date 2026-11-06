<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\VehicleRegistrations;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\DTO\Requests\VehicleRegistrationRequest;
use Dropshipping\DTO\Responses\VehicleRegistrationResponse;
use Dropshipping\Enums\IdentityVerificationDocumentType;
use Dropshipping\Exceptions\ApiException;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;
use Dropshipping\Support\Validator;

/**
 * API endpoint for vehicle registration operations.
 *
 * Provides the methods to submit a vehicle registration request and to download
 * the files the registration produces.
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
     * The response carries only the created order ID. The customer still has to
     * identify themselves and sign the documents — the URLs for both steps are
     * delivered as VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_INITIALIZED and
     * VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_INITIALIZED webhook events, as is
     * the registration result itself.
     *
     * @param VehicleRegistrationRequest $request The registration request data.
     *
     * @return VehicleRegistrationResponse The registration result containing the order ID.
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

    /**
     * Download the binary content of a vehicle registration file.
     *
     * The {@see $fileAccessKey} is obtained from a {@see \Dropshipping\DTO\Webhooks\VehicleRegistrationXkfzEventFile}
     * attached to a VEHICLE_REGISTRATION_XKFZ_EVENT webhook event.
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
            $this->baseUrl . '/vehicleRegistrations/files/content/' . rawurlencode($fileAccessKey),
        );

        $response = $this->httpClient->sendRequest($httpRequest);

        return $this->responseMapper->readBody($response);
    }

    /**
     * Download the binary content of a signed vehicle registration application file.
     *
     * The {@see $fileAccessKey} is obtained from a {@see \Dropshipping\DTO\Webhooks\VehicleRegistrationApplicationFile}
     * attached to a VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_SUCCEEDED webhook event.
     * Keys from a VEHICLE_REGISTRATION_XKFZ_EVENT belong to {@see downloadFileContent()}
     * instead — the two operations do not serve each other's keys.
     *
     * @param string $fileAccessKey The file access key from the webhook event.
     *
     * @return string Raw binary file content.
     *
     * @throws ApiException When the API responds with an unexpected status code.
     */
    public function downloadApplicationFileContent(string $fileAccessKey): string
    {
        $httpRequest = $this->requestFactory->createBinaryGetRequest(
            $this->baseUrl . '/vehicleRegistrations/applicationFiles/content/' . rawurlencode($fileAccessKey),
        );

        $response = $this->httpClient->sendRequest($httpRequest);

        return $this->responseMapper->readBody($response);
    }

    /**
     * Change the identity verification document type of an existing registration order.
     *
     * Requires API 2.6.0. The customer picks the document they identify themselves
     * with when the order is created; this corrects that choice afterwards, for
     * instance when they turn out not to hold a German ID card.
     *
     * The API answers 202 and does the work asynchronously: depending on the
     * change, a fresh verification link arrives as a
     * VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_INITIALIZED webhook event. A
     * returning 202 therefore means the change was accepted, not that a new link
     * exists yet.
     *
     * @param int                             $orderId      ID of the registration order to change.
     * @param IdentityVerificationDocumentType $documentType The document type to switch to.
     *
     * @throws \Dropshipping\Exceptions\DropshippingException When the order ID is not positive.
     * @throws ApiException When the API rejects the change: 404 for an unknown order,
     *                      409 once document signing has started or when the order is not
     *                      eligible for a change, 403 on an API version below 2.6.0.
     */
    public function updateIdentityVerificationDocumentType(
        int $orderId,
        IdentityVerificationDocumentType $documentType,
    ): void {
        // A non-positive ID is never a real order. Caught here so a mix-up does not
        // travel into the URL path and hit an unrelated route.
        Validator::requireIntRange($orderId, 'orderId', 1, PHP_INT_MAX);

        $httpRequest = $this->requestFactory->createJsonRequest(
            'PUT',
            sprintf('%s/vehicleRegistrations/%d/identityVerifications', $this->baseUrl, $orderId),
            ['identityVerificationDocumentType' => $documentType->value],
        );

        $response = $this->httpClient->sendRequest($httpRequest);
        $this->responseMapper->mapResponse($response, [202]);
    }
}

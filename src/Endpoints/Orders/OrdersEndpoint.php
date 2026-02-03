<?php

declare(strict_types=1);

namespace Dropshipping\Endpoints\Orders;

use Dropshipping\Client\Psr18HttpClient;
use Dropshipping\Contracts\SerializerInterface;
use Dropshipping\DTO\Requests\EmissionStickerOrderRequest;
use Dropshipping\DTO\Requests\OrderCreationRequest;
use Dropshipping\DTO\Requests\ReshippedOrderRequest;
use Dropshipping\DTO\Responses\EmissionStickerOrderResponse;
use Dropshipping\DTO\Responses\OrderCreationResponse;
use Dropshipping\Http\RequestFactory;
use Dropshipping\Http\ResponseMapper;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * API endpoint for order-related operations.
 */
final class OrdersEndpoint
{
    /**
     * @param Psr18HttpClient        $httpClient     HTTP client for sending requests
     * @param RequestFactory         $requestFactory  Factory for creating HTTP request objects
     * @param ResponseMapper         $responseMapper  Mapper for processing HTTP responses
     * @param SerializerInterface    $serializer      Serializer for encoding request payloads
     * @param string                 $baseUrl         Base URL of the dropshipping API
     * @param StreamFactoryInterface $streamFactory   Factory for creating PSR-7 stream instances
     */
    public function __construct(
        private readonly Psr18HttpClient $httpClient,
        private readonly RequestFactory $requestFactory,
        private readonly ResponseMapper $responseMapper,
        private readonly SerializerInterface $serializer,
        private readonly string $baseUrl,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * Create a new dropshipping order.
     *
     * @param OrderCreationRequest $request The order creation request payload
     *
     * @return OrderCreationResponse The created order response
     */
    public function create(OrderCreationRequest $request): OrderCreationResponse
    {
        $httpRequest = $this->requestFactory->createJsonRequest(
            'POST',
            $this->baseUrl . '/orders',
            $request->toArray(),
        );

        $response = $this->httpClient->sendRequest($httpRequest);
        $data = $this->responseMapper->mapResponse($response, [201]);

        return OrderCreationResponse::fromArray($data);
    }

    /**
     * Create an emission sticker order with file uploads via multipart request.
     *
     * @param EmissionStickerOrderRequest $request The emission sticker order request including file paths
     *
     * @return EmissionStickerOrderResponse The created emission sticker order response
     */
    public function createEmissionStickerOrder(EmissionStickerOrderRequest $request): EmissionStickerOrderResponse
    {
        $boundary = bin2hex(random_bytes(16));
        $orderJson = $this->serializer->encode($request->toOrderArray());

        $httpRequest = $this->requestFactory->createMultipartRequest(
            'POST',
            $this->baseUrl . '/orders/emissionStickerOrders',
            $orderJson,
            $boundary,
        );

        $body = $this->requestFactory->buildMultipartBody($orderJson, $request->filePaths, $boundary);
        $stream = $this->streamFactory->createStream($body);
        $httpRequest = $httpRequest->withBody($stream);

        $response = $this->httpClient->sendRequest($httpRequest);
        $data = $this->responseMapper->mapResponse($response, [201]);

        return EmissionStickerOrderResponse::fromArray($data);
    }

    /**
     * Create a reshipped order for a previously returned delivery.
     *
     * @param ReshippedOrderRequest $request The reshipped order request payload
     *
     * @return OrderCreationResponse The created order response
     */
    public function createReshippedOrder(ReshippedOrderRequest $request): OrderCreationResponse
    {
        $httpRequest = $this->requestFactory->createJsonRequest(
            'POST',
            $this->baseUrl . '/orders/reshippedOrders',
            $request->toArray(),
        );

        $response = $this->httpClient->sendRequest($httpRequest);
        $data = $this->responseMapper->mapResponse($response, [201]);

        return OrderCreationResponse::fromArray($data);
    }
}

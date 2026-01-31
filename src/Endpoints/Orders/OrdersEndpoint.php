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

final class OrdersEndpoint
{
    public function __construct(
        private readonly Psr18HttpClient $httpClient,
        private readonly RequestFactory $requestFactory,
        private readonly ResponseMapper $responseMapper,
        private readonly SerializerInterface $serializer,
        private readonly string $baseUrl,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

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

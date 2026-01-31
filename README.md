# Dropshipping SDK

A PHP SDK for the Kennzeichen Services Dropshipping API. It provides typed request/response objects, webhook processing with middleware pipeline support, and async queue integration.

## Features

- Typed endpoints for orders, shipments, products, and webhooks
- Immutable DTOs for all requests and responses
- Webhook processing with configurable middleware pipeline (signature validation, payload validation, deserialization)
- Async webhook processing via queue abstraction
- HMAC-SHA256 webhook signature verification
- Multipart file upload support for emission sticker orders
- PSR-18 HTTP client / PSR-17 HTTP factory compatible (bring your own HTTP client)

## Requirements

- PHP 8.2 or higher
- A PSR-18 HTTP client implementation (e.g. `guzzlehttp/guzzle`, `symfony/http-client`)
- A PSR-17 HTTP factory implementation (e.g. `guzzlehttp/psr7`, `nyholm/psr7`)

## Installation

```bash
composer require kennzeichenservices/dropshipping-sdk
```

## Configuration

```php
use Dropshipping\Configuration\DropshippingConfig;

$config = new DropshippingConfig(
    host: 'api.example.com',
    dropshippingClientId: 123,
    username: 'your-username',
    password: 'your-password',
    webhookSignatureSecret: 'your-webhook-secret', // optional
);
```

## Usage

### Creating the Client

The client requires a PSR-18 HTTP client and PSR-17 request/stream factories. Example using Guzzle:

```php
use Dropshipping\Client\ApiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

$httpClient = new Client();
$factory = new HttpFactory();

$client = new ApiClient(
    config: $config,
    httpClient: $httpClient,
    psrRequestFactory: $factory,
    streamFactory: $factory,
);
```

The client exposes four endpoint groups as public readonly properties:

- `$client->orders` -- Order operations
- `$client->shipments` -- Shipment operations (license plate reservations)
- `$client->products` -- Product operations (availability checks)
- `$client->webhooks` -- Webhook operations

### Creating an Order

```php
use Dropshipping\DTO\Address;
use Dropshipping\DTO\OrderItem;
use Dropshipping\DTO\LicensePlateItemCustomization;
use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\Requests\OrderCreationRequest;
use Dropshipping\Enums\Gender;

$address = new Address(
    firstName: 'Max',
    lastName: 'Mustermann',
    gender: Gender::Male,
    streetName: 'Musterstraße',
    houseNumber: '1',
    zipCode: '12345',
    cityName: 'Berlin',
    countryCode: 'DE',
);

$item = new OrderItem(
    productVariantId: 42,
    name: 'Zulassung',
    sku: 'ZL-001',
    quantity: 1,
    customization: new LicensePlateItemCustomization(
        licensePlateNumberComponents: new EuroLicensePlateNumberComponents(
            city: 'B',
            middle: 'AB',
            end: '1234',
        ),
    ),
);

$request = new OrderCreationRequest(
    externalId: 'order-001',
    email: 'max@example.com',
    deliveryAddress: $address,
    invoiceAddress: $address,
    items: [$item],
);

$response = $client->orders->create($request);

echo $response->id; // Order ID
```

### Creating an Emission Sticker Order

```php
use Dropshipping\DTO\Requests\EmissionStickerOrderRequest;

$request = new EmissionStickerOrderRequest(
    externalId: 'sticker-001',
    email: 'max@example.com',
    deliveryAddress: $address,
    invoiceAddress: $address,
    licensePlateNumberComponents: new EuroLicensePlateNumberComponents(
        city: 'B',
        middle: 'AB',
        end: '1234',
    ),
    electric: false,
    emissionKeyNumber: '0005',
    filePaths: ['/path/to/fahrzeugschein.pdf'],
);

$response = $client->orders->createEmissionStickerOrder($request);
```

### Creating a Reshipped Order

```php
use Dropshipping\DTO\Requests\ReshippedOrderRequest;

$request = new ReshippedOrderRequest(
    externalId: 'reship-001',
    returnedDeliveryId: 456,
    deliveryAddress: $address,
    invoiceAddress: $address,
);

$response = $client->orders->createReshippedOrder($request);
```

### Checking License Plate Availability

```php
use Dropshipping\DTO\Requests\AvailabilityCheckRequest;
use Dropshipping\Enums\LicensePlateType;
use Dropshipping\Enums\VehicleType;

$request = new AvailabilityCheckRequest(
    registrationOfficeServiceId: 1,
    city: 'B',
    middle: 'AB',
    end: '1234',
    licensePlateType: LicensePlateType::Regular,
    vehicleType: VehicleType::Car,
);

$response = $client->products->checkLicensePlateAvailability($request);

foreach ($response->availableLicensePlateNumbers as $plate) {
    echo "{$plate->city} {$plate->middle} {$plate->end}\n";
}
```

### Reserving a License Plate

```php
use Dropshipping\DTO\Requests\LicensePlateReservationRequest;
use Dropshipping\DTO\Requests\LicensePlateReservationCustomization;
use Dropshipping\DTO\Requests\LicensePlateReservationVehicleHolder;

$request = new LicensePlateReservationRequest(
    email: 'max@example.com',
    customization: new LicensePlateReservationCustomization(
        registrationOfficeServiceId: 1,
        licensePlateType: LicensePlateType::Regular,
        vehicleType: VehicleType::Car,
        licensePlateNumberComponents: new EuroLicensePlateNumberComponents(
            city: 'B',
            middle: 'AB',
            end: '1234',
        ),
    ),
    vehicleHolder: new LicensePlateReservationVehicleHolder(
        address: $address,
    ),
);

$response = $client->shipments->createLicensePlateReservation($request);
```

### Handling Webhooks

Set up a webhook receiver with the built-in middleware pipeline:

```php
use Dropshipping\Contracts\WebhookHandlerInterface;
use Dropshipping\DTO\Webhooks\WebhookEventInterface;
use Dropshipping\DTO\Webhooks\DeliveryShipmentEvent;
use Dropshipping\Security\WebhookSignatureVerifier;
use Dropshipping\Serialization\ArrayMapper;
use Dropshipping\Webhook\WebhookDispatcher;
use Dropshipping\Webhook\WebhookMessage;
use Dropshipping\Webhook\WebhookPipeline;
use Dropshipping\Webhook\Middleware\SignatureValidationMiddleware;
use Dropshipping\Webhook\Middleware\PayloadValidationMiddleware;
use Dropshipping\Webhook\Middleware\DeserializationMiddleware;
use Dropshipping\DTO\Webhooks\WebhookEventFactory;
use Dropshipping\Enums\WebhookEventType;

// Create middleware pipeline
$serializer = new ArrayMapper();
$verifier = new WebhookSignatureVerifier($config->getWebhookSignatureSecret());

$pipeline = (new WebhookPipeline())
    ->pipe(new SignatureValidationMiddleware($verifier))
    ->pipe(new PayloadValidationMiddleware())
    ->pipe(new DeserializationMiddleware($serializer, new WebhookEventFactory()));

// Implement a handler
class ShipmentHandler implements WebhookHandlerInterface
{
    public function supports(WebhookEventInterface $event): bool
    {
        return $event->getEventType() === WebhookEventType::DeliveryShipment;
    }

    public function handle(WebhookEventInterface $event): void
    {
        /** @var DeliveryShipmentEvent $event */
        echo "Order {$event->order->id} shipped, tracking: {$event->delivery->trackingCode}\n";
    }
}

// Wire up the dispatcher
$dispatcher = new WebhookDispatcher($pipeline, []);
$dispatcher->registerHandler(new ShipmentHandler());

// Receive a webhook (e.g. in a controller)
$message = new WebhookMessage(
    payload: $requestBody,
    signature: $request->getHeaderLine('X-Signature'),
    webhookId: (int) $request->getHeaderLine('X-Webhook-Id'),
    webhookVersion: $request->getHeaderLine('X-Webhook-Version'),
);

$dispatcher->dispatch($message);
```

### Async Webhook Processing

For high-throughput scenarios, queue webhooks for background processing:

```php
use Dropshipping\Async\QueueWebhookDispatcher;
use Dropshipping\Async\WebhookWorker;
use Dropshipping\Contracts\WebhookQueueInterface;

// Implement WebhookQueueInterface with your queue backend (Redis, RabbitMQ, database, etc.)
$queue = new YourQueueImplementation();

// In your HTTP controller: enqueue instead of processing inline
$queueDispatcher = new QueueWebhookDispatcher($queue);
$queueDispatcher->dispatch($message);

// In a background worker process
$worker = new WebhookWorker($queue, $dispatcher);
$processed = $worker->run(maxMessages: 100);
```

## Architecture Overview

```
src/
├── Async/              Queue-based webhook processing
├── Client/             API client and HTTP authentication
├── Configuration/      SDK configuration
├── Contracts/          Interfaces for serialization, webhooks, and queues
├── DTO/
│   ├── Requests/       Request objects with toArray() serialization
│   ├── Responses/      Response objects with fromArray() factories
│   └── Webhooks/       Webhook event types and factories
├── Endpoints/          API endpoint classes (Orders, Shipments, Products, Webhooks)
├── Enums/              Backed string enums for type safety
├── Exceptions/         Exception hierarchy
├── Http/               PSR-7 request building and response mapping
├── Security/           HMAC-SHA256 signature verification
├── Serialization/      JSON encode/decode
├── Support/            Input validation utilities
└── Webhook/            Middleware pipeline and dispatcher
```

The SDK follows these patterns:

- **Immutable DTOs** -- All request and response objects are `final readonly` classes.
- **Static factories** -- Response DTOs provide `fromArray()` constructors; request DTOs provide `toArray()` for serialization.
- **Middleware pipeline** -- Webhook processing uses composable middleware (signature validation, payload validation, deserialization).
- **PSR compliance** -- No HTTP client is bundled. The SDK depends on PSR-18 (HTTP Client), PSR-17 (HTTP Factories), and PSR-7 (HTTP Messages).

## Key Components

### Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `$client->orders->create()` | POST /orders | Create a standard order |
| `$client->orders->createEmissionStickerOrder()` | POST /orders/emissionStickerOrders | Create emission sticker order (multipart) |
| `$client->orders->createReshippedOrder()` | POST /orders/reshippedOrders | Create reshipped order |
| `$client->shipments->createLicensePlateReservation()` | POST /licensePlateReservations/reservations | Reserve a license plate |
| `$client->products->checkLicensePlateAvailability()` | POST /licensePlateReservations/availabilityChecks | Check license plate availability |

### Webhook Event Types

| Event | Class | Description |
|-------|-------|-------------|
| `PING` | `PingEvent` | Connection test |
| `DELIVERY_SHIPMENT` | `DeliveryShipmentEvent` | Delivery shipped with tracking code |
| `DELIVERY_RETURN` | `DeliveryReturnEvent` | Delivery returned with reason and reshipping offer |
| `DELIVERY_CANCELLATION` | `DeliveryCancellationEvent` | Delivery cancelled |
| `LICENSE_PLATE_RESERVATION_APPROVAL` | `LicensePlateReservationApprovalEvent` | Reservation approved with PIN and price |
| `LICENSE_PLATE_RESERVATION_REJECTION` | `LicensePlateReservationRejectionEvent` | Reservation rejected with alternatives |
| `LICENSE_PLATE_RESERVATION_TIMEOUT` | `LicensePlateReservationTimeoutEvent` | Reservation timed out |

### Enums

| Enum | Values |
|------|--------|
| `Gender` | `FEMALE`, `MALE`, `UNSPECIFIED` |
| `VehicleType` | `CAR`, `MOTORCYCLE` |
| `LicensePlateType` | `REGULAR`, `REGULAR_SEASON`, `ELECTRIC`, `ELECTRIC_SEASON`, `HISTORICAL`, `HISTORICAL_SEASON` |
| `LicensePlateUsageType` | `EURO`, `PARKING` |
| `ProductType` | `LICENSE_PLATE`, `OTHER` |

## Extensibility

- **Custom HTTP client** -- Pass any PSR-18 compliant HTTP client to `ApiClient`.
- **Custom serializer** -- Implement `SerializerInterface` and pass it to `ApiClient` to replace the default `ArrayMapper`.
- **Custom webhook handlers** -- Implement `WebhookHandlerInterface` and register with `WebhookDispatcher`.
- **Custom queue backend** -- Implement `WebhookQueueInterface` for async webhook processing with any queue system.
- **Custom middleware** -- Implement `WebhookMiddlewareInterface` to add processing steps to the webhook pipeline.

## Security Considerations

- API authentication uses HTTP Basic Auth. Credentials are added to every request by `ApiKeyAuthenticator`.
- Webhook payloads are verified using HMAC-SHA256 signatures via the `X-Signature` header. The `SignatureValidationMiddleware` rejects requests with invalid signatures.
- Store API credentials and webhook secrets outside of version control.

## Error Handling

All exceptions extend `DropshippingException`:

| Exception | When |
|-----------|------|
| `ApiException` | Non-expected HTTP status code from the API. Provides `getStatusCode()` and `getTraceId()` for debugging. |
| `HttpClientException` | PSR-18 client-level transport failure. Wraps the original `ClientExceptionInterface`. |
| `WebhookException` | Webhook signature verification or payload validation failure. |

## License

Proprietary

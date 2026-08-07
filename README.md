# Dropshipping SDK

A PHP SDK for the Kennzeichen Services Dropshipping API. It provides typed request/response objects, webhook processing with middleware pipeline support, and async queue integration.

## Features

- Typed endpoints for orders, shipments, products, webhooks, GKS configurations, vehicle deregistrations, and vehicle registrations (beta)
- Immutable DTOs for all requests and responses
- Webhook processing with configurable middleware pipeline (signature validation, payload validation, deserialization)
- Async webhook processing via queue abstraction
- HMAC-SHA256 webhook signature verification
- Multipart file upload support for emission sticker orders
- PSR-18 HTTP client / PSR-17 HTTP factory compatible (bring your own HTTP client)
- Built-in request/response debug logging via `KS_DROPSHIPPING_DEBUG` constant

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

### Using DS

Instead of importing individual DTO classes, use `Dropshipping\DS` as a single entry point for all request objects:

```php
use Dropshipping\DS;
use Dropshipping\Enums\Gender;

$address  = DS::address(firstName: 'Max', ..., gender: Gender::Male);
$response = $client->orders->create(
    DS::order(
        externalId: 'order-001',
        email: 'max@example.com',
        deliveryAddress: $address,
        invoiceAddress: $address,
        items: [DS::orderItem(42, 'Zulassung', 'ZL-001', 1, DS::plate('B', 'AB', '1234'))],
    )
);
```

The only imports you need are `Dropshipping\DS` and the enums you use (e.g. `Gender`, `LicensePlateType`). See the [`examples/`](examples/) directory for complete runnable scripts.

The client exposes seven endpoint groups as public readonly properties:

- `$client->orders` -- Order operations
- `$client->shipments` -- Shipment operations (license plate reservations)
- `$client->products` -- Product operations (availability checks)
- `$client->webhooks` -- Webhook operations
- `$client->gksConfigurations` -- GKS configuration management (KBA interface)
- `$client->vehicleDeregistrations` -- Vehicle deregistration operations
- `$client->vehicleRegistrations` -- Vehicle registration operations (**beta**)

> **Beta notice** -- vehicle registration is modelled against dropshipping API 2.4.0 and is still
> subject to change. Everything under `$client->vehicleRegistrations` and every class marked
> `@experimental` may change in a minor release without being treated as a breaking change.
>
> **2.4.0 is not the SDK default.** The API version is part of the request URL, and an API version
> your client is not entitled to answers `403 Forbidden` on *every* endpoint -- not just the new
> ones. `api-version` in `composer.json` therefore always names a version that is live for all
> clients, so an SDK update never moves you onto a version you cannot use. Opt into the beta per
> integration, once your client is enabled for it:
>
> ```bash
> DROPSHIPPING_API_VERSION=2.4.0
> ```
>
> Or pass it directly, which takes precedence over the environment:
>
> ```php
> $config = new DropshippingConfig(
>     host: 'api.example.com',
>     dropshippingClientId: 12345,
>     username: '...',
>     password: '...',
>     apiVersion: '2.4.0',
> );
> ```
>
> Without this, calls to `$client->vehicleRegistrations` return a 404 -- while every other endpoint
> keeps working.

## Examples

Ready-to-run PHP scripts are available in the [`examples/`](examples/) directory. Each example has an accompanying Markdown file that explains what it does.

| Example | Description |
|---------|-------------|
| [create-order.php](examples/create-order.php) · [docs](examples/create-order.md) | Create a standard order with a license plate item |
| [create-emission-sticker-order.php](examples/create-emission-sticker-order.php) · [docs](examples/create-emission-sticker-order.md) | Create an emission sticker order with file upload |
| [create-reshipped-order.php](examples/create-reshipped-order.php) · [docs](examples/create-reshipped-order.md) | Create a reshipped order for a returned delivery |
| [check-license-plate-availability.php](examples/check-license-plate-availability.php) · [docs](examples/check-license-plate-availability.md) | Check available license plate numbers at a registration office |
| [reserve-license-plate.php](examples/reserve-license-plate.php) · [docs](examples/reserve-license-plate.md) | Reserve a license plate |
| [gks-configurations.php](examples/gks-configurations.php) · [docs](examples/gks-configurations.md) | Create, update, list, and get GKS configurations |
| [vehicle-deregistration.php](examples/vehicle-deregistration.php) · [docs](examples/vehicle-deregistration.md) | Submit a vehicle deregistration and handle the XKFZ webhook with file download |
| [vehicle-registration.php](examples/vehicle-registration.php) · [docs](examples/vehicle-registration.md) | Submit a vehicle registration (**beta**) |
| [webhooks.php](examples/webhooks.php) · [docs](examples/webhooks.md) | Process incoming webhooks with the middleware pipeline |
| [async-webhooks.php](examples/async-webhooks.php) · [docs](examples/async-webhooks.md) | Enqueue and process webhooks asynchronously via a queue |

### Creating an Order

```php
use Dropshipping\DS;
use Dropshipping\Enums\Gender;

$address = DS::address(
    firstName: 'Max',
    lastName: 'Mustermann',
    gender: Gender::Male,
    streetName: 'Musterstraße',
    houseNumber: '1',
    zipCode: '12345',
    cityName: 'Berlin',
    countryCode: 'DE',
);

$response = $client->orders->create(
    DS::order(
        externalId: 'order-001',
        email: 'max@example.com',
        deliveryAddress: $address,
        invoiceAddress: $address,
        items: [DS::orderItem(42, 'Zulassung', 'ZL-001', 1, DS::plate('B', 'AB', '1234'))],
    )
);

echo $response->id; // Order ID
```

### Creating an Emission Sticker Order

```php
use Dropshipping\DS;

$response = $client->orders->createEmissionStickerOrder(
    DS::emissionStickerOrder(
        externalId: 'sticker-001',
        email: 'max@example.com',
        deliveryAddress: $address,
        invoiceAddress: $address,
        plate: DS::plate('B', 'AB', '1234'),
        electric: false,
        emissionKeyNumber: '0005',
        filePaths: ['/path/to/fahrzeugschein.pdf'],
    )
);
```

### Creating a Reshipped Order

```php
use Dropshipping\DS;

$response = $client->orders->createReshippedOrder(
    DS::reshippedOrder(
        externalId: 'reship-001',
        returnedDeliveryId: 456,
        deliveryAddress: $address,
        invoiceAddress: $address,
    )
);
```

### Checking License Plate Availability

```php
use Dropshipping\DS;
use Dropshipping\Enums\{LicensePlateType, VehicleType};

$response = $client->products->checkLicensePlateAvailability(
    DS::availabilityCheck(
        registrationOfficeServiceId: 1,
        city: 'B',
        middle: 'AB',
        end: '1234',
        licensePlateType: LicensePlateType::Regular,
        vehicleType: VehicleType::Car,
    )
);

foreach ($response->availableLicensePlateNumbers as $plate) {
    echo "{$plate->city} {$plate->middle} {$plate->end}\n";
}
```

### Reserving a License Plate

```php
use Dropshipping\DS;
use Dropshipping\Enums\{LicensePlateType, VehicleType};

$response = $client->shipments->createLicensePlateReservation(
    DS::licensePlateReservation(
        email: 'max@example.com',
        customization: DS::reservationCustomization(
            registrationOfficeServiceId: 1,
            licensePlateType: LicensePlateType::Regular,
            vehicleType: VehicleType::Car,
            plate: DS::plate('B', 'AB', '1234'),
        ),
        vehicleHolder: DS::reservationVehicleHolder(address: $address),
    )
);
```

### Managing GKS Configurations

```php
use Dropshipping\DS;

$request = DS::gksConfiguration(
    name: 'My KBA Config',
    kopaKey: 'kopa-key-value',
    username: 'kba-username',
    password: 'kba-password',
    publicKeyCertificate: file_get_contents('/path/to/cert.pem'),
    privateKey: file_get_contents('/path/to/private.key'),
    company: DS::gksCompany(
        name: 'Musterfirma GmbH',
        streetName: 'Musterstraße',
        houseNumber: '1',
        zipCode: '12345',
        cityName: 'Berlin',
        countryCode: 'DE',
    ),
);

// Create
$cfg = $client->gksConfigurations->create($request);
echo $cfg->id;   // UUID of the new configuration

// Update
$client->gksConfigurations->update($cfg->id, $request);

// List all
foreach ($client->gksConfigurations->getOverviews()->overviewGksConfigurations as $overview) {
    echo "{$overview->id}: {$overview->name}\n";
}

// Get single
$single = $client->gksConfigurations->getOverview($cfg->id);
```

### Submitting a Vehicle Deregistration

```php
use Dropshipping\DS;
use Dropshipping\Enums\{VehicleDeregistrationLicensePlateType, VehicleDeregistrationVehicleType};

$response = $client->vehicleDeregistrations->createDeregistration(
    DS::vehicleDeregistration(
        email: 'max@example.com',
        customization: DS::deregistrationCustomization(
            vehicleType: VehicleDeregistrationVehicleType::Car,
            plate: DS::plate('B', 'AB', '1234'),
            licensePlateReservationIncluded: false,
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleRegistrationCertificateSecurityCode: 'ABC123',
            rearLicensePlateSecurityCode: 'XY9876',
        ),
        vehicleHolderAddress: $address,
        externalOrderId: 'deregistration-001',  // optional
        gksConfigurationId: 'your-gks-uuid',    // optional
        contractPartnerKopaKey: 'K123X',        // optional
    )
);

echo $response->orderId; // Created order ID
```

### Downloading a Deregistration File

Files attached to a `VEHICLE_DEREGISTRATION_XKFZ_EVENT` webhook can be downloaded using the `fileAccessKey` from the event:

```php
use Dropshipping\Client\ApiClient;
use Dropshipping\Contracts\WebhookHandlerInterface;
use Dropshipping\DTO\Webhooks\{VehicleDeregistrationXkfzEvent, WebhookEventInterface};
use Dropshipping\Enums\WebhookEventType;

class DeregistrationXkfzHandler implements WebhookHandlerInterface
{
    public function __construct(private readonly ApiClient $client) {}

    public function supports(WebhookEventInterface $event): bool
    {
        return $event->getEventType() === WebhookEventType::VehicleDeregistrationXkfzEvent;
    }

    public function handle(WebhookEventInterface $event): void
    {
        /** @var VehicleDeregistrationXkfzEvent $event */
        echo "Order {$event->order->id} status: {$event->status->value} ({$event->derivedStatus})\n";

        foreach ($event->messages ?? [] as $message) {
            echo "[{$message->type}] {$message->text}\n";
        }

        foreach ($event->files ?? [] as $file) {
            $content = $this->client->vehicleDeregistrations->downloadFileContent($file->fileAccessKey);
            file_put_contents("{$file->purposeType->value}.pdf", $content);
        }
    }
}
```

### Submitting a Vehicle Registration (Beta)

> **Beta** -- modelled against dropshipping API 2.4.0. Request and response shapes may still change.
> Requires `apiVersion: '2.4.0'` on your `DropshippingConfig` (or `DROPSHIPPING_API_VERSION=2.4.0`)
> and a client that is enabled for that version -- see the beta notice above.

```php
use Dropshipping\DS;
use Dropshipping\Enums\{
    VehicleRegistrationLicensePlateType,
    VehicleRegistrationServiceTypeCode,
    VehicleRegistrationVehicleType,
};

$response = $client->vehicleRegistrations->createRegistration(
    DS::vehicleRegistration(
        email: 'max@example.com',
        customization: DS::registrationCustomization(
            licensePlateNumberAssignmentStrategy: DS::randomLicensePlateNumber(
                licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            ),
            vehicleRegistrationServiceTypeCode: VehicleRegistrationServiceTypeCode::NZ,
            deregistered: false,
            vehicleType: VehicleRegistrationVehicleType::Car,
            electronicInsuranceConfirmationNumber: 'ABC1234',   // eVB-Nummer, exactly 7 chars
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleTitleSecurityCode: 'ABCDEF123456',           // ZB II code, exactly 12 chars
            iban: 'DE89370400440532013000',
            bic: 'COBADEFFXXX',
        ),
        vehicleHolderAddress: $address,
        vehicleHolderPlaceOfBirth: 'Berlin',   // required
        vehicleHolderBirthDate: '1990-01-31',  // required, ISO 8601
        vehicleHolderBirthName: 'Musterfrau',  // optional
        externalOrderId: 'registration-001',   // optional
        gksConfigurationId: 'your-gks-uuid',   // optional
    )
);

echo $response->orderId;
```

The response carries nothing but the order ID. The customer still **must** identify themselves and
sign the documents before anything is processed -- but both URLs arrive as webhooks, not in this
response: `identityVerificationUrl` on `VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_INITIALIZED` and
`documentSignatureUrl` on `VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_INITIALIZED`. Send the customer
to each as it comes in.

The assignment strategy is an object, not a plain enum value -- it serializes with a
`strategyType` discriminator and carries the plate data belonging to that strategy. Build it
with `DS::randomLicensePlateNumber(...)`, `DS::reservedLicensePlateNumber(...)` or
`DS::retainedLicensePlateNumber()`:

```php
licensePlateNumberAssignmentStrategy: DS::reservedLicensePlateNumber(
    plate: DS::plate('B', 'AB', '1234'),
    licensePlateType: VehicleRegistrationLicensePlateType::ElectricSeason,
    reservationPin: '1234',
    seasonStartMonth: 4,  // optional, for *_SEASON plate types
    seasonEndMonth: 10,   // optional
),
```

`RANDOM` leaves only the *number* to the registration office -- the plate type is still yours
to choose, so it takes a `licensePlateType` too. `RETAINMENT` carries nothing; pair it with
`DS::previousLicensePlate(...)` on the customization to keep an existing number.

Everything after the order creation arrives as `VEHICLE_REGISTRATION_*` webhooks: the identity
check and signing steps report their start and outcome, and `VEHICLE_REGISTRATION_XKFZ_EVENT`
carries the registration office's verdict along with the assigned plate. See
[Webhook Event Types](#webhook-event-types).

### Handling Webhooks

Set up a webhook receiver with the built-in middleware pipeline:

```php
use Dropshipping\Contracts\WebhookHandlerInterface;
use Dropshipping\DS;
use Dropshipping\DTO\Webhooks\WebhookEventInterface;
use Dropshipping\Enums\WebhookEventType;

// Implement a handler
class ShipmentHandler implements WebhookHandlerInterface
{
    public function supports(WebhookEventInterface $event): bool
    {
        return $event->getEventType() === WebhookEventType::DeliveryShipment;
    }

    public function handle(WebhookEventInterface $event): void
    {
        echo "Order {$event->order->id} shipped, tracking: {$event->delivery->trackingCode}\n";
    }
}

// Wire up pipeline and dispatcher
$dispatcher = DS::webhookDispatcher(DS::webhookPipeline($config->getWebhookSignatureSecret()));
$dispatcher->registerHandler(new ShipmentHandler());

// Receive a webhook (e.g. in a controller)
$dispatcher->dispatch(DS::incomingWebhook());
```

### Async Webhook Processing

For high-throughput scenarios, queue webhooks for background processing:

```php
use Dropshipping\Contracts\WebhookQueueInterface;
use Dropshipping\DS;

// Implement WebhookQueueInterface with your queue backend (Redis, RabbitMQ, database, etc.)
$queue = new YourQueueImplementation();

// In your HTTP controller: enqueue instead of processing inline
DS::queueWebhookDispatcher($queue, $config->getWebhookSignatureSecret())
    ->dispatch(DS::incomingWebhook());

// In a background worker process
$processed = DS::webhookWorker($queue, $dispatcher)->run(maxMessages: 100);
```

The signature is verified **before** the message is enqueued, so a forged payload never
reaches your queue. Catch `WebhookException` in the controller and answer `401`:

```php
use Dropshipping\Exceptions\WebhookException;

try {
    DS::queueWebhookDispatcher($queue, $config->getWebhookSignatureSecret())
        ->dispatch(DS::incomingWebhook());
} catch (WebhookException) {
    http_response_code(401);

    return;
}

http_response_code(202);
```

The worker-side pipeline verifies the signature a second time, which is intentional —
messages may sit in the queue across a secret rotation, and the worker is the last point
where a bad message can be rejected before a handler runs.

## Architecture Overview

```
src/
├── Async/              Queue-based webhook processing
├── Client/             API client and HTTP authentication
├── Configuration/      SDK configuration
├── Contracts/          Interfaces for serialization, webhooks, and queues
├── DS.php              Static facade — single import for all request DTOs
├── DTO/
│   ├── Requests/       Request objects with toArray() serialization
│   ├── Responses/      Response objects with fromArray() factories
│   └── Webhooks/       Webhook event types and factories
├── Endpoints/          API endpoint classes (Orders, Shipments, Products, Webhooks, GksConfigurations, VehicleDeregistrations, VehicleRegistrations)
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
| `$client->gksConfigurations->create()` | POST /gksConfigurations | Create a GKS configuration |
| `$client->gksConfigurations->update()` | PUT /gksConfigurations/{id} | Update a GKS configuration |
| `$client->gksConfigurations->getOverviews()` | GET /gksConfigurations/overviews | List all GKS configurations |
| `$client->gksConfigurations->getOverview()` | GET /gksConfigurations/overviews/{id} | Get a single GKS configuration |
| `$client->vehicleDeregistrations->createDeregistration()` | POST /vehicleDeregistrations/deregistrations | Submit a vehicle deregistration |
| `$client->vehicleDeregistrations->downloadFileContent()` | GET /vehicleDeregistrations/files/content/{fileAccessKey} | Download a file from a `VEHICLE_DEREGISTRATION_XKFZ_EVENT` webhook |
| `$client->vehicleRegistrations->createRegistration()` | POST /vehicleRegistrations/registrations | Submit a vehicle registration (**beta**) |
| `$client->vehicleRegistrations->downloadFileContent()` | GET /vehicleRegistrations/files/content/{fileAccessKey} | Download a file from a `VEHICLE_REGISTRATION_XKFZ_EVENT` webhook (**beta**) |

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
| `VEHICLE_DEREGISTRATION_XKFZ_EVENT` | `VehicleDeregistrationXkfzEvent` | Vehicle deregistration XKFZ status update — includes `status`, `derivedStatus`, optional `files` (with `fileAccessKey` for download), optional `costBreakdown`, and optional `messages` |
| `VEHICLE_REGISTRATION_XKFZ_EVENT` (**beta**) | `VehicleRegistrationXkfzEvent` | Vehicle registration XKFZ status update — same shape as the deregistration event, plus the assigned `licensePlate`. Files carry a `fileAccessKey` for `downloadFileContent()` |
| `VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_INITIALIZED` (**beta**) | `VehicleRegistrationIdentityVerificationInitializedEvent` | Identity check started — send the customer to `identityVerificationUrl` |
| `VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_SUCCEEDED` (**beta**) | `VehicleRegistrationIdentityVerificationSucceededEvent` | Customer identified successfully |
| `VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_FAILED` (**beta**) | `VehicleRegistrationIdentityVerificationFailedEvent` | Identity check failed, with an optional `message` |
| `VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_INITIALIZED` (**beta**) | `VehicleRegistrationDocumentSignatureInitializedEvent` | Signing started — send the customer to `documentSignatureUrl` |
| `VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_SUCCEEDED` (**beta**) | `VehicleRegistrationDocumentSignatureSucceededEvent` | Documents signed |
| `VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_FAILED` (**beta**) | `VehicleRegistrationDocumentSignatureFailedEvent` | Signing failed, with an optional `message` |
| _(any unrecognised type)_ | `UnknownWebhookEvent` | Only produced when the pipeline is built with `tolerateUnknownEvents: true`. Carries `rawEventType` and the full `payload` — see below |

#### Unknown event types

The table above covers webhooks spec 3.2.0. The API can start sending a new event type before a
matching SDK release exists — by default such an `eventType` throws a `WebhookException`. Opt into
tolerance to receive them instead:

```php
$pipeline = DS::webhookPipeline($secret, tolerateUnknownEvents: true);
```

Unrecognised payloads then arrive as `UnknownWebhookEvent` with `getEventType() === WebhookEventType::Unknown`,
the original type string in `rawEventType`, and the complete decoded payload in `payload`.

Note this only covers an unknown `eventType`. An unmodelled value *inside* a known event still
throws, except where the SDK defines a fallback — `status` on both XKFZ events degrades to
`UNKNOWN` rather than failing the delivery.

### Enums

| Enum | Values |
|------|--------|
| `Gender` | `FEMALE`, `MALE`, `UNSPECIFIED` |
| `VehicleType` | `CAR`, `MOTORCYCLE` |
| `LicensePlateType` | `REGULAR`, `REGULAR_SEASON`, `ELECTRIC`, `ELECTRIC_SEASON`, `HISTORICAL`, `HISTORICAL_SEASON` |
| `LicensePlateUsageType` | `EURO`, `PARKING` |
| `ProductType` | `LICENSE_PLATE`, `VEHICLE_DEREGISTRATION`, `VEHICLE_REGISTRATION`, `OTHER` |
| `VehicleDeregistrationVehicleType` | `CAR`, `LIGHT_MOTORCYCLE`, `MOTORCYCLE`, `OTHER`, `TRACTOR`, `TRAILER`, `TRUCK` |
| `VehicleDeregistrationLicensePlateType` | `REGULAR`, `REGULAR_SEASON`, `ELECTRIC`, `ELECTRIC_SEASON`, `HISTORICAL`, `HISTORICAL_SEASON` |
| `VehicleDeregistrationXkfzEventStatus` | `ACCEPTED`, `APPROVED`, `APPROVED_WITH_DOCUMENTS`, `FAILED`, `FORWARDED`, `PROCESSED`, `REJECTED`, `REJECTED_WITH_DOCUMENTS`, `UNKNOWN` |
| `VehicleDeregistrationXkfzEventFilePurposeType` | `CERTIFICATE`, `RECEIPT`, `APPLICATION`, `UNSPECIFIED` |
| `VehicleRegistrationVehicleType` (beta) | `CAR`, `MOTORCYCLE`, `TRAILER` |
| `VehicleRegistrationLicensePlateType` (beta) | `REGULAR`, `REGULAR_SEASON`, `ELECTRIC`, `ELECTRIC_SEASON`, `HISTORICAL`, `HISTORICAL_SEASON` |
| `VehicleRegistrationLicensePlateNumberAssignmentStrategyType` (beta) | `RANDOM`, `RESERVATION`, `RETAINMENT` |
| `VehicleRegistrationServiceTypeCode` (beta) | `NZ`, `WZ`, `UO`, `UI`, `UM`, `WG`, `UG`, `HA` |
| `VehicleRegistrationXkfzEventStatus` (beta) | `ACCEPTED`, `APPROVED`, `APPROVED_WITH_DOCUMENTS`, `FAILED`, `FORWARDED`, `PROCESSED`, `REJECTED`, `REJECTED_WITH_DOCUMENTS`, `UNKNOWN` |
| `VehicleRegistrationXkfzEventFilePurposeType` (beta) | `OTHER`, `PROVISIONAL_VEHICLE_REGISTRATION_CERTIFICATE`, `VEHICLE_REGISTRATION_APPLICATION_POWER_OF_ATTORNEY`, `VEHICLE_REGISTRATION_APPROVAL_NOTICE`, `VEHICLE_REGISTRATION_CERTIFICATE_TOKEN`, `VEHICLE_REGISTRATION_CHARGES_NOTICE`, `VEHICLE_REGISTRATION_ELECTRONIC_INSURANCE_CONFIRMATION`, `VEHICLE_REGISTRATION_GDPR_CONSENT_DECLARATION`, `VEHICLE_REGISTRATION_MOTOR_VEHICLE_TAX_SEPA_DIRECT_DEBIT_MANDATE`, `VEHICLE_REGISTRATION_REJECTION_NOTICE` |
| `WebhookEventType` | see [Webhook Event Types](#webhook-event-types), plus `UNKNOWN` for unrecognised types |

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

## Debugging

The SDK supports request/response logging via PHP constants. Define `KS_DROPSHIPPING_DEBUG` before making API calls to write detailed logs:

```php
define('KS_DROPSHIPPING_DEBUG', true);
```

By default, logs are written to `dropshipping-debug.log` in the current working directory. To use a custom log file path:

```php
define('KS_DROPSHIPPING_DEBUG', true);
define('KS_DROPSHIPPING_DEBUG_FILE', '/var/log/dropshipping.log');
```

The debug log includes:

- Timestamp, HTTP method and URL
- Request headers (Authorization is masked)
- Request body
- Response status code and headers
- Response body
- Exception details on transport failures

Example log output:

```
--------------------------------------------------------------------------------
[2026-02-03 14:30:00] POST https://api.example.com/dropshipping-api/123/2.1.0/orders

>>> REQUEST HEADERS
  Authorization: ***
  Content-Type: application/json
  Accept: application/json

>>> REQUEST BODY
{"externalId":"order-001","email":"max@example.com",...}

<<< RESPONSE 201 Created
  Content-Type: application/json
  X-Trace-Id: abc-123

<<< RESPONSE BODY
{"id":42,"status":"created"}
--------------------------------------------------------------------------------
```

## Error Handling

All exceptions extend `DropshippingException`:

| Exception | When |
|-----------|------|
| `DropshippingException` | Request DTO field validation failure (e.g. string too long, invalid email, empty required field). Thrown before any HTTP request is made. |
| `ApiException` | Non-expected HTTP status code from the API. Provides `getStatusCode()` and `getTraceId()` for debugging. |
| `HttpClientException` | PSR-18 client-level transport failure. Wraps the original `ClientExceptionInterface`. |
| `WebhookException` | Webhook signature verification or payload validation failure. |

All request DTOs validate their fields against the API spec constraints when constructed. Invalid values throw a `DropshippingException` with a descriptive message including the field name and the provided value:

```php
// Throws: Field "firstName" must be between 1 and 100 characters, got 110
new Address(firstName: str_repeat('x', 110), ...);

// Throws: Field "email" must be a valid email address
new OrderCreationRequest(email: 'not-an-email', ...);

// Throws: Field "seasonStartMonth" must be between 1 and 12, got 0
new LicensePlateReservationCustomization(seasonStartMonth: 0, ...);
```

## Development

```bash
composer check            # lint + static analysis + unit tests — run before committing
composer test:unit        # unit tests only
composer test:integration # hits the live API, needs credentials in .env
composer lint:fix         # apply code style
composer analyse          # PHPStan: src at level 8, tests at level 6
composer audit:security   # known CVEs in dependencies
```

PHP is formatted with [Laravel Pint](pint.json), not with your editor's built-in
formatter — the two disagree and will overwrite each other. The bundled
[.vscode/settings.json](.vscode/settings.json) disables format-on-save for PHP in this
project for that reason.

### Backward-compatibility check

Run before every release. It answers "if a customer upgrades, does the SDK still behave
the same?" by comparing the working tree against a released tag:

```bash
composer bc-check              # against the latest tag
composer bc-check -- v2.3.20   # against a specific ref
```

It reports two things:

1. **Public API surface** — every public class, property, method signature and enum case,
   diffed. A removed parameter or a newly required argument shows up here; that is what
   breaks a consumer's code at call time.
2. **Hydration behaviour** — a corpus of realistic payloads
   ([scripts/bc-check/corpus.php](scripts/bc-check/corpus.php)) is run through both
   versions' DTOs and the resulting objects are compared field by field. This catches
   silent behaviour changes that no unit test happens to cover.

The command exits non-zero on an unexplained behaviour difference, so it can gate a
release. Deliberate changes are recorded in
[scripts/bc-check/accepted.php](scripts/bc-check/accepted.php) with a reason and the
release they shipped in — without that, the check would stay red after the first
intentional change and everyone would learn to ignore it.

**When you add a DTO or a field, add a corpus entry for it.** The check can only compare
what the corpus exercises.

## License

Proprietary

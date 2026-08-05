<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Webhooks;

use Dropshipping\DTO\Webhooks\DeliveryCancellationEvent;
use Dropshipping\DTO\Webhooks\DeliveryReturnEvent;
use Dropshipping\DTO\Webhooks\DeliveryShipmentEvent;
use Dropshipping\DTO\Webhooks\LicensePlateReservationApprovalEvent;
use Dropshipping\DTO\Webhooks\LicensePlateReservationRejectionEvent;
use Dropshipping\DTO\Webhooks\LicensePlateReservationTimeoutEvent;
use Dropshipping\DTO\Webhooks\PingEvent;
use Dropshipping\DTO\Webhooks\UnknownWebhookEvent;
use Dropshipping\DTO\Webhooks\VehicleDeregistrationXkfzEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationDocumentSignatureFailedEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationDocumentSignatureInitializedEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationDocumentSignatureSucceededEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationIdentityVerificationFailedEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationIdentityVerificationInitializedEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationIdentityVerificationSucceededEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationXkfzEvent;
use Dropshipping\DTO\Webhooks\WebhookDelivery;
use Dropshipping\DTO\Webhooks\WebhookEventFactory;
use Dropshipping\DTO\Webhooks\WebhookOrder;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Exceptions\WebhookException;
use PHPUnit\Framework\TestCase;

final class WebhookEventFactoryTest extends TestCase
{
    public function test_fromArray_creates_ping_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'PING',
            'eventTime' => '2024-01-01T00:00:00Z',
        ]);

        self::assertInstanceOf(PingEvent::class, $event);
        self::assertSame(WebhookEventType::Ping, $event->getEventType());
        self::assertSame('2024-01-01T00:00:00Z', $event->getEventTime());
    }

    public function test_fromArray_creates_delivery_shipment_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'DELIVERY_SHIPMENT',
            'eventTime' => '2024-01-01T00:00:00Z',
            'delivery' => ['id' => 1, 'trackingCode' => 'TRACK'],
            'order' => ['id' => 10, 'externalId' => 'ext-1'],
        ]);

        self::assertInstanceOf(DeliveryShipmentEvent::class, $event);
        self::assertSame(1, $event->delivery->id);
        self::assertSame('TRACK', $event->delivery->trackingCode);
        self::assertSame(10, $event->order->id);
    }

    public function test_fromArray_creates_delivery_return_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'DELIVERY_RETURN',
            'eventTime' => '2024-01-01T00:00:00Z',
            'delivery' => ['id' => 1],
            'order' => ['id' => 10],
            'returnReason' => 'Damaged',
            'reshippingOfferExpirationDate' => '2024-02-01',
        ]);

        self::assertInstanceOf(DeliveryReturnEvent::class, $event);
        self::assertSame('Damaged', $event->returnReason);
        self::assertSame('2024-02-01', $event->reshippingOfferExpirationDate);
    }

    public function test_fromArray_creates_delivery_cancellation_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'DELIVERY_CANCELLATION',
            'eventTime' => '2024-01-01T00:00:00Z',
            'delivery' => ['id' => 1],
            'order' => ['id' => 10],
        ]);

        self::assertInstanceOf(DeliveryCancellationEvent::class, $event);
    }

    public function test_fromArray_creates_license_plate_reservation_approval_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'LICENSE_PLATE_RESERVATION_APPROVAL',
            'eventTime' => '2024-01-01T00:00:00Z',
            'order' => ['id' => 10],
            'reservationPin' => '1234',
            'customization' => [
                'registrationOfficeServiceId' => 602,
                'licensePlateType' => 'REGULAR',
                'vehicleType' => 'CAR',
                'licensePlateNumberComponents' => ['city' => 'B', 'middle' => 'AB', 'end' => '1'],
            ],
            'reservationPrice' => '12.50',
        ]);

        self::assertInstanceOf(LicensePlateReservationApprovalEvent::class, $event);
        self::assertSame('1234', $event->reservationPin);
        self::assertSame('12.50', $event->reservationPrice);
    }

    public function test_fromArray_creates_license_plate_reservation_rejection_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'LICENSE_PLATE_RESERVATION_REJECTION',
            'eventTime' => '2024-01-01T00:00:00Z',
            'order' => ['id' => 10],
            'customization' => [
                'registrationOfficeServiceId' => 602,
                'licensePlateType' => 'REGULAR',
                'vehicleType' => 'CAR',
                'licensePlateNumberComponents' => ['city' => 'B', 'middle' => 'AB', 'end' => '1'],
            ],
            'proposedAlternativeLicensePlateNumberComponents' => [
                ['city' => 'B', 'middle' => 'XY', 'end' => '999'],
            ],
        ]);

        self::assertInstanceOf(LicensePlateReservationRejectionEvent::class, $event);
        self::assertCount(1, $event->proposedAlternativeLicensePlateNumberComponents);
    }

    public function test_fromArray_creates_license_plate_reservation_timeout_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'LICENSE_PLATE_RESERVATION_TIMEOUT',
            'eventTime' => '2024-01-01T00:00:00Z',
            'order' => ['id' => 10],
            'customization' => [
                'registrationOfficeServiceId' => 602,
                'licensePlateType' => 'REGULAR',
                'vehicleType' => 'CAR',
                'licensePlateNumberComponents' => ['city' => 'B', 'middle' => 'AB', 'end' => '1'],
            ],
        ]);

        self::assertInstanceOf(LicensePlateReservationTimeoutEvent::class, $event);
    }

    public function test_fromArray_creates_vehicle_deregistration_xkfz_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'VEHICLE_DEREGISTRATION_XKFZ_EVENT',
            'eventTime' => '2026-04-12T20:35:07',
            'order' => ['id' => 7, 'externalId' => 'dropshipping-client-external-id'],
            'status' => 'APPROVED_WITH_DOCUMENTS',
            'derivedStatus' => 'SUCCESS',
            'files' => [
                ['purposeType' => 'CERTIFICATE', 'mediaType' => 'application/pdf', 'fileAccessKey' => '7_4d61a48b-7209-4e4c-959b-fedfbda248e2_4', 'expirationTime' => '2026-04-12T21:35:07'],
            ],
            'costBreakdown' => [
                'kbaCost' => 350,
                'registrationOfficeCosts' => [
                    'items' => [
                        ['number' => 1, 'code' => 'FEE', 'name' => 'Service fee', 'amount' => 200, 'note' => null],
                    ],
                    'total' => ['number' => 0, 'code' => null, 'name' => 'Total', 'amount' => 200, 'note' => null],
                    'note' => 'Test note',
                ],
            ],
        ]);

        self::assertInstanceOf(VehicleDeregistrationXkfzEvent::class, $event);
        self::assertSame(WebhookEventType::VehicleDeregistrationXkfzEvent, $event->getEventType());
        self::assertSame('2026-04-12T20:35:07', $event->getEventTime());
        self::assertSame(7, $event->order->id);
        self::assertNotNull($event->files);
        self::assertCount(1, $event->files);
        self::assertNotNull($event->costBreakdown);
        self::assertSame(350, $event->costBreakdown->kbaCost);
        self::assertNotNull($event->costBreakdown->registrationOfficeCosts);
        self::assertCount(1, $event->costBreakdown->registrationOfficeCosts->items);
        self::assertSame(200, $event->costBreakdown->registrationOfficeCosts->total->amount);
    }

    public function test_fromArray_creates_vehicle_deregistration_xkfz_event_without_optional_fields(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'VEHICLE_DEREGISTRATION_XKFZ_EVENT',
            'eventTime' => '2024-06-15T10:30:00Z',
            'order' => ['id' => 42],
            'status' => 'PROCESSED',
            'derivedStatus' => 'SUCCESS',
        ]);

        self::assertInstanceOf(VehicleDeregistrationXkfzEvent::class, $event);
        self::assertNull($event->files);
        self::assertNull($event->costBreakdown);
    }

    public function test_fromArray_creates_vehicle_registration_xkfz_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'VEHICLE_REGISTRATION_XKFZ_EVENT',
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2, 'externalId' => 'ext-2'],
            'status' => 'APPROVED_WITH_DOCUMENTS',
            'derivedStatus' => 'SUCCESS',
            'files' => [
                ['purposeType' => 'VEHICLE_REGISTRATION_APPROVAL_NOTICE', 'mediaType' => 'application/pdf', 'fileAccessKey' => 'key-1', 'expirationTime' => '2000-10-31T01:30:44'],
            ],
            'costBreakdown' => [
                'kbaCost' => 350,
                'registrationOfficeCosts' => [
                    'items' => [
                        ['number' => 1, 'code' => 'FEE', 'name' => 'Service fee', 'amount' => 200, 'note' => null],
                    ],
                    'total' => ['number' => 0, 'code' => null, 'name' => 'Total', 'amount' => 200, 'note' => null],
                    'note' => 'Test note',
                ],
            ],
            'licensePlate' => [
                'licensePlateNumberComponents' => ['usageType' => 'EURO', 'city' => 'KÜ', 'middle' => 'PO', 'end' => '321'],
                'licensePlateType' => 'ELECTRIC_SEASON',
                'seasonStartMonth' => 1,
                'seasonEndMonth' => 2,
            ],
        ]);

        self::assertInstanceOf(VehicleRegistrationXkfzEvent::class, $event);
        self::assertSame(WebhookEventType::VehicleRegistrationXkfzEvent, $event->getEventType());
        self::assertSame('2023-10-31T12:34:56', $event->getEventTime());
        self::assertSame(2, $event->order->id);
        self::assertNotNull($event->files);
        self::assertCount(1, $event->files);
        self::assertNotNull($event->costBreakdown);
        self::assertSame(350, $event->costBreakdown->kbaCost);
        self::assertNotNull($event->costBreakdown->registrationOfficeCosts);
        self::assertSame(200, $event->costBreakdown->registrationOfficeCosts->total->amount);
        self::assertNotNull($event->licensePlate);
        self::assertSame('KÜ', $event->licensePlate->licensePlateNumberComponents->city);
        self::assertSame(VehicleRegistrationLicensePlateType::ElectricSeason, $event->licensePlate->licensePlateType);
    }

    public function test_fromArray_creates_vehicle_registration_xkfz_event_without_optional_fields(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'VEHICLE_REGISTRATION_XKFZ_EVENT',
            'eventTime' => '2024-06-15T10:30:00Z',
            'order' => ['id' => 42],
            'status' => 'PROCESSED',
            'derivedStatus' => 'SUCCESS',
        ]);

        self::assertInstanceOf(VehicleRegistrationXkfzEvent::class, $event);
        self::assertNull($event->files);
        self::assertNull($event->costBreakdown);
        self::assertNull($event->licensePlate);
    }

    public function test_fromArray_creates_vehicle_registration_identity_verification_initialized_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_INITIALIZED',
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2, 'externalId' => null],
            'identityVerificationVendor' => ['id' => 1],
            'identityVerificationUrl' => 'https://go.test.idnow.de/identifications/abc_123',
        ]);

        self::assertInstanceOf(VehicleRegistrationIdentityVerificationInitializedEvent::class, $event);
        self::assertSame(WebhookEventType::VehicleRegistrationIdentityVerificationInitialized, $event->getEventType());
        self::assertSame(2, $event->order->id);
        self::assertSame(1, $event->identityVerificationVendor->id);
        self::assertSame('https://go.test.idnow.de/identifications/abc_123', $event->identityVerificationUrl);
    }

    public function test_fromArray_creates_vehicle_registration_identity_verification_succeeded_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_SUCCEEDED',
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2],
            'identityVerificationVendor' => ['id' => 1],
        ]);

        self::assertInstanceOf(VehicleRegistrationIdentityVerificationSucceededEvent::class, $event);
        self::assertSame(WebhookEventType::VehicleRegistrationIdentityVerificationSucceeded, $event->getEventType());
        self::assertSame(1, $event->identityVerificationVendor->id);
    }

    public function test_fromArray_creates_vehicle_registration_identity_verification_failed_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'VEHICLE_REGISTRATION_IDENTITY_VERIFICATION_FAILED',
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2],
            'identityVerificationVendor' => ['id' => 1],
            'message' => 'Document could not be read',
        ]);

        self::assertInstanceOf(VehicleRegistrationIdentityVerificationFailedEvent::class, $event);
        self::assertSame(WebhookEventType::VehicleRegistrationIdentityVerificationFailed, $event->getEventType());
        self::assertSame('Document could not be read', $event->message);
    }

    public function test_fromArray_creates_vehicle_registration_document_signature_initialized_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_INITIALIZED',
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2],
            'identityVerificationVendor' => ['id' => 1],
            'documentSignatureUrl' => 'https://go.test.idnow.de/instantsign/abc_123',
        ]);

        self::assertInstanceOf(VehicleRegistrationDocumentSignatureInitializedEvent::class, $event);
        self::assertSame(WebhookEventType::VehicleRegistrationDocumentSignatureInitialized, $event->getEventType());
        self::assertSame('https://go.test.idnow.de/instantsign/abc_123', $event->documentSignatureUrl);
    }

    public function test_fromArray_creates_vehicle_registration_document_signature_succeeded_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_SUCCEEDED',
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2],
            'identityVerificationVendor' => ['id' => 1],
        ]);

        self::assertInstanceOf(VehicleRegistrationDocumentSignatureSucceededEvent::class, $event);
        self::assertSame(WebhookEventType::VehicleRegistrationDocumentSignatureSucceeded, $event->getEventType());
        self::assertSame(2, $event->order->id);
    }

    public function test_fromArray_creates_vehicle_registration_document_signature_failed_event(): void
    {
        $event = WebhookEventFactory::fromArray([
            'eventType' => 'VEHICLE_REGISTRATION_DOCUMENT_SIGNATURE_FAILED',
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2],
            'identityVerificationVendor' => ['id' => 1],
        ]);

        self::assertInstanceOf(VehicleRegistrationDocumentSignatureFailedEvent::class, $event);
        self::assertSame(WebhookEventType::VehicleRegistrationDocumentSignatureFailed, $event->getEventType());
        self::assertNull($event->message);
    }

    public function test_fromArray_throws_on_missing_eventType(): void
    {
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Missing eventType');

        WebhookEventFactory::fromArray(['eventTime' => '2024-01-01T00:00:00Z']);
    }

    public function test_fromArray_throws_on_unknown_eventType(): void
    {
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Unknown webhook event type');

        WebhookEventFactory::fromArray(['eventType' => 'UNKNOWN_TYPE', 'eventTime' => '2024-01-01T00:00:00Z']);
    }

    public function test_fromArray_returns_unknown_event_when_tolerated(): void
    {
        $payload = [
            'eventType' => 'VEHICLE_REGISTRATION_SOME_FUTURE_EVENT',
            'eventTime' => '2024-01-01T00:00:00Z',
            'order' => ['id' => 42],
        ];

        $event = WebhookEventFactory::fromArray($payload, tolerateUnknown: true);

        self::assertInstanceOf(UnknownWebhookEvent::class, $event);
        self::assertSame(WebhookEventType::Unknown, $event->getEventType());
        self::assertSame('VEHICLE_REGISTRATION_SOME_FUTURE_EVENT', $event->rawEventType);
        self::assertSame('2024-01-01T00:00:00Z', $event->getEventTime());
        self::assertSame($payload, $event->payload);
    }

    public function test_fromArray_still_throws_on_missing_eventType_when_tolerated(): void
    {
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Missing eventType');

        WebhookEventFactory::fromArray(['eventTime' => '2024-01-01T00:00:00Z'], tolerateUnknown: true);
    }

    public function test_fromArray_still_resolves_known_events_when_tolerated(): void
    {
        $event = WebhookEventFactory::fromArray(
            ['eventType' => 'PING', 'eventTime' => '2024-01-01T00:00:00Z'],
            tolerateUnknown: true,
        );

        self::assertSame(WebhookEventType::Ping, $event->getEventType());
    }

    public function test_webhook_delivery_fromArray(): void
    {
        $delivery = WebhookDelivery::fromArray(['id' => 5, 'trackingCode' => 'ABC']);
        self::assertSame(5, $delivery->id);
        self::assertSame('ABC', $delivery->trackingCode);

        $deliveryNoTracking = WebhookDelivery::fromArray(['id' => 6]);
        self::assertNull($deliveryNoTracking->trackingCode);
    }

    public function test_webhook_order_fromArray(): void
    {
        $order = WebhookOrder::fromArray(['id' => 10, 'externalId' => 'ext-1']);
        self::assertSame(10, $order->id);
        self::assertSame('ext-1', $order->externalId);

        $orderNoExternal = WebhookOrder::fromArray(['id' => 11]);
        self::assertNull($orderNoExternal->externalId);
    }
}

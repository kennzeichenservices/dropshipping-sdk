<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Webhooks;

use Dropshipping\DTO\Webhooks\VehicleRegistrationDocumentSignatureFailedEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationDocumentSignatureInitializedEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationDocumentSignatureSucceededEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationIdentityVerificationFailedEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationIdentityVerificationInitializedEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationIdentityVerificationSucceededEvent;
use Dropshipping\DTO\Webhooks\VehicleRegistrationIdentityVerificationVendor;
use Dropshipping\Enums\WebhookEventType;
use PHPUnit\Framework\TestCase;

/**
 * Covers the six flat vehicle registration lifecycle events.
 *
 * The factory test proves each eventType maps to the right class; this one covers
 * what it does not: the optional failure message, and the vendor value object.
 */
final class VehicleRegistrationLifecycleEventsTest extends TestCase
{
    public function test_identity_verification_initialized_fromArray(): void
    {
        $event = VehicleRegistrationIdentityVerificationInitializedEvent::fromArray([
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2, 'externalId' => null],
            'identityVerificationVendor' => ['id' => 1],
            'identityVerificationUrl' => 'https://go.test.idnow.de/identifications/abc_123',
        ]);

        self::assertSame(WebhookEventType::VehicleRegistrationIdentityVerificationInitialized, $event->getEventType());
        self::assertSame('2023-10-31T12:34:56', $event->getEventTime());
        self::assertSame(2, $event->order->id);
        self::assertNull($event->order->externalId);
        self::assertSame(1, $event->identityVerificationVendor->id);
        self::assertSame('https://go.test.idnow.de/identifications/abc_123', $event->identityVerificationUrl);
    }

    public function test_identity_verification_succeeded_fromArray(): void
    {
        $event = VehicleRegistrationIdentityVerificationSucceededEvent::fromArray([
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2],
            'identityVerificationVendor' => ['id' => 1],
        ]);

        self::assertSame(WebhookEventType::VehicleRegistrationIdentityVerificationSucceeded, $event->getEventType());
        self::assertSame('2023-10-31T12:34:56', $event->getEventTime());
        self::assertSame(2, $event->order->id);
        self::assertSame(1, $event->identityVerificationVendor->id);
    }

    public function test_identity_verification_failed_fromArray_with_message(): void
    {
        $event = VehicleRegistrationIdentityVerificationFailedEvent::fromArray([
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2],
            'identityVerificationVendor' => ['id' => 1],
            'message' => 'Document could not be read',
        ]);

        self::assertSame(WebhookEventType::VehicleRegistrationIdentityVerificationFailed, $event->getEventType());
        self::assertSame('Document could not be read', $event->message);
    }

    public function test_identity_verification_failed_fromArray_without_message(): void
    {
        $event = VehicleRegistrationIdentityVerificationFailedEvent::fromArray([
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2],
            'identityVerificationVendor' => ['id' => 1],
        ]);

        self::assertNull($event->message);
    }

    public function test_document_signature_initialized_fromArray(): void
    {
        $event = VehicleRegistrationDocumentSignatureInitializedEvent::fromArray([
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2],
            'identityVerificationVendor' => ['id' => 1],
            'documentSignatureUrl' => 'https://go.test.idnow.de/instantsign/abc_123',
        ]);

        self::assertSame(WebhookEventType::VehicleRegistrationDocumentSignatureInitialized, $event->getEventType());
        self::assertSame('2023-10-31T12:34:56', $event->getEventTime());
        self::assertSame('https://go.test.idnow.de/instantsign/abc_123', $event->documentSignatureUrl);
    }

    public function test_document_signature_succeeded_fromArray(): void
    {
        $event = VehicleRegistrationDocumentSignatureSucceededEvent::fromArray([
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2],
            'identityVerificationVendor' => ['id' => 1],
        ]);

        self::assertSame(WebhookEventType::VehicleRegistrationDocumentSignatureSucceeded, $event->getEventType());
        self::assertSame(2, $event->order->id);
        self::assertSame(1, $event->identityVerificationVendor->id);
    }

    public function test_document_signature_failed_fromArray_with_message(): void
    {
        $event = VehicleRegistrationDocumentSignatureFailedEvent::fromArray([
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2],
            'identityVerificationVendor' => ['id' => 1],
            'message' => 'Signature timed out',
        ]);

        self::assertSame(WebhookEventType::VehicleRegistrationDocumentSignatureFailed, $event->getEventType());
        self::assertSame('Signature timed out', $event->message);
    }

    public function test_document_signature_failed_fromArray_without_message(): void
    {
        $event = VehicleRegistrationDocumentSignatureFailedEvent::fromArray([
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2],
            'identityVerificationVendor' => ['id' => 1],
        ]);

        self::assertNull($event->message);
    }

    public function test_identity_verification_vendor_fromArray(): void
    {
        $vendor = VehicleRegistrationIdentityVerificationVendor::fromArray(['id' => 7]);

        self::assertSame(7, $vendor->id);
    }
}

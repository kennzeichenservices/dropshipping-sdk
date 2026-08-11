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
use Dropshipping\Enums\VehicleRegistrationApplicationFilePurposeType;
use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\TestCase;

/**
 * Covers the six vehicle registration lifecycle events that precede the XKFZ verdict.
 *
 * The factory test proves each eventType maps to the right class; this one covers
 * what it does not: the optional failure message, the vendor value object, and the
 * signed application files on the document signature success.
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
            'applicationFiles' => [
                [
                    'purposeType' => 'VEHICLE_REGISTRATION_APPLICATION_POWER_OF_ATTORNEY',
                    'mediaType' => 'application/pdf',
                    'fileAccessKey' => '2_11111111-1111-1111-1111-111111111111_78_1234',
                    'expirationTime' => '2000-10-31T01:30:44',
                ],
                [
                    'purposeType' => 'VEHICLE_REGISTRATION_MOTOR_VEHICLE_TAX_SEPA_DIRECT_DEBIT_MANDATE',
                    'mediaType' => 'application/pdf',
                    'fileAccessKey' => '2_11111111-1111-1111-1111-111111111111_78_123456',
                    'expirationTime' => '2000-10-31T01:30:46',
                ],
            ],
        ]);

        self::assertSame(WebhookEventType::VehicleRegistrationDocumentSignatureSucceeded, $event->getEventType());
        self::assertSame(2, $event->order->id);
        self::assertSame(1, $event->identityVerificationVendor->id);

        self::assertCount(2, $event->applicationFiles);
        self::assertSame(
            VehicleRegistrationApplicationFilePurposeType::VehicleRegistrationApplicationPowerOfAttorney,
            $event->applicationFiles[0]->purposeType,
        );
        self::assertSame('application/pdf', $event->applicationFiles[0]->mediaType);
        self::assertSame('2_11111111-1111-1111-1111-111111111111_78_1234', $event->applicationFiles[0]->fileAccessKey);
        self::assertSame('2000-10-31T01:30:44', $event->applicationFiles[0]->expirationTime);
        self::assertSame(
            VehicleRegistrationApplicationFilePurposeType::VehicleRegistrationMotorVehicleTaxSepaDirectDebitMandate,
            $event->applicationFiles[1]->purposeType,
        );
    }

    /**
     * `applicationFiles` is required as of webhooks 3.2.0, but an event without it is
     * still delivered rather than throwing — the handler just sees an empty list.
     */
    public function test_document_signature_succeeded_fromArray_without_application_files(): void
    {
        $event = VehicleRegistrationDocumentSignatureSucceededEvent::fromArray([
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2],
            'identityVerificationVendor' => ['id' => 1],
        ]);

        self::assertSame([], $event->applicationFiles);
    }

    public function test_document_signature_succeeded_fromArray_rejects_an_unmodelled_purpose_type(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('Unsupported value string("VEHICLE_REGISTRATION_APPROVAL_NOTICE")');

        VehicleRegistrationDocumentSignatureSucceededEvent::fromArray([
            'eventTime' => '2023-10-31T12:34:56',
            'order' => ['id' => 2],
            'identityVerificationVendor' => ['id' => 1],
            'applicationFiles' => [
                [
                    // Valid for an XKFZ event file, but not for a signed application file.
                    'purposeType' => 'VEHICLE_REGISTRATION_APPROVAL_NOTICE',
                    'mediaType' => 'application/pdf',
                    'fileAccessKey' => 'key',
                    'expirationTime' => '2000-10-31T01:30:44',
                ],
            ],
        ]);
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

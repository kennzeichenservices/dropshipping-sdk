<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Support\Hydrator;

/**
 * Webhook event fired when the customer has signed the documents for a vehicle registration.
 *
 * Carries the signed documents themselves as {@see $applicationFiles}: the power of
 * attorney, the GDPR consent declaration and the SEPA mandate for the motor vehicle
 * tax. This is the only event that offers them — the registration office's own
 * documents arrive later, on VEHICLE_REGISTRATION_XKFZ_EVENT.
 */
final readonly class VehicleRegistrationDocumentSignatureSucceededEvent implements WebhookEventInterface
{
    private const CONTEXT = 'VehicleRegistrationDocumentSignatureSucceededEvent';

    /**
     * @param string                                        $eventTime                  The ISO 8601 timestamp when the event occurred.
     * @param WebhookOrder                                  $order                      The associated order reference.
     * @param VehicleRegistrationIdentityVerificationVendor $identityVerificationVendor The vendor that handled the signing.
     * @param list<VehicleRegistrationApplicationFile>      $applicationFiles           The signed application documents.
     */
    public function __construct(
        public string $eventTime,
        public WebhookOrder $order,
        public VehicleRegistrationIdentityVerificationVendor $identityVerificationVendor,
        public array $applicationFiles = [],
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::VehicleRegistrationDocumentSignatureSucceeded;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventTime(): string
    {
        return $this->eventTime;
    }

    /**
     * Create an instance from a raw data array.
     *
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            eventTime: Hydrator::requireString($data, 'eventTime', self::CONTEXT),
            order: WebhookOrder::fromArray(Hydrator::requireArray($data, 'order', self::CONTEXT)),
            identityVerificationVendor: VehicleRegistrationIdentityVerificationVendor::fromArray(
                Hydrator::requireArray($data, 'identityVerificationVendor', self::CONTEXT),
            ),
            // Required as of webhooks 3.2.0, but read leniently: an event that predates
            // the field, or one whose signing produced no documents, is still worth
            // delivering to the handler.
            applicationFiles: array_map(
                static fn (array $file): VehicleRegistrationApplicationFile => VehicleRegistrationApplicationFile::fromArray($file),
                Hydrator::optionalArrayList($data, 'applicationFiles', self::CONTEXT),
            ),
        );
    }
}

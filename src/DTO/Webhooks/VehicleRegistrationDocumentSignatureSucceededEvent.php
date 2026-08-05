<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Support\Hydrator;

/**
 * Webhook event fired when the customer has signed the documents for a vehicle registration.
 *
 * @experimental Vehicle registration webhook events are new in the dropshipping
 *               webhooks API (3.2.0) and may change without a major version bump.
 */
final readonly class VehicleRegistrationDocumentSignatureSucceededEvent implements WebhookEventInterface
{
    private const CONTEXT = 'VehicleRegistrationDocumentSignatureSucceededEvent';

    /**
     * @param string                                       $eventTime                  The ISO 8601 timestamp when the event occurred.
     * @param WebhookOrder                                 $order                      The associated order reference.
     * @param VehicleRegistrationIdentityVerificationVendor $identityVerificationVendor The vendor that handled the signing.
     */
    public function __construct(
        public string $eventTime,
        public WebhookOrder $order,
        public VehicleRegistrationIdentityVerificationVendor $identityVerificationVendor,
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
        );
    }
}

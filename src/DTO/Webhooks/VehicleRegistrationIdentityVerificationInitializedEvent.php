<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Support\Hydrator;

/**
 * Webhook event fired when identity verification for a vehicle registration has started.
 *
 * Carries the URL the customer has to visit to identify themselves. Nothing is
 * processed until they complete it.
 */
final readonly class VehicleRegistrationIdentityVerificationInitializedEvent implements WebhookEventInterface
{
    private const CONTEXT = 'VehicleRegistrationIdentityVerificationInitializedEvent';

    /**
     * @param string                                       $eventTime                  The ISO 8601 timestamp when the event occurred.
     * @param WebhookOrder                                 $order                      The associated order reference.
     * @param VehicleRegistrationIdentityVerificationVendor $identityVerificationVendor The vendor handling the verification.
     * @param string                                       $identityVerificationUrl    Where to send the customer to identify themselves.
     */
    public function __construct(
        public string $eventTime,
        public WebhookOrder $order,
        public VehicleRegistrationIdentityVerificationVendor $identityVerificationVendor,
        public string $identityVerificationUrl,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::VehicleRegistrationIdentityVerificationInitialized;
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
            identityVerificationUrl: Hydrator::requireString($data, 'identityVerificationUrl', self::CONTEXT),
        );
    }
}

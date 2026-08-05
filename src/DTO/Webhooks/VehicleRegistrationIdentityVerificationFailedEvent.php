<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Support\Hydrator;

/**
 * Webhook event fired when identity verification for a vehicle registration failed.
 *
 * @experimental Vehicle registration webhook events are new in the dropshipping
 *               webhooks API (3.2.0) and may change without a major version bump.
 */
final readonly class VehicleRegistrationIdentityVerificationFailedEvent implements WebhookEventInterface
{
    private const CONTEXT = 'VehicleRegistrationIdentityVerificationFailedEvent';

    /**
     * @param string                                       $eventTime                  The ISO 8601 timestamp when the event occurred.
     * @param WebhookOrder                                 $order                      The associated order reference.
     * @param VehicleRegistrationIdentityVerificationVendor $identityVerificationVendor The vendor that handled the verification.
     * @param string|null                                  $message                    Reason for the failure, if the vendor supplied one.
     *                                                                                 The spec declares `format: uri` for this field, which
     *                                                                                 looks like a copy-paste from the neighbouring URL
     *                                                                                 properties — it is treated as a plain string.
     */
    public function __construct(
        public string $eventTime,
        public WebhookOrder $order,
        public VehicleRegistrationIdentityVerificationVendor $identityVerificationVendor,
        public ?string $message,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::VehicleRegistrationIdentityVerificationFailed;
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
            message: Hydrator::optionalString($data, 'message', self::CONTEXT),
        );
    }
}

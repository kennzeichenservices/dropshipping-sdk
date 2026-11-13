<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\VehicleRegistrationIdentityVerificationFailureReason;
use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Support\Hydrator;

/**
 * Webhook event fired when identity verification for a vehicle registration failed.
 */
final readonly class VehicleRegistrationIdentityVerificationFailedEvent implements WebhookEventInterface
{
    private const CONTEXT = 'VehicleRegistrationIdentityVerificationFailedEvent';

    /**
     * @param string                                       $eventTime                  The ISO 8601 timestamp when the event occurred.
     * @param WebhookOrder                                 $order                      The associated order reference.
     * @param VehicleRegistrationIdentityVerificationVendor $identityVerificationVendor The vendor that handled the verification.
     * @param string|null                                  $message                    Reason for the failure, if one was supplied. Either a
     *                                                                                 key of the provider's own company and representative
     *                                                                                 check for a legal person, which refuses the order
     *                                                                                 before the vendor is involved (resolved by
     *                                                                                 {@see self::failureReason()}), or a free text of the
     *                                                                                 identification vendor.
     *                                                                                 The spec declares `format: uri` for this field, which
     *                                                                                 looks like a copy-paste from the neighbouring URL
     *                                                                                 properties, so it is treated as a plain string.
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
     * The reason of the failure, where the message is one of the known keys.
     *
     * Those are the outcomes of the company and representative check that
     * precedes the identification of a legal person. Null for an event without
     * a message and for any other text, which stays readable in `$message`.
     */
    public function failureReason(): ?VehicleRegistrationIdentityVerificationFailureReason
    {
        return VehicleRegistrationIdentityVerificationFailureReason::fromMessage($this->message);
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

<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Support\Hydrator;

/**
 * Webhook event fired when a durable power of attorney failed, i.e. reached the
 * status ERROR because document generation or signature did not succeed.
 *
 * Unlike the registration events it carries no order: a durable power of
 * attorney exists independently of any order.
 */
final readonly class VehicleRegistrationDurablePowerOfAttorneyErrorEvent implements WebhookEventInterface
{
    private const CONTEXT = 'VehicleRegistrationDurablePowerOfAttorneyErrorEvent';

    /**
     * @param string                        $eventTime              The ISO 8601 timestamp when the event occurred.
     * @param WebhookDurablePowerOfAttorney $durablePowerOfAttorney The power of attorney that failed.
     * @param string                        $message                Reason for the failure. Free text, the spec lists no values.
     */
    public function __construct(
        public string $eventTime,
        public WebhookDurablePowerOfAttorney $durablePowerOfAttorney,
        public string $message,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::VehicleRegistrationDurablePowerOfAttorneyError;
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
     */
    public static function fromArray(array $data): self
    {
        return new self(
            eventTime: Hydrator::requireString($data, 'eventTime', self::CONTEXT),
            durablePowerOfAttorney: WebhookDurablePowerOfAttorney::fromArray(
                Hydrator::requireArray($data, 'durablePowerOfAttorney', self::CONTEXT),
            ),
            message: Hydrator::requireString($data, 'message', self::CONTEXT),
        );
    }
}

<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;

/**
 * Webhook event fired when a vehicle deregistration XKFZ status update is received.
 *
 * Contains the order reference and an optional cost breakdown detailing
 * KBA and registration office charges.
 */
final readonly class VehicleDeregistrationXkfzEvent implements WebhookEventInterface
{
    /**
     * @param string                                       $eventTime     The ISO 8601 timestamp when the event occurred.
     * @param WebhookOrder                                 $order         The associated order reference.
     * @param VehicleDeregistrationCostBreakdown|null       $costBreakdown The cost breakdown, if available.
     */
    public function __construct(
        public string $eventTime,
        public WebhookOrder $order,
        public ?VehicleDeregistrationCostBreakdown $costBreakdown,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::VehicleDeregistrationXkfzEvent;
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
            eventTime: $data['eventTime'],
            order: WebhookOrder::fromArray($data['order']),
            costBreakdown: isset($data['costBreakdown'])
                ? VehicleDeregistrationCostBreakdown::fromArray($data['costBreakdown'])
                : null,
        );
    }
}

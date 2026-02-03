<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;

/**
 * Webhook event fired when a delivery has been shipped.
 *
 * Contains the delivery and order references associated with the shipment.
 */
final readonly class DeliveryShipmentEvent implements WebhookEventInterface
{
    /**
     * @param string          $eventTime The ISO 8601 timestamp when the shipment occurred.
     * @param WebhookDelivery $delivery  The delivery reference.
     * @param WebhookOrder    $order     The associated order reference.
     */
    public function __construct(
        public string $eventTime,
        public WebhookDelivery $delivery,
        public WebhookOrder $order,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::DeliveryShipment;
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
            delivery: WebhookDelivery::fromArray($data['delivery']),
            order: WebhookOrder::fromArray($data['order']),
        );
    }
}

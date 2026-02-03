<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;

/**
 * Webhook event fired when a delivery has been returned.
 *
 * Contains the delivery and order references, the reason for the return,
 * and the expiration date for the reshipping offer.
 */
final readonly class DeliveryReturnEvent implements WebhookEventInterface
{
    /**
     * @param string          $eventTime                     The ISO 8601 timestamp when the return was registered.
     * @param WebhookDelivery $delivery                      The delivery reference.
     * @param WebhookOrder    $order                         The associated order reference.
     * @param string          $returnReason                  The reason the delivery was returned.
     * @param string          $reshippingOfferExpirationDate The expiration date for the reshipping offer.
     */
    public function __construct(
        public string $eventTime,
        public WebhookDelivery $delivery,
        public WebhookOrder $order,
        public string $returnReason,
        public string $reshippingOfferExpirationDate,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::DeliveryReturn;
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
            returnReason: $data['returnReason'],
            reshippingOfferExpirationDate: $data['reshippingOfferExpirationDate'],
        );
    }
}

<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;

final readonly class DeliveryReturnEvent implements WebhookEventInterface
{
    public function __construct(
        public string $eventTime,
        public WebhookDelivery $delivery,
        public WebhookOrder $order,
        public string $returnReason,
        public string $reshippingOfferExpirationDate,
    ) {
    }

    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::DeliveryReturn;
    }

    public function getEventTime(): string
    {
        return $this->eventTime;
    }

    /** @param array<string, mixed> $data */
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

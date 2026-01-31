<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;

final readonly class DeliveryCancellationEvent implements WebhookEventInterface
{
    public function __construct(
        public string $eventTime,
        public WebhookDelivery $delivery,
        public WebhookOrder $order,
    ) {
    }

    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::DeliveryCancellation;
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
        );
    }
}

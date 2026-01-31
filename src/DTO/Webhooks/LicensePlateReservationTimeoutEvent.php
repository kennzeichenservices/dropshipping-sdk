<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\DTO\Requests\LicensePlateReservationCustomization;
use Dropshipping\Enums\WebhookEventType;

final readonly class LicensePlateReservationTimeoutEvent implements WebhookEventInterface
{
    public function __construct(
        public string $eventTime,
        public WebhookOrder $order,
        public LicensePlateReservationCustomization $customization,
    ) {
    }

    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::LicensePlateReservationTimeout;
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
            order: WebhookOrder::fromArray($data['order']),
            customization: LicensePlateReservationCustomization::fromArray($data['customization']),
        );
    }
}

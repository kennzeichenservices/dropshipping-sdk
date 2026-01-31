<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;

final readonly class PingEvent implements WebhookEventInterface
{
    public function __construct(
        public string $eventTime,
    ) {
    }

    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::Ping;
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
        );
    }
}

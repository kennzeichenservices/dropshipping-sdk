<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;

/**
 * Webhook event fired to verify webhook endpoint connectivity.
 *
 * This event is used as a health check to confirm that
 * the webhook consumer is reachable and operational.
 */
final readonly class PingEvent implements WebhookEventInterface
{
    /**
     * @param string $eventTime The ISO 8601 timestamp when the ping was issued.
     */
    public function __construct(
        public string $eventTime,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::Ping;
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
        );
    }
}

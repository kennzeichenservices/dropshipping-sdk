<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;

/**
 * Fallback event for webhook payloads whose {@code eventType} this SDK version
 * does not know yet.
 *
 * Only produced when unknown event types are explicitly tolerated (see
 * {@see WebhookEventFactory::fromArray()}); the default remains to throw.
 * The original event type string and the complete decoded payload are
 * preserved so consumers can inspect or forward the event.
 *
 * This exists because the API can start sending an event type before this SDK
 * models it — a spec revision and an SDK release are separate events in time.
 * Tolerating unknown types keeps such payloads deliverable until the typed
 * event ships, at which point this fallback stops matching them.
 */
final readonly class UnknownWebhookEvent implements WebhookEventInterface
{
    /**
     * @param string               $rawEventType The eventType string as sent by the API.
     * @param string               $eventTime    The ISO 8601 timestamp when the event occurred.
     * @param array<string, mixed> $payload      The complete decoded webhook payload.
     */
    public function __construct(
        public string $rawEventType,
        public string $eventTime,
        public array $payload,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * Always {@see WebhookEventType::Unknown}. Use {@see $rawEventType} for the
     * value the API actually sent.
     */
    public function getEventType(): WebhookEventType
    {
        return WebhookEventType::Unknown;
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
            rawEventType: (string) ($data['eventType'] ?? ''),
            eventTime: (string) ($data['eventTime'] ?? ''),
            payload: $data,
        );
    }
}

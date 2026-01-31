<?php

declare(strict_types=1);

namespace Dropshipping\Webhook;

use Dropshipping\DTO\Webhooks\WebhookEventInterface;

final class WebhookMessage
{
    private ?WebhookEventInterface $event = null;

    public function __construct(
        private readonly string $payload,
        private readonly string $signature,
        private readonly int $webhookId,
        private readonly string $webhookVersion,
    ) {
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function getWebhookId(): int
    {
        return $this->webhookId;
    }

    public function getWebhookVersion(): string
    {
        return $this->webhookVersion;
    }

    public function getEvent(): ?WebhookEventInterface
    {
        return $this->event;
    }

    public function withEvent(WebhookEventInterface $event): self
    {
        $clone = clone $this;
        $clone->event = $event;

        return $clone;
    }
}

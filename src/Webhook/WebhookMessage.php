<?php

declare(strict_types=1);

namespace Dropshipping\Webhook;

use Dropshipping\DTO\Webhooks\WebhookEventInterface;

/**
 * Immutable value object representing an incoming webhook message.
 *
 * Contains the raw payload, signature, webhook ID and version.
 * A deserialized event can be attached via {@see withEvent()}, which
 * returns a new instance to preserve immutability.
 */
final class WebhookMessage
{
    private ?WebhookEventInterface $event = null;

    /**
     * @param string $payload        Raw webhook payload body.
     * @param string $signature      HMAC signature supplied with the request.
     * @param int    $webhookId      Unique identifier of the webhook.
     * @param string $webhookVersion Version of the webhook specification.
     */
    public function __construct(
        private readonly string $payload,
        private readonly string $signature,
        private readonly int $webhookId,
        private readonly string $webhookVersion,
    ) {
    }

    /**
     * Return the raw webhook payload body.
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * Return the HMAC signature supplied with the webhook request.
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * Return the unique identifier of the webhook.
     */
    public function getWebhookId(): int
    {
        return $this->webhookId;
    }

    /**
     * Return the version of the webhook specification.
     */
    public function getWebhookVersion(): string
    {
        return $this->webhookVersion;
    }

    /**
     * Return the deserialized webhook event, or null if not yet attached.
     */
    public function getEvent(): ?WebhookEventInterface
    {
        return $this->event;
    }

    /**
     * Return a new instance with the given event attached.
     *
     * @param WebhookEventInterface $event The deserialized webhook event.
     *
     * @return self A cloned message carrying the event.
     */
    public function withEvent(WebhookEventInterface $event): self
    {
        $clone = clone $this;
        $clone->event = $event;

        return $clone;
    }
}

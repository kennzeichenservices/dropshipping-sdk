<?php

declare(strict_types=1);

namespace Dropshipping\Webhook\Middleware;

use Dropshipping\Contracts\SerializerInterface;
use Dropshipping\DTO\Webhooks\WebhookEventFactory;
use Dropshipping\Webhook\WebhookMessage;

/**
 * Middleware that deserializes the raw webhook payload into a typed event.
 *
 * Uses the {@see SerializerInterface} to decode the JSON payload and
 * the {@see WebhookEventFactory} to create the corresponding
 * {@see \Dropshipping\DTO\Webhooks\WebhookEventInterface} instance.
 */
final class DeserializationMiddleware implements WebhookMiddlewareInterface
{
    /**
     * @param SerializerInterface $serializer Serializer used to decode the raw payload.
     */
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function process(WebhookMessage $message, callable $next): WebhookMessage
    {
        $data = $this->serializer->decode($message->getPayload());
        $event = WebhookEventFactory::fromArray($data);
        $message = $message->withEvent($event);

        return $next($message);
    }
}

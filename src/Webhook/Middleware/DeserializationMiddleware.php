<?php

declare(strict_types=1);

namespace Dropshipping\Webhook\Middleware;

use Dropshipping\Contracts\SerializerInterface;
use Dropshipping\DTO\Webhooks\WebhookEventFactory;
use Dropshipping\Webhook\WebhookMessage;

final class DeserializationMiddleware implements WebhookMiddlewareInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function process(WebhookMessage $message, callable $next): WebhookMessage
    {
        $data = $this->serializer->decode($message->getPayload());
        $event = WebhookEventFactory::fromArray($data);
        $message = $message->withEvent($event);

        return $next($message);
    }
}

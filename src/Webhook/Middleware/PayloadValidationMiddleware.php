<?php

declare(strict_types=1);

namespace Dropshipping\Webhook\Middleware;

use Dropshipping\Contracts\SerializerInterface;
use Dropshipping\Exceptions\WebhookException;
use Dropshipping\Webhook\WebhookMessage;

final class PayloadValidationMiddleware implements WebhookMiddlewareInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function process(WebhookMessage $message, callable $next): WebhookMessage
    {
        $payload = $message->getPayload();

        if ($payload === '') {
            throw new WebhookException('Webhook payload must not be empty');
        }

        $data = $this->serializer->decode($payload);

        if (!isset($data['eventType'])) {
            throw new WebhookException('Webhook payload must contain eventType');
        }

        if (!isset($data['eventTime'])) {
            throw new WebhookException('Webhook payload must contain eventTime');
        }

        return $next($message);
    }
}

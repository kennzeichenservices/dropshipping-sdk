<?php

declare(strict_types=1);

namespace Dropshipping\Webhook\Middleware;

use Dropshipping\Contracts\SerializerInterface;
use Dropshipping\Exceptions\WebhookException;
use Dropshipping\Webhook\WebhookMessage;

/**
 * Middleware that validates the structure of a webhook payload.
 *
 * Ensures the payload is non-empty and contains the required fields
 * {@code eventType} and {@code eventTime}. Throws a
 * {@see WebhookException} when validation fails.
 */
final class PayloadValidationMiddleware implements WebhookMiddlewareInterface
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
     *
     * @throws WebhookException If the payload is empty or missing required fields.
     */
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

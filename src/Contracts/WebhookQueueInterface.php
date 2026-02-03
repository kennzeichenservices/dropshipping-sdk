<?php

declare(strict_types=1);

namespace Dropshipping\Contracts;

use Dropshipping\Webhook\WebhookMessage;

/**
 * Contract for a webhook message queue used in asynchronous processing.
 */
interface WebhookQueueInterface
{
    /**
     * Add a webhook message to the queue.
     *
     * @param WebhookMessage $message The message to enqueue.
     */
    public function push(WebhookMessage $message): void;

    /**
     * Retrieve and remove the next webhook message from the queue.
     *
     * @return WebhookMessage|null The next message, or null if the queue is empty.
     */
    public function pop(): ?WebhookMessage;
}

<?php

declare(strict_types=1);

namespace Dropshipping\Async;

use Dropshipping\Contracts\WebhookQueueInterface;
use Dropshipping\Webhook\WebhookMessage;

/**
 * Asynchronous webhook dispatcher that enqueues messages for later processing.
 *
 * Instead of processing a webhook message immediately, this dispatcher
 * pushes it onto a {@see WebhookQueueInterface} queue so that a
 * {@see \Dropshipping\Async\WebhookWorker} can handle it asynchronously.
 */
final class QueueWebhookDispatcher
{
    /**
     * @param WebhookQueueInterface $queue Queue implementation to push messages onto.
     */
    public function __construct(
        private readonly WebhookQueueInterface $queue,
    ) {
    }

    /**
     * Enqueue a webhook message for asynchronous processing.
     *
     * @param WebhookMessage $message The webhook message to enqueue.
     */
    public function dispatch(WebhookMessage $message): void
    {
        $this->queue->push($message);
    }
}

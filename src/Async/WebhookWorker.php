<?php

declare(strict_types=1);

namespace Dropshipping\Async;

use Dropshipping\Contracts\WebhookQueueInterface;
use Dropshipping\Webhook\WebhookDispatcher;

/**
 * Worker that consumes and dispatches webhook messages from a queue.
 *
 * Messages are popped from the {@see WebhookQueueInterface} and forwarded
 * to the {@see WebhookDispatcher} for synchronous processing.
 */
final class WebhookWorker
{
    /**
     * @param WebhookQueueInterface $queue      Queue to consume messages from.
     * @param WebhookDispatcher     $dispatcher Dispatcher that processes each message.
     */
    public function __construct(
        private readonly WebhookQueueInterface $queue,
        private readonly WebhookDispatcher $dispatcher,
    ) {
    }

    /**
     * Pop and dispatch the next message from the queue.
     *
     * @return bool True if a message was processed, false if the queue was empty.
     */
    public function processNext(): bool
    {
        $message = $this->queue->pop();

        if ($message === null) {
            return false;
        }

        $this->dispatcher->dispatch($message);

        return true;
    }

    /**
     * Process messages in a loop until the queue is empty or the limit is reached.
     *
     * @param int $maxMessages Maximum number of messages to process (0 = unlimited).
     *
     * @return int The total number of messages processed.
     */
    public function run(int $maxMessages = 0): int
    {
        $processed = 0;

        while (true) {
            if ($maxMessages > 0 && $processed >= $maxMessages) {
                break;
            }

            if (!$this->processNext()) {
                break;
            }

            $processed++;
        }

        return $processed;
    }
}

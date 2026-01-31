<?php

declare(strict_types=1);

namespace Dropshipping\Async;

use Dropshipping\Contracts\WebhookQueueInterface;
use Dropshipping\Exceptions\WebhookException;
use Dropshipping\Webhook\WebhookDispatcher;

final class WebhookWorker
{
    public function __construct(
        private readonly WebhookQueueInterface $queue,
        private readonly WebhookDispatcher $dispatcher,
    ) {
    }

    public function processNext(): bool
    {
        $message = $this->queue->pop();

        if ($message === null) {
            return false;
        }

        $this->dispatcher->dispatch($message);

        return true;
    }

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

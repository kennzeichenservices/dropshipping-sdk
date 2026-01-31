<?php

declare(strict_types=1);

namespace Dropshipping\Async;

use Dropshipping\Contracts\WebhookQueueInterface;
use Dropshipping\Webhook\WebhookMessage;

final class QueueWebhookDispatcher
{
    public function __construct(
        private readonly WebhookQueueInterface $queue,
    ) {
    }

    public function dispatch(WebhookMessage $message): void
    {
        $this->queue->push($message);
    }
}

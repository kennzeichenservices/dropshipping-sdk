<?php

declare(strict_types=1);

namespace Dropshipping\Contracts;

use Dropshipping\Webhook\WebhookMessage;

interface WebhookQueueInterface
{
    public function push(WebhookMessage $message): void;

    public function pop(): ?WebhookMessage;
}

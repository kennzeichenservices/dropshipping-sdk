<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;

interface WebhookEventInterface
{
    public function getEventType(): WebhookEventType;

    public function getEventTime(): string;
}

<?php

declare(strict_types=1);

namespace Dropshipping\Contracts;

use Dropshipping\DTO\Webhooks\WebhookEventInterface;

interface WebhookHandlerInterface
{
    public function handle(WebhookEventInterface $event): void;

    public function supports(WebhookEventInterface $event): bool;
}

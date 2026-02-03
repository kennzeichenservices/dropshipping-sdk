<?php

declare(strict_types=1);

namespace Dropshipping\Contracts;

use Dropshipping\DTO\Webhooks\WebhookEventInterface;

/**
 * Contract for handling webhook events.
 */
interface WebhookHandlerInterface
{
    /**
     * Process the given webhook event.
     *
     * @param WebhookEventInterface $event The webhook event to process.
     */
    public function handle(WebhookEventInterface $event): void;

    /**
     * Determine whether this handler can process the given webhook event.
     *
     * @param WebhookEventInterface $event The webhook event to evaluate.
     *
     * @return bool True if this handler supports the event, false otherwise.
     */
    public function supports(WebhookEventInterface $event): bool;
}

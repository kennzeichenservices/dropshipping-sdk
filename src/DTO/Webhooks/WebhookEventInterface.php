<?php

declare(strict_types=1);

namespace Dropshipping\DTO\Webhooks;

use Dropshipping\Enums\WebhookEventType;

/**
 * Interface for all webhook event DTOs.
 *
 * Defines the contracts that every webhook event must fulfill,
 * providing access to the event type and the time the event occurred.
 */
interface WebhookEventInterface
{
    /**
     * Get the type of the webhook event.
     *
     * @return WebhookEventType
     */
    public function getEventType(): WebhookEventType;

    /**
     * Get the ISO 8601 timestamp when the event occurred.
     *
     * @return string
     */
    public function getEventTime(): string;
}

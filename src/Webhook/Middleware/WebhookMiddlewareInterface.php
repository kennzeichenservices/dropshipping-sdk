<?php

declare(strict_types=1);

namespace Dropshipping\Webhook\Middleware;

use Dropshipping\Webhook\WebhookMessage;

/**
 * Interface for webhook processing middleware.
 *
 * Implementations inspect or transform a {@see WebhookMessage} and then
 * delegate to the next middleware in the pipeline via the provided callable.
 */
interface WebhookMiddlewareInterface
{
    /**
     * Process the webhook message and pass it to the next middleware.
     *
     * @param WebhookMessage $message The incoming webhook message.
     * @param callable       $next    The next middleware in the pipeline.
     *
     * @return WebhookMessage The processed webhook message.
     */
    public function process(WebhookMessage $message, callable $next): WebhookMessage;
}

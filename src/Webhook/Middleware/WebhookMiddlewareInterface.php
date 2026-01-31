<?php

declare(strict_types=1);

namespace Dropshipping\Webhook\Middleware;

use Dropshipping\Webhook\WebhookMessage;

interface WebhookMiddlewareInterface
{
    public function process(WebhookMessage $message, callable $next): WebhookMessage;
}

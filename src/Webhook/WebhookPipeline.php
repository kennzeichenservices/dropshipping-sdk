<?php

declare(strict_types=1);

namespace Dropshipping\Webhook;

use Dropshipping\Webhook\Middleware\WebhookMiddlewareInterface;

final class WebhookPipeline
{
    /** @var list<WebhookMiddlewareInterface> */
    private array $middleware = [];

    public function pipe(WebhookMiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    public function process(WebhookMessage $message): WebhookMessage
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            static fn (callable $next, WebhookMiddlewareInterface $middleware): callable =>
                static fn (WebhookMessage $msg): WebhookMessage => $middleware->process($msg, $next),
            static fn (WebhookMessage $msg): WebhookMessage => $msg,
        );

        return $pipeline($message);
    }
}

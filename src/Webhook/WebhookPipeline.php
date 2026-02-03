<?php

declare(strict_types=1);

namespace Dropshipping\Webhook;

use Dropshipping\Webhook\Middleware\WebhookMiddlewareInterface;

/**
 * Processes webhook messages through a chain of middleware.
 *
 * Middleware instances are executed in the order they are added via
 * {@see pipe()}. The pipeline uses a functional reduce to compose the
 * middleware stack.
 */
final class WebhookPipeline
{
    /** @var list<WebhookMiddlewareInterface> */
    private array $middleware = [];

    /**
     * Add a middleware to the end of the pipeline.
     *
     * @param WebhookMiddlewareInterface $middleware The middleware to append.
     *
     * @return self Fluent interface.
     */
    public function pipe(WebhookMiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Run the message through all registered middleware in order.
     *
     * @param WebhookMessage $message The incoming webhook message.
     *
     * @return WebhookMessage The message after passing through the pipeline.
     */
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

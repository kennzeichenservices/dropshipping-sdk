<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Webhook;

use Dropshipping\Contracts\WebhookHandlerInterface;
use Dropshipping\DTO\Webhooks\PingEvent;
use Dropshipping\DTO\Webhooks\WebhookEventInterface;
use Dropshipping\Exceptions\WebhookException;
use Dropshipping\Webhook\Middleware\WebhookMiddlewareInterface;
use Dropshipping\Webhook\WebhookDispatcher;
use Dropshipping\Webhook\WebhookMessage;
use Dropshipping\Webhook\WebhookPipeline;
use PHPUnit\Framework\TestCase;

final class WebhookDispatcherTest extends TestCase
{
    public function test_dispatch_calls_matching_handlers(): void
    {
        $event = new PingEvent('2024-01-01T00:00:00Z');

        $pipeline = new WebhookPipeline();
        $pipeline->pipe($this->createEventMiddleware($event));

        $spy = new HandlerSpy();
        $handler = new class ($spy) implements WebhookHandlerInterface {
            public function __construct(private readonly HandlerSpy $spy)
            {
            }
            public function handle(WebhookEventInterface $event): void
            {
                $this->spy->handled = true;
            }
            public function supports(WebhookEventInterface $event): bool
            {
                return true;
            }
        };

        $dispatcher = new WebhookDispatcher($pipeline);
        $dispatcher->registerHandler($handler);
        $dispatcher->dispatch(new WebhookMessage('p', 's', 1, '1.0'));

        self::assertTrue($spy->handled);
    }

    public function test_dispatch_skips_non_matching_handlers(): void
    {
        $event = new PingEvent('2024-01-01T00:00:00Z');

        $pipeline = new WebhookPipeline();
        $pipeline->pipe($this->createEventMiddleware($event));

        $spy = new HandlerSpy();
        $handler = new class ($spy) implements WebhookHandlerInterface {
            public function __construct(private readonly HandlerSpy $spy)
            {
            }
            public function handle(WebhookEventInterface $event): void
            {
                $this->spy->handled = true;
            }
            public function supports(WebhookEventInterface $event): bool
            {
                return false;
            }
        };

        $dispatcher = new WebhookDispatcher($pipeline);
        $dispatcher->registerHandler($handler);
        $dispatcher->dispatch(new WebhookMessage('p', 's', 1, '1.0'));

        self::assertFalse($spy->handled);
    }

    public function test_dispatch_throws_when_no_event_deserialized(): void
    {
        $pipeline = new WebhookPipeline();
        $dispatcher = new WebhookDispatcher($pipeline);

        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('No event was deserialized');

        $dispatcher->dispatch(new WebhookMessage('p', 's', 1, '1.0'));
    }

    private function createEventMiddleware(WebhookEventInterface $event): WebhookMiddlewareInterface
    {
        return new class ($event) implements WebhookMiddlewareInterface {
            public function __construct(private WebhookEventInterface $event)
            {
            }
            public function process(WebhookMessage $message, callable $next): WebhookMessage
            {
                return $next($message->withEvent($this->event));
            }
        };
    }
}

/**
 * Mutable flag shared with a handler so the test can observe whether it ran.
 */
final class HandlerSpy
{
    public bool $handled = false;
}

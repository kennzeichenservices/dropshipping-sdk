<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Webhook;

use Dropshipping\DTO\Webhooks\PingEvent;
use Dropshipping\Webhook\Middleware\WebhookMiddlewareInterface;
use Dropshipping\Webhook\WebhookMessage;
use Dropshipping\Webhook\WebhookPipeline;
use PHPUnit\Framework\TestCase;

final class WebhookPipelineTest extends TestCase
{
    public function test_process_without_middleware_returns_message_unchanged(): void
    {
        $pipeline = new WebhookPipeline();
        $message = new WebhookMessage('payload', 'sig', 1, '1.0');

        $result = $pipeline->process($message);

        self::assertSame($message, $result);
    }

    public function test_process_executes_middleware_in_order(): void
    {
        $log = [];

        $first = new class ($log) implements WebhookMiddlewareInterface {
            public function __construct(private array &$log) {}
            public function process(WebhookMessage $message, callable $next): WebhookMessage
            {
                $this->log[] = 'first';
                return $next($message);
            }
        };

        $second = new class ($log) implements WebhookMiddlewareInterface {
            public function __construct(private array &$log) {}
            public function process(WebhookMessage $message, callable $next): WebhookMessage
            {
                $this->log[] = 'second';
                return $next($message);
            }
        };

        $pipeline = new WebhookPipeline();
        $pipeline->pipe($first)->pipe($second);

        $pipeline->process(new WebhookMessage('p', 's', 1, '1.0'));

        self::assertSame(['first', 'second'], $log);
    }

    public function test_middleware_can_modify_message(): void
    {
        $middleware = new class implements WebhookMiddlewareInterface {
            public function process(WebhookMessage $message, callable $next): WebhookMessage
            {
                $event = new PingEvent('2024-01-01T00:00:00Z');
                return $next($message->withEvent($event));
            }
        };

        $pipeline = new WebhookPipeline();
        $pipeline->pipe($middleware);

        $result = $pipeline->process(new WebhookMessage('p', 's', 1, '1.0'));

        self::assertNotNull($result->getEvent());
    }
}

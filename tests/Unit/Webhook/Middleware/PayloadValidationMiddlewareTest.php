<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Webhook\Middleware;

use Dropshipping\Exceptions\WebhookException;
use Dropshipping\Serialization\ArrayMapper;
use Dropshipping\Webhook\Middleware\PayloadValidationMiddleware;
use Dropshipping\Webhook\WebhookMessage;
use PHPUnit\Framework\TestCase;

final class PayloadValidationMiddlewareTest extends TestCase
{
    private PayloadValidationMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new PayloadValidationMiddleware(new ArrayMapper());
    }

    public function test_process_passes_valid_payload(): void
    {
        $payload = json_encode(['eventType' => 'PING', 'eventTime' => '2024-01-01T00:00:00Z']);
        $message = new WebhookMessage($payload, 'sig', 1, '3.2.0');

        $nextCalled = false;
        $this->middleware->process($message, function (WebhookMessage $msg) use (&$nextCalled) {
            $nextCalled = true;
            return $msg;
        });

        self::assertTrue($nextCalled);
    }

    public function test_process_throws_on_empty_payload(): void
    {
        $message = new WebhookMessage('', 'sig', 1, '3.2.0');

        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('must not be empty');

        $this->middleware->process($message, fn ($msg) => $msg);
    }

    public function test_process_throws_when_eventType_missing(): void
    {
        $payload = json_encode(['eventTime' => '2024-01-01T00:00:00Z']);
        $message = new WebhookMessage($payload, 'sig', 1, '3.2.0');

        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('eventType');

        $this->middleware->process($message, fn ($msg) => $msg);
    }

    public function test_process_throws_when_eventTime_missing(): void
    {
        $payload = json_encode(['eventType' => 'PING']);
        $message = new WebhookMessage($payload, 'sig', 1, '3.2.0');

        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('eventTime');

        $this->middleware->process($message, fn ($msg) => $msg);
    }
}

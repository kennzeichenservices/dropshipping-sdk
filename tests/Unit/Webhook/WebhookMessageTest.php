<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Webhook;

use Dropshipping\DTO\Webhooks\PingEvent;
use Dropshipping\Webhook\WebhookMessage;
use PHPUnit\Framework\TestCase;

final class WebhookMessageTest extends TestCase
{
    public function test_getters_return_constructor_values(): void
    {
        $message = new WebhookMessage('payload', 'sig', 42, '1.0');

        self::assertSame('payload', $message->getPayload());
        self::assertSame('sig', $message->getSignature());
        self::assertSame(42, $message->getWebhookId());
        self::assertSame('1.0', $message->getWebhookVersion());
    }

    public function test_getEvent_returns_null_initially(): void
    {
        $message = new WebhookMessage('payload', 'sig', 1, '1.0');

        self::assertNull($message->getEvent());
    }

    public function test_withEvent_returns_new_instance_with_event(): void
    {
        $message = new WebhookMessage('payload', 'sig', 1, '1.0');
        $event = new PingEvent('2024-01-01T00:00:00Z');

        $newMessage = $message->withEvent($event);

        self::assertNull($message->getEvent());
        self::assertSame($event, $newMessage->getEvent());
        self::assertNotSame($message, $newMessage);
    }
}

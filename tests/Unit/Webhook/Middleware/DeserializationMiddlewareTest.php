<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Webhook\Middleware;

use Dropshipping\DTO\Webhooks\PingEvent;
use Dropshipping\DTO\Webhooks\UnknownWebhookEvent;
use Dropshipping\Enums\WebhookEventType;
use Dropshipping\Exceptions\WebhookException;
use Dropshipping\Serialization\ArrayMapper;
use Dropshipping\Webhook\Middleware\DeserializationMiddleware;
use Dropshipping\Webhook\WebhookMessage;
use PHPUnit\Framework\TestCase;

final class DeserializationMiddlewareTest extends TestCase
{
    private DeserializationMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new DeserializationMiddleware(new ArrayMapper());
    }

    public function test_process_deserializes_ping_event(): void
    {
        $payload = json_encode(['eventType' => 'PING', 'eventTime' => '2024-01-01T00:00:00Z']);
        $message = new WebhookMessage($payload, 'sig', 1, '3.2.0');

        $result = $this->middleware->process($message, fn ($msg) => $msg);

        self::assertInstanceOf(PingEvent::class, $result->getEvent());
        self::assertSame(WebhookEventType::Ping, $result->getEvent()->getEventType());
    }

    public function test_process_deserializes_delivery_shipment_event(): void
    {
        $payload = json_encode([
            'eventType' => 'DELIVERY_SHIPMENT',
            'eventTime' => '2024-01-01T00:00:00Z',
            'delivery' => ['id' => 1, 'trackingCode' => 'TRACK123'],
            'order' => ['id' => 10, 'externalId' => 'ext-1'],
        ]);
        $message = new WebhookMessage($payload, 'sig', 1, '3.2.0');

        $result = $this->middleware->process($message, fn ($msg) => $msg);

        self::assertSame(WebhookEventType::DeliveryShipment, $result->getEvent()->getEventType());
    }

    public function test_process_throws_on_unknown_event_by_default(): void
    {
        $payload = json_encode(['eventType' => 'SOME_FUTURE_EVENT', 'eventTime' => '2024-01-01T00:00:00Z']);
        $message = new WebhookMessage($payload, 'sig', 1, '3.2.0');

        $this->expectException(WebhookException::class);

        $this->middleware->process($message, fn ($msg) => $msg);
    }

    public function test_process_yields_unknown_event_when_tolerance_enabled(): void
    {
        $middleware = new DeserializationMiddleware(new ArrayMapper(), tolerateUnknownEvents: true);
        $payload = json_encode(['eventType' => 'SOME_FUTURE_EVENT', 'eventTime' => '2024-01-01T00:00:00Z']);
        $message = new WebhookMessage($payload, 'sig', 1, '3.2.0');

        $result = $middleware->process($message, fn ($msg) => $msg);
        $event = $result->getEvent();

        self::assertInstanceOf(UnknownWebhookEvent::class, $event);
        self::assertSame('SOME_FUTURE_EVENT', $event->rawEventType);
    }
}

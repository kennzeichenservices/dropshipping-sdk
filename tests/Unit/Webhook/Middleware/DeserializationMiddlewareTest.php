<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Webhook\Middleware;

use Dropshipping\DTO\Webhooks\PingEvent;
use Dropshipping\Enums\WebhookEventType;
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
        $message = new WebhookMessage($payload, 'sig', 1, '1.0');

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
        $message = new WebhookMessage($payload, 'sig', 1, '1.0');

        $result = $this->middleware->process($message, fn ($msg) => $msg);

        self::assertSame(WebhookEventType::DeliveryShipment, $result->getEvent()->getEventType());
    }
}

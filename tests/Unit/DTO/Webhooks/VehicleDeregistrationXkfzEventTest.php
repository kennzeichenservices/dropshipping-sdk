<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Webhooks;

use Dropshipping\DTO\Webhooks\VehicleDeregistrationCostBreakdown;
use Dropshipping\DTO\Webhooks\VehicleDeregistrationCostBreakdownItem;
use Dropshipping\DTO\Webhooks\VehicleDeregistrationRegistrationOfficeCosts;
use Dropshipping\DTO\Webhooks\VehicleDeregistrationXkfzEvent;
use Dropshipping\Enums\WebhookEventType;
use PHPUnit\Framework\TestCase;

final class VehicleDeregistrationXkfzEventTest extends TestCase
{
    public function test_fromArray_with_full_cost_breakdown(): void
    {
        $event = VehicleDeregistrationXkfzEvent::fromArray([
            'eventTime' => '2024-06-15T10:30:00Z',
            'order' => ['id' => 42, 'externalId' => 'ext-42'],
            'costBreakdown' => [
                'kbaCost' => 350,
                'registrationOfficeCosts' => [
                    'items' => [
                        ['number' => 1, 'code' => 'FEE', 'name' => 'Service fee', 'amount' => 200, 'note' => 'Some note'],
                        ['number' => 2, 'code' => null, 'name' => 'Admin', 'amount' => 150, 'note' => null],
                    ],
                    'total' => ['number' => 0, 'code' => null, 'name' => 'Total', 'amount' => 350, 'note' => null],
                    'note' => 'Office note',
                ],
            ],
        ]);

        self::assertSame(WebhookEventType::VehicleDeregistrationXkfzEvent, $event->getEventType());
        self::assertSame('2024-06-15T10:30:00Z', $event->getEventTime());
        self::assertSame(42, $event->order->id);
        self::assertSame('ext-42', $event->order->externalId);

        self::assertNotNull($event->costBreakdown);
        self::assertSame(350, $event->costBreakdown->kbaCost);

        $officeCosts = $event->costBreakdown->registrationOfficeCosts;
        self::assertNotNull($officeCosts);
        self::assertCount(2, $officeCosts->items);
        self::assertSame(1, $officeCosts->items[0]->number);
        self::assertSame('FEE', $officeCosts->items[0]->code);
        self::assertSame('Service fee', $officeCosts->items[0]->name);
        self::assertSame(200, $officeCosts->items[0]->amount);
        self::assertSame('Some note', $officeCosts->items[0]->note);
        self::assertSame(350, $officeCosts->total->amount);
        self::assertSame('Office note', $officeCosts->note);
    }

    public function test_fromArray_without_cost_breakdown(): void
    {
        $event = VehicleDeregistrationXkfzEvent::fromArray([
            'eventTime' => '2024-06-15T10:30:00Z',
            'order' => ['id' => 42],
        ]);

        self::assertSame(WebhookEventType::VehicleDeregistrationXkfzEvent, $event->getEventType());
        self::assertSame(42, $event->order->id);
        self::assertNull($event->order->externalId);
        self::assertNull($event->costBreakdown);
    }

    public function test_fromArray_with_partial_cost_breakdown(): void
    {
        $event = VehicleDeregistrationXkfzEvent::fromArray([
            'eventTime' => '2024-06-15T10:30:00Z',
            'order' => ['id' => 42],
            'costBreakdown' => [
                'kbaCost' => 500,
            ],
        ]);

        self::assertNotNull($event->costBreakdown);
        self::assertSame(500, $event->costBreakdown->kbaCost);
        self::assertNull($event->costBreakdown->registrationOfficeCosts);
    }

    public function test_cost_breakdown_item_fromArray(): void
    {
        $item = VehicleDeregistrationCostBreakdownItem::fromArray([
            'number' => 1,
            'code' => 'ABC',
            'name' => 'Test item',
            'amount' => 100,
            'note' => 'A note',
        ]);

        self::assertSame(1, $item->number);
        self::assertSame('ABC', $item->code);
        self::assertSame('Test item', $item->name);
        self::assertSame(100, $item->amount);
        self::assertSame('A note', $item->note);
    }

    public function test_cost_breakdown_item_fromArray_with_nulls(): void
    {
        $item = VehicleDeregistrationCostBreakdownItem::fromArray([
            'number' => 1,
            'amount' => 100,
        ]);

        self::assertNull($item->code);
        self::assertNull($item->name);
        self::assertNull($item->note);
    }

    public function test_registration_office_costs_fromArray_without_total(): void
    {
        $costs = VehicleDeregistrationRegistrationOfficeCosts::fromArray([
            'items' => [
                ['number' => 1, 'amount' => 100],
            ],
        ]);

        self::assertCount(1, $costs->items);
        self::assertNull($costs->total);
        self::assertNull($costs->note);
    }

    public function test_cost_breakdown_fromArray_with_all_nulls(): void
    {
        $breakdown = VehicleDeregistrationCostBreakdown::fromArray([]);

        self::assertNull($breakdown->kbaCost);
        self::assertNull($breakdown->registrationOfficeCosts);
    }
}

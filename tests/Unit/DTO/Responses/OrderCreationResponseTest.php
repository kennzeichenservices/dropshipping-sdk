<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Responses;

use Dropshipping\DTO\Responses\Delivery;
use Dropshipping\DTO\Responses\DeliveryItem;
use Dropshipping\DTO\Responses\EmissionStickerOrderResponse;
use Dropshipping\DTO\Responses\LicensePlateReservationResponse;
use Dropshipping\DTO\Responses\OrderCreationResponse;
use PHPUnit\Framework\TestCase;

final class OrderCreationResponseTest extends TestCase
{
    public function test_fromArray_parses_id_and_deliveries(): void
    {
        $data = [
            'id' => 42,
            'deliveries' => [
                [
                    'id' => 1,
                    'items' => [
                        ['orderItemIndex' => 0],
                        ['orderItemIndex' => 1],
                    ],
                ],
            ],
            'costNetValue' => '1.23',
        ];

        $response = OrderCreationResponse::fromArray($data);

        self::assertSame(42, $response->id);
        self::assertCount(1, $response->deliveries);
        self::assertSame(1, $response->deliveries[0]->id);
        self::assertCount(2, $response->deliveries[0]->items);
        self::assertSame(0, $response->deliveries[0]->items[0]->orderItemIndex);
        self::assertSame('1.23', $response->costNetValue);
    }

    public function test_fromArray_handles_empty_deliveries(): void
    {
        $response = OrderCreationResponse::fromArray(['id' => 1, 'costNetValue' => '0.00']);

        self::assertSame(1, $response->id);
        self::assertCount(0, $response->deliveries);
        self::assertSame('0.00', $response->costNetValue);
    }

    public function test_delivery_fromArray(): void
    {
        $delivery = Delivery::fromArray(['id' => 5, 'items' => [['orderItemIndex' => 0]]]);

        self::assertSame(5, $delivery->id);
        self::assertCount(1, $delivery->items);
    }

    public function test_delivery_item_fromArray(): void
    {
        $item = DeliveryItem::fromArray(['orderItemIndex' => 3]);

        self::assertSame(3, $item->orderItemIndex);
    }

    public function test_emission_sticker_response_fromArray(): void
    {
        $response = EmissionStickerOrderResponse::fromArray([
            'id' => 10,
            'delivery' => ['id' => 20],
            'costNetValue' => '1.23',
        ]);

        self::assertSame(10, $response->id);
        self::assertSame(20, $response->deliveryId);
        self::assertSame('1.23', $response->costNetValue);
    }

    public function test_license_plate_reservation_response_fromArray(): void
    {
        $response = LicensePlateReservationResponse::fromArray([
            'order' => ['id' => 55, 'costNetValue' => '1.23'],
        ]);

        self::assertSame(55, $response->orderId);
        self::assertSame('1.23', $response->costNetValue);
    }
}

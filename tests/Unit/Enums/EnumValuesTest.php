<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\Enums;

use Dropshipping\Enums\Gender;
use Dropshipping\Enums\LicensePlateType;
use Dropshipping\Enums\LicensePlateUsageType;
use Dropshipping\Enums\ProductType;
use Dropshipping\Enums\VehicleType;
use Dropshipping\Enums\WebhookEventType;
use PHPUnit\Framework\TestCase;

final class EnumValuesTest extends TestCase
{
    public function test_gender_values(): void
    {
        self::assertSame('FEMALE', Gender::Female->value);
        self::assertSame('MALE', Gender::Male->value);
        self::assertSame('UNSPECIFIED', Gender::Unspecified->value);
        self::assertSame(Gender::Male, Gender::from('MALE'));
        self::assertNull(Gender::tryFrom('INVALID'));
    }

    public function test_vehicle_type_values(): void
    {
        self::assertSame('CAR', VehicleType::Car->value);
        self::assertSame('MOTORCYCLE', VehicleType::Motorcycle->value);
        self::assertSame(VehicleType::Car, VehicleType::from('CAR'));
    }

    public function test_license_plate_type_values(): void
    {
        self::assertSame('REGULAR', LicensePlateType::Regular->value);
        self::assertSame('REGULAR_SEASON', LicensePlateType::RegularSeason->value);
        self::assertSame('ELECTRIC', LicensePlateType::Electric->value);
        self::assertSame('ELECTRIC_SEASON', LicensePlateType::ElectricSeason->value);
        self::assertSame('HISTORICAL', LicensePlateType::Historical->value);
        self::assertSame('HISTORICAL_SEASON', LicensePlateType::HistoricalSeason->value);
        self::assertCount(6, LicensePlateType::cases());
    }

    public function test_license_plate_usage_type_values(): void
    {
        self::assertSame('EURO', LicensePlateUsageType::Euro->value);
        self::assertSame('PARKING', LicensePlateUsageType::Parking->value);
    }

    public function test_product_type_values(): void
    {
        self::assertSame('LICENSE_PLATE', ProductType::LicensePlate->value);
        self::assertSame('OTHER', ProductType::Other->value);
    }

    public function test_webhook_event_type_values(): void
    {
        self::assertSame('PING', WebhookEventType::Ping->value);
        self::assertSame('DELIVERY_SHIPMENT', WebhookEventType::DeliveryShipment->value);
        self::assertSame('DELIVERY_RETURN', WebhookEventType::DeliveryReturn->value);
        self::assertSame('DELIVERY_CANCELLATION', WebhookEventType::DeliveryCancellation->value);
        self::assertSame('LICENSE_PLATE_RESERVATION_APPROVAL', WebhookEventType::LicensePlateReservationApproval->value);
        self::assertSame('LICENSE_PLATE_RESERVATION_REJECTION', WebhookEventType::LicensePlateReservationRejection->value);
        self::assertSame('LICENSE_PLATE_RESERVATION_TIMEOUT', WebhookEventType::LicensePlateReservationTimeout->value);
        self::assertCount(7, WebhookEventType::cases());
    }

    public function test_from_invalid_value_throws(): void
    {
        $this->expectException(\ValueError::class);
        Gender::from('INVALID');
    }
}

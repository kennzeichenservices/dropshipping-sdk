<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO;

use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\LicensePlateItemCustomization;
use Dropshipping\DTO\OrderItem;
use Dropshipping\DTO\OtherItemCustomization;
use PHPUnit\Framework\TestCase;

final class OrderItemTest extends TestCase
{
    public function test_toArray_with_license_plate_customization(): void
    {
        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');
        $customization = new LicensePlateItemCustomization($components);
        $item = new OrderItem(1, 'Kennzeichen', 'LP-001', 2, $customization);

        $array = $item->toArray();

        self::assertSame(1, $array['productVariantId']);
        self::assertSame('Kennzeichen', $array['name']);
        self::assertSame('LP-001', $array['sku']);
        self::assertSame(2, $array['quantity']);
        self::assertSame('LICENSE_PLATE', $array['customization']['productType']);
        self::assertSame('B', $array['customization']['licensePlateNumberComponents']['city']);
    }

    public function test_toArray_with_other_customization(): void
    {
        $item = new OrderItem(5, 'Other Product', 'OTHER-1', 1, new OtherItemCustomization());
        $array = $item->toArray();

        self::assertSame('OTHER', $array['customization']['productType']);
    }

    public function test_license_plate_customization_with_season(): void
    {
        $components = new EuroLicensePlateNumberComponents('M', 'XY', '99');
        $customization = new LicensePlateItemCustomization($components, seasonStartMonth: 3, seasonEndMonth: 10);
        $array = $customization->toArray();

        self::assertSame(3, $array['seasonStartMonth']);
        self::assertSame(10, $array['seasonEndMonth']);
    }

    public function test_license_plate_customization_excludes_null_season(): void
    {
        $components = new EuroLicensePlateNumberComponents('B', 'CD', '1');
        $customization = new LicensePlateItemCustomization($components);
        $array = $customization->toArray();

        self::assertArrayNotHasKey('seasonStartMonth', $array);
        self::assertArrayNotHasKey('seasonEndMonth', $array);
    }
}

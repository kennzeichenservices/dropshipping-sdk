<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO;

use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\LicensePlateNumberComponentsFactory;
use Dropshipping\DTO\ParkingLicensePlateNumberComponents;
use Dropshipping\Enums\LicensePlateUsageType;
use PHPUnit\Framework\TestCase;

final class LicensePlateNumberComponentsTest extends TestCase
{
    public function test_euro_components_toArray_and_fromArray(): void
    {
        $components = new EuroLicensePlateNumberComponents('B', 'AB', '1234');
        $array = $components->toArray();

        self::assertSame('EURO', $array['usageType']);
        self::assertSame('B', $array['city']);
        self::assertSame('AB', $array['middle']);
        self::assertSame('1234', $array['end']);

        $restored = EuroLicensePlateNumberComponents::fromArray($array);
        self::assertSame('B', $restored->city);
        self::assertSame(LicensePlateUsageType::Euro, $restored->usageType);
    }

    public function test_parking_components_toArray_and_fromArray(): void
    {
        $components = new ParkingLicensePlateNumberComponents('PARK-1');
        $array = $components->toArray();

        self::assertSame('PARKING', $array['usageType']);
        self::assertSame('PARK-1', $array['text']);

        $restored = ParkingLicensePlateNumberComponents::fromArray($array);
        self::assertSame('PARK-1', $restored->text);
        self::assertSame(LicensePlateUsageType::Parking, $restored->usageType);
    }

    public function test_factory_creates_euro_components(): void
    {
        $result = LicensePlateNumberComponentsFactory::fromArray([
            'usageType' => 'EURO',
            'city' => 'M',
            'middle' => 'XY',
            'end' => '999',
        ]);

        self::assertInstanceOf(EuroLicensePlateNumberComponents::class, $result);
    }

    public function test_factory_creates_parking_components(): void
    {
        $result = LicensePlateNumberComponentsFactory::fromArray([
            'usageType' => 'PARKING',
            'text' => 'P-123',
        ]);

        self::assertInstanceOf(ParkingLicensePlateNumberComponents::class, $result);
    }
}

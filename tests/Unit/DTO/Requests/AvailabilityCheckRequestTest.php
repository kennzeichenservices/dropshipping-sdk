<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO\Requests;

use Dropshipping\DTO\Requests\AvailabilityCheckRequest;
use Dropshipping\Enums\LicensePlateType;
use Dropshipping\Enums\VehicleType;
use PHPUnit\Framework\TestCase;

final class AvailabilityCheckRequestTest extends TestCase
{
    public function test_toArray_includes_all_required_fields(): void
    {
        $request = new AvailabilityCheckRequest(
            registrationOfficeServiceId: 100,
            city: 'B',
            middle: 'AB',
            end: '1234',
            licensePlateType: LicensePlateType::Regular,
            vehicleType: VehicleType::Car,
        );

        $array = $request->toArray();

        self::assertSame(100, $array['registrationOfficeServiceId']);
        self::assertSame('B', $array['licensePlateNumberPatternComponents']['city']);
        self::assertSame('AB', $array['licensePlateNumberPatternComponents']['middle']);
        self::assertSame('1234', $array['licensePlateNumberPatternComponents']['end']);
        self::assertSame('REGULAR', $array['licensePlateType']);
        self::assertSame('CAR', $array['vehicleType']);
    }

    public function test_toArray_excludes_null_season_months(): void
    {
        $request = new AvailabilityCheckRequest(1, 'B', 'AB', '1', LicensePlateType::Regular, VehicleType::Car);
        $array = $request->toArray();

        self::assertArrayNotHasKey('seasonStartMonth', $array);
        self::assertArrayNotHasKey('seasonEndMonth', $array);
    }

    public function test_toArray_includes_season_months_when_set(): void
    {
        $request = new AvailabilityCheckRequest(
            1, 'B', 'AB', '1', LicensePlateType::RegularSeason, VehicleType::Car,
            seasonStartMonth: 4, seasonEndMonth: 10,
        );
        $array = $request->toArray();

        self::assertSame(4, $array['seasonStartMonth']);
        self::assertSame(10, $array['seasonEndMonth']);
    }
}

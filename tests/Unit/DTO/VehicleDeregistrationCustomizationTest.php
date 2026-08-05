<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO;

use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\VehicleDeregistrationCustomization;
use Dropshipping\Enums\ProductType;
use Dropshipping\Enums\VehicleDeregistrationLicensePlateType;
use Dropshipping\Enums\VehicleDeregistrationVehicleType;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\TestCase;

final class VehicleDeregistrationCustomizationTest extends TestCase
{
    public function test_product_type_is_vehicle_deregistration(): void
    {
        $customization = $this->createCustomization();

        self::assertSame(ProductType::VehicleDeregistration, $customization->productType);
    }

    public function test_toArray_includes_required_fields(): void
    {
        $customization = $this->createCustomization();
        $array = $customization->toArray();

        self::assertSame('VEHICLE_DEREGISTRATION', $array['productType']);
        self::assertSame('CAR', $array['vehicleType']);
        self::assertSame('REGULAR', $array['licensePlateType']);
        self::assertFalse($array['licensePlateReservationIncluded']);
        self::assertSame('WBA12345678901234', $array['vehicleIdentificationNumber']);
        self::assertSame('SEC123', $array['vehicleRegistrationCertificateSecurityCode']);
        self::assertSame('2020-01-15', $array['vehicleRegistrationDate']);
        self::assertSame('REAR1', $array['rearLicensePlateSecurityCode']);
        self::assertArrayHasKey('licensePlateNumberComponents', $array);
        self::assertSame('B', $array['licensePlateNumberComponents']['city']);
    }

    public function test_toArray_excludes_null_optional_fields(): void
    {
        $customization = $this->createCustomization();
        $array = $customization->toArray();

        self::assertArrayNotHasKey('frontLicensePlateSecurityCode', $array);
        self::assertArrayNotHasKey('seasonStartMonth', $array);
        self::assertArrayNotHasKey('seasonEndMonth', $array);
    }

    public function test_toArray_includes_optional_fields_when_set(): void
    {
        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');
        $customization = new VehicleDeregistrationCustomization(
            vehicleType: VehicleDeregistrationVehicleType::Motorcycle,
            licensePlateType: VehicleDeregistrationLicensePlateType::HistoricalSeason,
            licensePlateNumberComponents: $components,
            licensePlateReservationIncluded: true,
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleRegistrationCertificateSecurityCode: 'SEC123',
            vehicleRegistrationDate: '2020-01-15',
            rearLicensePlateSecurityCode: 'REAR1',
            frontLicensePlateSecurityCode: 'FRONT1',
            seasonStartMonth: 4,
            seasonEndMonth: 10,
        );

        $array = $customization->toArray();

        self::assertSame('FRONT1', $array['frontLicensePlateSecurityCode']);
        self::assertSame(4, $array['seasonStartMonth']);
        self::assertSame(10, $array['seasonEndMonth']);
        self::assertSame('MOTORCYCLE', $array['vehicleType']);
        self::assertSame('HISTORICAL_SEASON', $array['licensePlateType']);
    }

    public function test_validates_empty_vin(): void
    {
        $this->expectException(DropshippingException::class);

        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');

        new VehicleDeregistrationCustomization(
            vehicleType: VehicleDeregistrationVehicleType::Car,
            licensePlateType: VehicleDeregistrationLicensePlateType::Regular,
            licensePlateNumberComponents: $components,
            licensePlateReservationIncluded: false,
            vehicleIdentificationNumber: '',
            vehicleRegistrationCertificateSecurityCode: 'SEC123',
            vehicleRegistrationDate: '2020-01-15',
            rearLicensePlateSecurityCode: 'REAR1',
        );
    }

    public function test_validates_season_month_range(): void
    {
        $this->expectException(DropshippingException::class);

        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');

        new VehicleDeregistrationCustomization(
            vehicleType: VehicleDeregistrationVehicleType::Car,
            licensePlateType: VehicleDeregistrationLicensePlateType::Regular,
            licensePlateNumberComponents: $components,
            licensePlateReservationIncluded: false,
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleRegistrationCertificateSecurityCode: 'SEC123',
            vehicleRegistrationDate: '2020-01-15',
            rearLicensePlateSecurityCode: 'REAR1',
            seasonEndMonth: 0,
        );
    }

    private function createCustomization(): VehicleDeregistrationCustomization
    {
        $components = new EuroLicensePlateNumberComponents('B', 'AB', '123');

        return new VehicleDeregistrationCustomization(
            vehicleType: VehicleDeregistrationVehicleType::Car,
            licensePlateType: VehicleDeregistrationLicensePlateType::Regular,
            licensePlateNumberComponents: $components,
            licensePlateReservationIncluded: false,
            vehicleIdentificationNumber: 'WBA12345678901234',
            vehicleRegistrationCertificateSecurityCode: 'SEC123',
            vehicleRegistrationDate: '2020-01-15',
            rearLicensePlateSecurityCode: 'REAR1',
        );
    }
}

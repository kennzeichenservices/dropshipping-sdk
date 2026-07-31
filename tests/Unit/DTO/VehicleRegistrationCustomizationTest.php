<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO;

use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\VehicleRegistrationCustomization;
use Dropshipping\DTO\VehicleRegistrationLicensePlate;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyReservation;
use Dropshipping\DTO\VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained;
use Dropshipping\DTO\VehicleRegistrationPreviousLicensePlate;
use Dropshipping\Enums\ProductType;
use Dropshipping\Enums\VehicleRegistrationLicensePlateType;
use Dropshipping\Enums\VehicleRegistrationServiceTypeCode;
use Dropshipping\Enums\VehicleRegistrationVehicleType;
use Dropshipping\Exceptions\DropshippingException;
use PHPUnit\Framework\TestCase;

final class VehicleRegistrationCustomizationTest extends TestCase
{
    public function test_productType_is_always_vehicle_registration(): void
    {
        $customization = $this->create();

        self::assertSame(ProductType::VehicleRegistration, $customization->productType);
        self::assertSame('VEHICLE_REGISTRATION', $customization->toArray()['productType']);
    }

    public function test_toArray_keeps_deregistered_false(): void
    {
        $array = $this->create(['deregistered' => false])->toArray();

        self::assertArrayHasKey('deregistered', $array);
        self::assertFalse($array['deregistered']);
    }

    public function test_toArray_excludes_null_optional_fields(): void
    {
        $array = $this->create()->toArray();

        self::assertArrayNotHasKey('vehicleRegistrationCertificateSecurityCode', $array);
        self::assertArrayNotHasKey('vehicleTitleNumber', $array);
        self::assertArrayNotHasKey('previousLicensePlate', $array);
    }

    public function test_toArray_includes_previousLicensePlate_when_set(): void
    {
        $previous = new VehicleRegistrationPreviousLicensePlate(
            licensePlateNumberComponents: new EuroLicensePlateNumberComponents('M', 'XY', '99'),
            licensePlateType: VehicleRegistrationLicensePlateType::Historical,
            frontLicensePlateSecurityCode: 'F12',
            rearLicensePlateSecurityCode: 'R34',
        );

        $array = $this->create(['previousLicensePlate' => $previous])->toArray();

        self::assertSame('HISTORICAL', $array['previousLicensePlate']['licensePlateType']);
        self::assertSame('F12', $array['previousLicensePlate']['frontLicensePlateSecurityCode']);
        self::assertSame('R34', $array['previousLicensePlate']['rearLicensePlateSecurityCode']);
        self::assertSame('EURO', $array['previousLicensePlate']['licensePlateNumberComponents']['usageType']);
    }

    public function test_random_strategy_serializes_to_discriminator_only(): void
    {
        $array = $this->create()->toArray();

        self::assertSame(['strategyType' => 'RANDOM'], $array['licensePlateNumberAssignmentStrategy']);
    }

    public function test_retained_strategy_serializes_to_discriminator_only(): void
    {
        $array = $this->create([
            'licensePlateNumberAssignmentStrategy' => new VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained(),
        ])->toArray();

        self::assertSame(['strategyType' => 'RETAINMENT'], $array['licensePlateNumberAssignmentStrategy']);
    }

    public function test_reservation_strategy_serializes_plate_and_pin(): void
    {
        $array = $this->create([
            'licensePlateNumberAssignmentStrategy' => new VehicleRegistrationLicensePlateNumberAssignmentStrategyReservation(
                licensePlate: new VehicleRegistrationLicensePlate(
                    licensePlateNumberComponents: new EuroLicensePlateNumberComponents('B', 'AB', '123'),
                    licensePlateType: VehicleRegistrationLicensePlateType::ElectricSeason,
                    seasonStartMonth: 3,
                    seasonEndMonth: 10,
                ),
                reservationPin: '1234',
            ),
        ])->toArray();

        $strategy = $array['licensePlateNumberAssignmentStrategy'];

        self::assertSame('RESERVATION', $strategy['strategyType']);
        self::assertSame('1234', $strategy['reservationPin']);
        self::assertSame('ELECTRIC_SEASON', $strategy['licensePlate']['licensePlateType']);
        self::assertSame(3, $strategy['licensePlate']['seasonStartMonth']);
        self::assertSame(10, $strategy['licensePlate']['seasonEndMonth']);
        self::assertSame('EURO', $strategy['licensePlate']['licensePlateNumberComponents']['usageType']);
    }

    public function test_reservation_strategy_rejects_too_short_reservationPin(): void
    {
        $this->expectException(DropshippingException::class);

        new VehicleRegistrationLicensePlateNumberAssignmentStrategyReservation(
            licensePlate: new VehicleRegistrationLicensePlate(
                licensePlateNumberComponents: new EuroLicensePlateNumberComponents('B', 'AB', '123'),
                licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            ),
            reservationPin: '123',
        );
    }

    public function test_licensePlate_rejects_out_of_range_season_month(): void
    {
        $this->expectException(DropshippingException::class);

        new VehicleRegistrationLicensePlate(
            licensePlateNumberComponents: new EuroLicensePlateNumberComponents('B', 'AB', '123'),
            licensePlateType: VehicleRegistrationLicensePlateType::RegularSeason,
            seasonStartMonth: 13,
        );
    }

    public function test_licensePlate_toArray_excludes_null_season_months(): void
    {
        $array = (new VehicleRegistrationLicensePlate(
            licensePlateNumberComponents: new EuroLicensePlateNumberComponents('B', 'AB', '123'),
            licensePlateType: VehicleRegistrationLicensePlateType::Regular,
        ))->toArray();

        self::assertArrayNotHasKey('seasonStartMonth', $array);
        self::assertArrayNotHasKey('seasonEndMonth', $array);
        self::assertSame('REGULAR', $array['licensePlateType']);
    }

    public function test_constructor_rejects_electronicInsuranceConfirmationNumber_of_wrong_length(): void
    {
        $this->expectException(DropshippingException::class);

        $this->create(['electronicInsuranceConfirmationNumber' => 'ABC123']);
    }

    public function test_constructor_rejects_vehicleTitleSecurityCode_of_wrong_length(): void
    {
        $this->expectException(DropshippingException::class);

        $this->create(['vehicleTitleSecurityCode' => 'TOOSHORT']);
    }

    public function test_constructor_rejects_vehicleTitleNumber_of_wrong_length(): void
    {
        $this->expectException(DropshippingException::class);

        $this->create(['vehicleTitleNumber' => 'AB1234567']);
    }

    public function test_constructor_rejects_vehicleRegistrationCertificateSecurityCode_of_wrong_length(): void
    {
        $this->expectException(DropshippingException::class);

        $this->create(['vehicleRegistrationCertificateSecurityCode' => 'SEC12345']);
    }

    public function test_constructor_rejects_too_short_iban(): void
    {
        $this->expectException(DropshippingException::class);

        $this->create(['iban' => 'DE8937040044']);
    }

    public function test_constructor_rejects_too_long_bic(): void
    {
        $this->expectException(DropshippingException::class);

        $this->create(['bic' => 'COBADEFFXXXX']);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function create(array $overrides = []): VehicleRegistrationCustomization
    {
        $defaults = [
            'licensePlateNumberAssignmentStrategy' => new VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom(),
            'vehicleRegistrationServiceTypeCode' => VehicleRegistrationServiceTypeCode::NZ,
            'deregistered' => false,
            'vehicleType' => VehicleRegistrationVehicleType::Car,
            'electronicInsuranceConfirmationNumber' => 'ABC1234',
            'vehicleIdentificationNumber' => 'WBA12345678901234',
            'vehicleTitleSecurityCode' => 'ABCDEF123456',
            'iban' => 'DE89370400440532013000',
            'bic' => 'COBADEFFXXX',
            'vehicleRegistrationCertificateSecurityCode' => null,
            'vehicleTitleNumber' => null,
            'previousLicensePlate' => null,
        ];

        /** @var array<string, mixed> $args */
        $args = array_merge($defaults, $overrides);

        return new VehicleRegistrationCustomization(...$args);
    }
}

<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO;

use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\VehicleRegistrationCustomization;
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

        $array = $this->create([
            'vehicleRegistrationServiceTypeCode' => VehicleRegistrationServiceTypeCode::WZ,
            'previousLicensePlate' => $previous,
        ])->toArray();

        self::assertSame('HISTORICAL', $array['previousLicensePlate']['licensePlateType']);
        self::assertSame('F12', $array['previousLicensePlate']['frontLicensePlateSecurityCode']);
        self::assertSame('R34', $array['previousLicensePlate']['rearLicensePlateSecurityCode']);
        self::assertSame('EURO', $array['previousLicensePlate']['licensePlateNumberComponents']['usageType']);
    }

    public function test_random_strategy_serializes_plate_type(): void
    {
        $array = $this->create()->toArray();

        self::assertSame(
            ['strategyType' => 'RANDOM', 'licensePlateType' => 'REGULAR'],
            $array['licensePlateNumberAssignmentStrategy'],
        );
    }

    public function test_random_strategy_serializes_season_months(): void
    {
        $array = $this->create([
            'licensePlateNumberAssignmentStrategy' => new VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom(
                licensePlateType: VehicleRegistrationLicensePlateType::RegularSeason,
                seasonStartMonth: 4,
                seasonEndMonth: 10,
            ),
        ])->toArray();

        self::assertSame([
            'strategyType' => 'RANDOM',
            'licensePlateType' => 'REGULAR_SEASON',
            'seasonStartMonth' => 4,
            'seasonEndMonth' => 10,
        ], $array['licensePlateNumberAssignmentStrategy']);
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
                licensePlateNumberComponents: new EuroLicensePlateNumberComponents('B', 'AB', '123'),
                licensePlateType: VehicleRegistrationLicensePlateType::ElectricSeason,
                reservationPin: '1234',
                seasonStartMonth: 3,
                seasonEndMonth: 10,
            ),
        ])->toArray();

        $strategy = $array['licensePlateNumberAssignmentStrategy'];

        self::assertSame('RESERVATION', $strategy['strategyType']);
        self::assertSame('1234', $strategy['reservationPin']);
        self::assertSame('ELECTRIC_SEASON', $strategy['licensePlateType']);
        self::assertSame(3, $strategy['seasonStartMonth']);
        self::assertSame(10, $strategy['seasonEndMonth']);
        self::assertSame('EURO', $strategy['licensePlateNumberComponents']['usageType']);
    }

    public function test_reservation_strategy_omits_null_season_months(): void
    {
        $array = (new VehicleRegistrationLicensePlateNumberAssignmentStrategyReservation(
            licensePlateNumberComponents: new EuroLicensePlateNumberComponents('B', 'AB', '123'),
            licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            reservationPin: '1234',
        ))->toArray();

        self::assertArrayNotHasKey('seasonStartMonth', $array);
        self::assertArrayNotHasKey('seasonEndMonth', $array);
    }

    public function test_reservation_strategy_rejects_too_short_reservationPin(): void
    {
        $this->expectException(DropshippingException::class);

        new VehicleRegistrationLicensePlateNumberAssignmentStrategyReservation(
            licensePlateNumberComponents: new EuroLicensePlateNumberComponents('B', 'AB', '123'),
            licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            reservationPin: '123',
        );
    }

    public function test_strategy_rejects_out_of_range_season_month(): void
    {
        $this->expectException(DropshippingException::class);

        new VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom(
            licensePlateType: VehicleRegistrationLicensePlateType::RegularSeason,
            seasonStartMonth: 13,
        );
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
        $this->expectExceptionMessage('must be between 7 and 7 characters');

        $this->create([
            'vehicleRegistrationServiceTypeCode' => VehicleRegistrationServiceTypeCode::WZ,
            'vehicleRegistrationCertificateSecurityCode' => 'SEC12345',
        ]);
    }

    public function test_constructor_rejects_vehicleRegistrationCertificateSecurityCode_for_nz(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage(
            'Field "vehicleRegistrationCertificateSecurityCode" must be null for vehicleRegistrationServiceTypeCode NZ',
        );

        $this->create([
            'vehicleRegistrationServiceTypeCode' => VehicleRegistrationServiceTypeCode::NZ,
            'vehicleRegistrationCertificateSecurityCode' => 'SEC1234',
        ]);
    }

    public function test_constructor_rejects_previousLicensePlate_for_nz(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage(
            'Field "previousLicensePlate" must be null for vehicleRegistrationServiceTypeCode NZ',
        );

        $this->create([
            'vehicleRegistrationServiceTypeCode' => VehicleRegistrationServiceTypeCode::NZ,
            'previousLicensePlate' => $this->previousLicensePlate(),
        ]);
    }

    public function test_constructor_allows_previous_vehicle_fields_for_other_service_type_codes(): void
    {
        foreach (VehicleRegistrationServiceTypeCode::cases() as $code) {
            if ($code === VehicleRegistrationServiceTypeCode::NZ) {
                continue;
            }

            $array = $this->create([
                'vehicleRegistrationServiceTypeCode' => $code,
                'vehicleRegistrationCertificateSecurityCode' => 'SEC1234',
                'previousLicensePlate' => $this->previousLicensePlate(),
            ])->toArray();

            self::assertSame('SEC1234', $array['vehicleRegistrationCertificateSecurityCode'], $code->value);
            self::assertArrayHasKey('previousLicensePlate', $array, $code->value);
        }
    }

    public function test_nz_stays_valid_without_the_forbidden_fields(): void
    {
        $array = $this->create(['vehicleRegistrationServiceTypeCode' => VehicleRegistrationServiceTypeCode::NZ])->toArray();

        self::assertSame('NZ', $array['vehicleRegistrationServiceTypeCode']);
        self::assertArrayNotHasKey('vehicleRegistrationCertificateSecurityCode', $array);
        self::assertArrayNotHasKey('previousLicensePlate', $array);
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

    private function previousLicensePlate(): VehicleRegistrationPreviousLicensePlate
    {
        return new VehicleRegistrationPreviousLicensePlate(
            licensePlateNumberComponents: new EuroLicensePlateNumberComponents('M', 'KG', '6988'),
            licensePlateType: VehicleRegistrationLicensePlateType::Regular,
        );
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function create(array $overrides = []): VehicleRegistrationCustomization
    {
        $defaults = [
            'licensePlateNumberAssignmentStrategy' => new VehicleRegistrationLicensePlateNumberAssignmentStrategyRandom(
                licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            ),
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

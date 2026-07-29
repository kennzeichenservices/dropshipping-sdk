<?php

declare(strict_types=1);

namespace Dropshipping\Tests\Unit\DTO;

use Dropshipping\DTO\EuroLicensePlateNumberComponents;
use Dropshipping\DTO\VehicleRegistrationCustomization;
use Dropshipping\DTO\VehicleRegistrationPreviousLicensePlate;
use Dropshipping\Enums\ProductType;
use Dropshipping\Enums\VehicleRegistrationLicensePlateNumberAssignmentStrategy;
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

        self::assertArrayNotHasKey('seasonStartMonth', $array);
        self::assertArrayNotHasKey('seasonEndMonth', $array);
        self::assertArrayNotHasKey('vehicleRegistrationCertificateSecurityCode', $array);
        self::assertArrayNotHasKey('vehicleTitleNumber', $array);
        self::assertArrayNotHasKey('previousLicensePlate', $array);
        self::assertArrayNotHasKey('reservationPin', $array);
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

    public function test_reservation_strategy_requires_reservationPin(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage('reservationPin is required');

        $this->create([
            'licensePlateNumberAssignmentStrategy' => VehicleRegistrationLicensePlateNumberAssignmentStrategy::Reservation,
        ]);
    }

    public function test_reservation_strategy_accepts_reservationPin(): void
    {
        $array = $this->create([
            'licensePlateNumberAssignmentStrategy' => VehicleRegistrationLicensePlateNumberAssignmentStrategy::Reservation,
            'reservationPin' => '1234',
        ])->toArray();

        self::assertSame('RESERVATION', $array['licensePlateNumberAssignmentStrategy']);
        self::assertSame('1234', $array['reservationPin']);
    }

    public function test_random_strategy_does_not_require_reservationPin(): void
    {
        $customization = $this->create([
            'licensePlateNumberAssignmentStrategy' => VehicleRegistrationLicensePlateNumberAssignmentStrategy::Random,
        ]);

        self::assertNull($customization->reservationPin);
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

    public function test_constructor_rejects_out_of_range_season_month(): void
    {
        $this->expectException(DropshippingException::class);

        $this->create(['seasonStartMonth' => 13]);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function create(array $overrides = []): VehicleRegistrationCustomization
    {
        $defaults = [
            'licensePlateNumberAssignmentStrategy' => VehicleRegistrationLicensePlateNumberAssignmentStrategy::Random,
            'vehicleRegistrationServiceTypeCode' => VehicleRegistrationServiceTypeCode::NZ,
            'licensePlateNumberComponents' => new EuroLicensePlateNumberComponents('B', 'AB', '123'),
            'deregistered' => false,
            'vehicleType' => VehicleRegistrationVehicleType::Car,
            'licensePlateType' => VehicleRegistrationLicensePlateType::Regular,
            'electronicInsuranceConfirmationNumber' => 'ABC1234',
            'vehicleIdentificationNumber' => 'WBA12345678901234',
            'vehicleTitleSecurityCode' => 'ABCDEF123456',
            'iban' => 'DE89370400440532013000',
            'bic' => 'COBADEFFXXX',
            'seasonStartMonth' => null,
            'seasonEndMonth' => null,
            'vehicleRegistrationCertificateSecurityCode' => null,
            'vehicleTitleNumber' => null,
            'previousLicensePlate' => null,
            'reservationPin' => null,
        ];

        /** @var array<string, mixed> $args */
        $args = array_merge($defaults, $overrides);

        return new VehicleRegistrationCustomization(...$args);
    }
}

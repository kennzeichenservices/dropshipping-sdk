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
        $array = $this->createContinuation(['deregistered' => false])->toArray();

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

        $array = $this->createContinuation(['previousLicensePlate' => $previous])->toArray();

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
        $array = $this->createContinuation([
            'licensePlateNumberAssignmentStrategy' => new VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained(),
        ])->toArray();

        self::assertSame(['strategyType' => 'RETAINMENT'], $array['licensePlateNumberAssignmentStrategy']);
    }

    public function test_retained_strategy_requires_previousLicensePlate(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage(
            'Field "previousLicensePlate" is required when the previous license plate number is retained',
        );

        $this->create([
            'licensePlateNumberAssignmentStrategy' => new VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained(),
        ]);
    }

    public function test_retained_strategy_rejects_front_plate_security_code(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage(
            'Field "previousLicensePlate.frontLicensePlateSecurityCode" must be null when the previous license plate number is retained',
        );

        $this->createContinuation([
            'licensePlateNumberAssignmentStrategy' => new VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained(),
            'previousLicensePlate' => $this->previousLicensePlate(frontLicensePlateSecurityCode: 'F12'),
        ]);
    }

    public function test_retained_strategy_rejects_rear_plate_security_code(): void
    {
        $this->expectException(DropshippingException::class);
        $this->expectExceptionMessage(
            'Field "previousLicensePlate.rearLicensePlateSecurityCode" must be null when the previous license plate number is retained',
        );

        $this->createContinuation([
            'licensePlateNumberAssignmentStrategy' => new VehicleRegistrationLicensePlateNumberAssignmentStrategyRetained(),
            'previousLicensePlate' => $this->previousLicensePlate(rearLicensePlateSecurityCode: 'R34'),
        ]);
    }

    public function test_plate_security_codes_survive_the_other_strategies(): void
    {
        $array = $this->createContinuation([
            'previousLicensePlate' => $this->previousLicensePlate(
                frontLicensePlateSecurityCode: 'F12',
                rearLicensePlateSecurityCode: 'R34',
            ),
        ])->toArray();

        self::assertSame('F12', $array['previousLicensePlate']['frontLicensePlateSecurityCode']);
        self::assertSame('R34', $array['previousLicensePlate']['rearLicensePlateSecurityCode']);
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

        $this->createContinuation(['vehicleRegistrationCertificateSecurityCode' => 'SEC12345']);
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
        foreach (self::continuingServiceTypeCodes() as $code) {
            $array = $this->create([
                'vehicleRegistrationServiceTypeCode' => $code,
                'vehicleRegistrationCertificateSecurityCode' => 'SEC1234',
                'previousLicensePlate' => $this->previousLicensePlate(),
            ])->toArray();

            self::assertSame('SEC1234', $array['vehicleRegistrationCertificateSecurityCode'], $code->value);
            self::assertArrayHasKey('previousLicensePlate', $array, $code->value);
        }
    }

    public function test_constructor_requires_vehicleRegistrationCertificateSecurityCode_for_other_service_type_codes(): void
    {
        foreach (self::continuingServiceTypeCodes() as $code) {
            try {
                $this->create([
                    'vehicleRegistrationServiceTypeCode' => $code,
                    'previousLicensePlate' => $this->previousLicensePlate(),
                ]);

                self::fail(sprintf('Expected %s to require vehicleRegistrationCertificateSecurityCode', $code->value));
            } catch (DropshippingException $exception) {
                self::assertSame(
                    sprintf(
                        'Field "vehicleRegistrationCertificateSecurityCode" is required for vehicleRegistrationServiceTypeCode %s',
                        $code->value,
                    ),
                    $exception->getMessage(),
                );
            }
        }
    }

    public function test_constructor_requires_previousLicensePlate_for_other_service_type_codes(): void
    {
        foreach (self::continuingServiceTypeCodes() as $code) {
            try {
                $this->create([
                    'vehicleRegistrationServiceTypeCode' => $code,
                    'vehicleRegistrationCertificateSecurityCode' => 'SEC1234',
                ]);

                self::fail(sprintf('Expected %s to require previousLicensePlate', $code->value));
            } catch (DropshippingException $exception) {
                self::assertSame(
                    sprintf(
                        'Field "previousLicensePlate" is required for vehicleRegistrationServiceTypeCode %s',
                        $code->value,
                    ),
                    $exception->getMessage(),
                );
            }
        }
    }

    public function test_constructor_requires_a_deregistered_vehicle_for_nz_wz_and_wg(): void
    {
        $codes = [
            VehicleRegistrationServiceTypeCode::NZ,
            VehicleRegistrationServiceTypeCode::WZ,
            VehicleRegistrationServiceTypeCode::WG,
        ];

        foreach ($codes as $code) {
            $overrides = ['vehicleRegistrationServiceTypeCode' => $code, 'deregistered' => false];

            if ($code->requiresPreviousRegistration()) {
                $overrides['vehicleRegistrationCertificateSecurityCode'] = 'SEC1234';
                $overrides['previousLicensePlate'] = $this->previousLicensePlate();
            }

            try {
                $this->create($overrides);

                self::fail(sprintf('Expected %s to require a deregistered vehicle', $code->value));
            } catch (DropshippingException $exception) {
                self::assertSame(
                    sprintf('Field "deregistered" must be true for vehicleRegistrationServiceTypeCode %s', $code->value),
                    $exception->getMessage(),
                );
            }
        }
    }

    public function test_constructor_leaves_deregistered_free_for_the_remaining_codes(): void
    {
        $codes = [
            VehicleRegistrationServiceTypeCode::UG,
            VehicleRegistrationServiceTypeCode::UM,
            VehicleRegistrationServiceTypeCode::UO,
            VehicleRegistrationServiceTypeCode::UI,
            VehicleRegistrationServiceTypeCode::HA,
        ];

        foreach ($codes as $code) {
            $array = $this->createContinuation([
                'vehicleRegistrationServiceTypeCode' => $code,
                'deregistered' => false,
            ])->toArray();

            self::assertFalse($array['deregistered'], $code->value);
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

    /**
     * The service type codes that continue an existing registration — every one
     * but NZ.
     *
     * @return list<VehicleRegistrationServiceTypeCode>
     */
    private static function continuingServiceTypeCodes(): array
    {
        return array_values(array_filter(
            VehicleRegistrationServiceTypeCode::cases(),
            static fn (VehicleRegistrationServiceTypeCode $code): bool => $code !== VehicleRegistrationServiceTypeCode::NZ,
        ));
    }

    private function previousLicensePlate(
        ?string $frontLicensePlateSecurityCode = null,
        ?string $rearLicensePlateSecurityCode = null,
    ): VehicleRegistrationPreviousLicensePlate {
        return new VehicleRegistrationPreviousLicensePlate(
            licensePlateNumberComponents: new EuroLicensePlateNumberComponents('M', 'KG', '6988'),
            licensePlateType: VehicleRegistrationLicensePlateType::Regular,
            frontLicensePlateSecurityCode: $frontLicensePlateSecurityCode,
            rearLicensePlateSecurityCode: $rearLicensePlateSecurityCode,
        );
    }

    /**
     * A customization for a service type code that continues an existing
     * registration, carrying the two fields those codes require.
     *
     * @param array<string, mixed> $overrides
     */
    private function createContinuation(array $overrides = []): VehicleRegistrationCustomization
    {
        return $this->create(array_merge([
            'vehicleRegistrationServiceTypeCode' => VehicleRegistrationServiceTypeCode::UG,
            'vehicleRegistrationCertificateSecurityCode' => 'SEC1234',
            'previousLicensePlate' => $this->previousLicensePlate(),
        ], $overrides));
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
            'deregistered' => true,
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
